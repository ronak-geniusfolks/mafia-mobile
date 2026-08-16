<?php

namespace App\Http\Controllers;

use App\Exports\AttachmentsExport;
use App\Models\Attachment;
use App\Models\Invoice;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use ZipArchive;

class AttachmentController extends Controller
{
    // ─── Authenticated: upload from laptop ───────────────────────────────────

    public function store(Request $request)
    {
        // ── Pending mode: uploaded from the Create screen before the parent exists ──
        if ($request->filled('upload_token') && ! $request->filled('attachable_id')) {
            return $this->storePending($request);
        }

        $request->validate([
            'attachable_type' => 'required|in:invoice,purchase',
            'attachable_id'   => 'required|integer',
            'files'           => 'required|array|min:1',
            'files.*'         => 'required|file|mimes:jpg,jpeg,png,gif,webp,pdf|max:10240',
            'label'           => 'nullable|string|max:100',
        ]);

        [$modelClass, $type] = $this->resolveModel($request->attachable_type);
        $model = $modelClass::findOrFail($request->attachable_id);

        $uploaded = [];
        foreach ($request->file('files') as $file) {
            $attachment = $this->storeFile($file, "attachments/{$type}/{$model->id}", [
                'attachable_type' => $modelClass,
                'attachable_id'   => $model->id,
                'label'           => $request->label ?? null,
                'uploaded_by'     => Auth::id(),
            ]);
            $uploaded[] = $this->formatAttachment($attachment);
        }

        return response()->json(['success' => true, 'attachments' => $uploaded]);
    }

    // ─── Authenticated: pending upload (Create screen, before parent exists) ────

    /**
     * Store files into a pending bucket keyed by an upload-session token.
     * Rows keep a null attachable_id until the invoice is saved and
     * attachPendingToInvoice() links them.
     */
    protected function storePending(Request $request)
    {
        $request->validate([
            'upload_token' => 'required|string|max:64',
            'files'        => 'required|array|min:1',
            'files.*'      => 'required|file|mimes:jpg,jpeg,png,gif,webp,pdf|max:10240',
            'label'        => 'nullable|string|max:100',
        ]);

        $token   = $request->upload_token;
        $session = Cache::get('mm_upload_token_' . $token);

        if (! $session || empty($session['pending'])) {
            return response()->json([
                'success' => false,
                'message' => 'This upload session has expired. Please reload the page and try again.',
            ], 403);
        }

        [$modelClass] = $this->resolveModel($session['type'] ?? 'invoice');

        $uploaded = [];
        foreach ($request->file('files') as $file) {
            $attachment = $this->storeFile($file, "attachments/pending/{$token}", [
                'attachable_type' => $modelClass,
                'attachable_id'   => null,
                'upload_token'    => $token,
                'label'           => $request->label ?? null,
                'uploaded_by'     => Auth::id(),
            ]);
            $uploaded[] = $this->formatAttachment($attachment);
        }

        return response()->json(['success' => true, 'attachments' => $uploaded]);
    }

    /** Return the current pending uploads for a token (the Create screen polls this). */
    public function listPending(string $token)
    {
        $attachments = Attachment::pendingForToken($token)
            ->orderBy('created_at')
            ->get()
            ->map(fn (Attachment $a) => $this->formatAttachment($a))
            ->values();

        return response()->json(['success' => true, 'attachments' => $attachments]);
    }

    // ─── Shared helpers ─────────────────────────────────────────────────────────

    /**
     * Move an uploaded file into $folder (under public/) and create its Attachment row.
     * A short random suffix keeps filenames unique when several files land in the same
     * second (e.g. a phone and a laptop uploading at once).
     */
    protected function storeFile($file, string $folder, array $attributes): Attachment
    {
        // Capture metadata BEFORE move()
        $origName  = $file->getClientOriginalName();
        $mimeType  = $file->getClientMimeType();
        $fileSize  = $file->getSize();
        $extension = $file->getClientOriginalExtension();

        File::ensureDirectoryExists(public_path($folder));
        $filename = date('YmdHis') . '_' . Str::random(6) . '_'
            . Str::slug(pathinfo($origName, PATHINFO_FILENAME)) . '.' . $extension;
        $file->move(public_path($folder), $filename);

        return Attachment::create(array_merge([
            'file_path' => $folder . '/' . $filename,
            'file_name' => $origName,
            'file_type' => $mimeType,
            'file_size' => $fileSize,
        ], $attributes));
    }

