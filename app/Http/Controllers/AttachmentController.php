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
            // Capture metadata BEFORE move()
            $origName  = $file->getClientOriginalName();
            $mimeType  = $file->getClientMimeType();
            $fileSize  = $file->getSize();
            $extension = $file->getClientOriginalExtension();

            $folder   = "attachments/{$type}/{$model->id}";
            File::ensureDirectoryExists(public_path($folder));
            $filename = date('YmdHis') . '_' . Str::slug(pathinfo($origName, PATHINFO_FILENAME)) . '.' . $extension;
            $file->move(public_path($folder), $filename);

            $attachment = Attachment::create([
                'attachable_type' => $modelClass,
                'attachable_id'   => $model->id,
                'file_path'       => $folder . '/' . $filename,
                'file_name'       => $origName,
                'file_type'       => $mimeType,
                'file_size'       => $fileSize,
                'label'           => $request->label ?? null,
                'uploaded_by'     => Auth::id(),
            ]);

            $uploaded[] = [
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

        return response()->json(['success' => true, 'attachments' => $uploaded]);
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

        [$modelClass] = $this->resolveModel($data['type']);
        $model = $modelClass::find($data['id']);

        if (! $model) {
            abort(404);
        }

        return view('attachments.mobile-upload', [
            'token' => $token,
            'type'  => $data['type'],
            'id'    => $data['id'],
            'model' => $model,
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
        $id = $data['id'];

        $uploaded = 0;
        foreach ($request->file('files') as $file) {
            // Capture metadata BEFORE move()
            $origName  = $file->getClientOriginalName();
            $mimeType  = $file->getClientMimeType();
            $fileSize  = $file->getSize();
            $extension = $file->getClientOriginalExtension();

            $folder   = "attachments/{$type}/{$id}";
            File::ensureDirectoryExists(public_path($folder));
            $filename = date('YmdHis') . '_' . Str::slug(pathinfo($origName, PATHINFO_FILENAME)) . '.' . $extension;
            $file->move(public_path($folder), $filename);

            Attachment::create([
                'attachable_type' => $modelClass,
                'attachable_id'   => $id,
                'file_path'       => $folder . '/' . $filename,
                'file_name'       => $origName,
                'file_type'       => $mimeType,
                'file_size'       => $fileSize,
                'label'           => $request->label ?? null,
                'uploaded_by'     => null, // mobile = no auth
            ]);
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

        // Invoice attachments
        $invoiceAtts = Attachment::where('attachable_type', Invoice::class)
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

        // Purchase attachments
        $purchaseAtts = Attachment::where('attachable_type', Purchase::class)
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