    /** Shape an Attachment for the JSON the panel JS consumes. */
    protected function formatAttachment(Attachment $attachment): array
    {
        return [
            'id'             => $attachment->id,
            'file_name'      => $attachment->file_name,
            'file_type'      => $attachment->file_type,
            'formatted_size' => $attachment->formatted_size,
            'url'            => $attachment->url,
            'label'          => $attachment->label,
            'is_image'       => $attachment->isImage(),
            'is_pdf'         => $attachment->isPdf(),
        ];
    }

    /**
     * Link all pending uploads for $token to the freshly-created invoice and move
     * their files from the pending bucket into the invoice's own folder. Safe to call
     * with a null/empty token. Returns the number of files linked.
     */
    public static function attachPendingToInvoice(?string $token, Invoice $invoice): int
    {
        if (empty($token)) {
            return 0;
        }

        $pending = Attachment::pendingForToken($token)->get();
        $moved   = 0;

        foreach ($pending as $att) {
            $newFolder = "attachments/invoice/{$invoice->id}";
            File::ensureDirectoryExists(public_path($newFolder));

            $newPath = $newFolder . '/' . basename($att->file_path);
            $oldFull = public_path($att->file_path);
            $newFull = public_path($newPath);

            if (is_file($oldFull) && @rename($oldFull, $newFull)) {
                $att->file_path = $newPath;
            }

            $att->attachable_type = Invoice::class;
            $att->attachable_id   = $invoice->id;
            $att->upload_token    = null;
            $att->save();
            $moved++;
        }

        // Best-effort cleanup of the now-empty pending directory + token.
        $dir = public_path("attachments/pending/{$token}");
        if (is_dir($dir) && count((array) glob($dir . '/*')) === 0) {
            @rmdir($dir);
        }
        Cache::forget('mm_upload_token_' . $token);

        return $moved;
    }

    // ─── Authenticated: delete ────────────────────────────────────────────────

    public function destroy($id)
    {
        $attachment = Attachment::findOrFail($id);

        // Delete physical file
        $fullPath = public_path($attachment->file_path);
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        $attachment->delete();

        return response()->json(['success' => true]);
    }

    // ─── Authenticated: generate mobile upload token ──────────────────────────

    /**
     * Generates a 24-hour token and returns the mobile upload URL.
     * Called via AJAX from the detail page to build the QR code.
     */
    public function generateToken(Request $request, string $type, int $id)
    {
        if (! in_array($type, ['invoice', 'purchase'])) {
            abort(404);
        }

        [$modelClass] = $this->resolveModel($type);
        $modelClass::findOrFail($id); // 404 if not found

        $token = Str::random(48);
        Cache::put('mm_upload_token_' . $token, [
            'type' => $type,
            'id'   => $id,
        ], now()->addHours(24));

        return response()->json([
            'success'    => true,
            'upload_url' => route('attachments.mobile-upload', ['token' => $token]),
            'expires_in' => '24 hours',
        ]);
    }

    // ─── Public (no auth): mobile upload page ────────────────────────────────

    public function mobileUploadPage(string $token)
    {
        $data = Cache::get('mm_upload_token_' . $token);

        if (! $data) {
            return view('attachments.expired');
        }

        // Pending session — the parent (e.g. a new invoice) isn't created yet.
        if (! empty($data['pending'])) {
            return view('attachments.mobile-upload', [
                'token'   => $token,
                'type'    => $data['type'],
                'id'      => null,
                'model'   => null,
                'pending' => true,
            ]);
        }

        [$modelClass] = $this->resolveModel($data['type']);
        $model = $modelClass::find($data['id']);

        if (! $model) {
            abort(404);
        }

        return view('attachments.mobile-upload', [
            'token'   => $token,
            'type'    => $data['type'],
            'id'      => $data['id'],
            'model'   => $model,
            'pending' => false,
        ]);
    }

    // ─── Public (no auth): handle mobile upload POST ──────────────────────────

    public function mobileUploadStore(Request $request, string $token)
    {
        $data = Cache::get('mm_upload_token_' . $token);

        if (! $data) {
            return response()->json(['error' => 'This upload link has expired. Please ask for a new QR code.'], 403);
        }

        $request->validate([
            'files'   => 'required|array|min:1',
            'files.*' => 'required|file|mimes:jpg,jpeg,png,gif,webp,pdf|max:10240',
            'label'   => 'nullable|string|max:100',
        ]);

        [$modelClass, $type] = $this->resolveModel($data['type']);
        $isPending = ! empty($data['pending']);

        $uploaded = 0;
        foreach ($request->file('files') as $file) {
            if ($isPending) {
                $this->storeFile($file, "attachments/pending/{$token}", [
                    'attachable_type' => $modelClass,
                    'attachable_id'   => null,
                    'upload_token'    => $token,
                    'label'           => $request->label ?? null,
                    'uploaded_by'     => null, // mobile = no auth
                ]);
            } else {
                $this->storeFile($file, "attachments/{$type}/{$data['id']}", [
                    'attachable_type' => $modelClass,
                    'attachable_id'   => $data['id'],
                    'label'           => $request->label ?? null,
                    'uploaded_by'     => null, // mobile = no auth
                ]);
            }
            $uploaded++;
        }

        return response()->json([
            'success'  => true,
            'uploaded' => $uploaded,
            'message'  => "{$uploaded} file(s) uploaded successfully.",
        ]);
    }

    // ─── Documents Manager: index ─────────────────────────────────────────────

    public function docIndex(Request $request)
    {
        $tab    = $request->get('tab', 'invoice');
        $from   = $request->get('from');
        $to     = $request->get('to');
        $label  = $request->get('label');
        $search = $request->get('search');

        $invoiceLabels  = ['Aadhaar Card', 'PAN Card', 'Driving Licence', 'Voter ID', 'Passport', 'Other ID Proof'];
        $purchaseLabels = ['Original Bill', 'Device Photo', 'Box Photo', 'Accessories Photo', 'Other Document'];
        $allLabels      = array_unique(array_merge($invoiceLabels, $purchaseLabels));

        // Invoice attachments (exclude not-yet-linked pending uploads)
        $invoiceAtts = Attachment::where('attachable_type', Invoice::class)
            ->whereNotNull('attachable_id')
            ->with(['attachable', 'uploader'])
            ->when($label,  fn($q) => $q->where('label', $label))
            ->when($search, fn($q) => $q->whereHas('attachable', fn($sq) =>
                $sq->where('customer_name', 'like', "%{$search}%")
                   ->orWhere('customer_no',  'like', "%{$search}%")
                   ->orWhere('invoice_no',   'like', "%{$search}%")
            ))
            ->when($from, fn($q) => $q->whereHas('attachable', fn($sq) =>
                $sq->whereDate('invoice_date', '>=', $from)
            ))
            ->when($to, fn($q) => $q->whereHas('attachable', fn($sq) =>
                $sq->whereDate('invoice_date', '<=', $to)
            ))
            ->orderBy('created_at', 'desc')
            ->paginate(20, ['*'], 'ipage')
            ->withQueryString();

        // Purchase attachments (exclude not-yet-linked pending uploads)
        $purchaseAtts = Attachment::where('attachable_type', Purchase::class)
            ->whereNotNull('attachable_id')
            ->with(['attachable', 'uploader'])
            ->when($label,  fn($q) => $q->where('label', $label))
            ->when($search, fn($q) => $q->whereHas('attachable', fn($sq) =>
                $sq->where('model', 'like', "%{$search}%")
                   ->orWhere('imei',  'like', "%{$search}%")
                   ->orWhere('purchase_from', 'like', "%{$search}%")
            ))
            ->when($from, fn($q) => $q->whereHas('attachable', fn($sq) =>
                $sq->whereDate('purchase_date', '>=', $from)
            ))
            ->when($to, fn($q) => $q->whereHas('attachable', fn($sq) =>
                $sq->whereDate('purchase_date', '<=', $to)
            ))
            ->orderBy('created_at', 'desc')
            ->paginate(20, ['*'], 'ppage')
            ->withQueryString();

        return view('attachments.index', compact(
            'tab', 'from', 'to', 'label', 'search',
            'invoiceAtts', 'purchaseAtts',
            'allLabels', 'invoiceLabels', 'purchaseLabels'
        ));
    }

    // ─── Documents Manager: export (PDF / Excel / ZIP) ────────────────────────

    public function export(Request $request)
    {
        $tab    = $request->get('tab', 'invoice');
        $format = $request->get('format', 'excel');
        $from   = $request->get('from');
        $to     = $request->get('to');
        $label  = $request->get('label');
        $search = $request->get('search');

        $isInvoice  = $tab === 'invoice';
        $modelClass = $isInvoice ? Invoice::class : Purchase::class;
        $dateField  = $isInvoice ? 'invoice_date' : 'purchase_date';

        $query = Attachment::where('attachable_type', $modelClass)
            ->whereNotNull('attachable_id')
            ->with(['attachable', 'uploader'])
            ->when($label, fn($q) => $q->where('label', $label));

        if ($isInvoice) {
            $query
                ->when($search, fn($q) => $q->whereHas('attachable', fn($sq) =>
                    $sq->where('customer_name', 'like', "%{$search}%")
                       ->orWhere('customer_no',  'like', "%{$search}%")
                       ->orWhere('invoice_no',   'like', "%{$search}%")
                ))
                ->when($from, fn($q) => $q->whereHas('attachable', fn($sq) =>
                    $sq->whereDate('invoice_date', '>=', $from)
                ))
                ->when($to, fn($q) => $q->whereHas('attachable', fn($sq) =>
                    $sq->whereDate('invoice_date', '<=', $to)
                ));
        } else {
            $query
                ->when($search, fn($q) => $q->whereHas('attachable', fn($sq) =>
                    $sq->where('model', 'like', "%{$search}%")
                       ->orWhere('imei',  'like', "%{$search}%")
                       ->orWhere('purchase_from', 'like', "%{$search}%")
                ))
                ->when($from, fn($q) => $q->whereHas('attachable', fn($sq) =>
                    $sq->whereDate('purchase_date', '>=', $from)
                ))
                ->when($to, fn($q) => $q->whereHas('attachable', fn($sq) =>
                    $sq->whereDate('purchase_date', '<=', $to)
                ));
        }

        $records = $query->orderBy('created_at', 'desc')->get();
        $suffix  = now()->format('Y-m-d');

        // ── Excel ──────────────────────────────────────────────────────────────
        if ($format === 'excel') {
            $filename = 'mm-' . $tab . '-documents-' . $suffix . '.xlsx';
            return Excel::download(new AttachmentsExport($records, $tab), $filename);
        }

        // ── ZIP ────────────────────────────────────────────────────────────────
        if ($format === 'zip') {
            return $this->buildZip($records, $tab, $suffix);
        }

        // ── PDF (printable HTML page) ──────────────────────────────────────────
        if ($format === 'pdf') {
            // Group by parent record id
            $grouped = $records->groupBy('attachable_id');

            return view('attachments.export-pdf', [
                'grouped'    => $grouped,
                'tab'        => $tab,
                'from'       => $from,
                'to'         => $to,
                'label'      => $label,
                'search'     => $search,
                'total'      => $records->count(),
                'exportedAt' => now()->format('d M Y, g:i A'),
            ]);
        }

        return redirect()->back()->with('error', 'Invalid export format selected.');
    }

    // ─── ZIP builder helper ───────────────────────────────────────────────────

    private function buildZip($records, string $tab, string $suffix)
    {
        $zipName = 'mm-' . $tab . '-documents-' . $suffix . '-' . time() . '.zip';
        $zipPath = storage_path('app/' . $zipName);

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return redirect()->back()->with('error', 'Could not create ZIP archive.');
        }

        foreach ($records as $att) {
            $fullPath = public_path($att->file_path);
            if (! file_exists($fullPath)) {
                continue;
            }

            if ($tab === 'invoice') {
                $invoiceNo = $att->attachable?->invoice_no ?? 'unknown';
                $folder    = 'Invoice_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $invoiceNo);
            } else {
                $model  = $att->attachable?->model ?? 'Unknown';
                $folder = 'Stock_' . $att->attachable_id . '_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $model);
            }

            $label   = $att->label ? preg_replace('/[^A-Za-z0-9\-]/', '_', $att->label) . '_' : '';
            $safeName = $label . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $att->file_name);

            $zip->addFile($fullPath, $folder . '/' . $safeName);
        }

        $zip->close();

        return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
    }

    // ─── Helper ───────────────────────────────────────────────────────────────

    private function resolveModel(string $type): array
    {
        return match ($type) {
            'invoice'  => [Invoice::class, 'invoice'],
            'purchase' => [Purchase::class, 'purchase'],
            default    => abort(404),
        };
    }
}
