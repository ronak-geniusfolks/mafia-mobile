<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Purchase;
use Carbon\Carbon;
use DateTimeImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $isSold = $request->input('is_sold');
        $year = $request->input('year');
        $storage = $request->input('storage');
        $color = $request->input('color');
        $sortDirection = $request->input('direction', 'desc');
        if ($request->query('download') === 'csv') {
            $fileName = 'stock';
            if ($color) {
                $fileName .= ''.$color;
            }
            if ($storage) {
                $fileName .= '-'.$storage;
            }
            if ($year) {
                $fileName .= '-'.$year;
            }

            $fileName .= '-.csv';
            $headers = [
                'Content-type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=$fileName",
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ];
            $allPurchases = Purchase::query()
                ->when($search, function ($query, $search): void {
                    $query->where(function ($q) use ($search): void {
                        $q->where('id', 'like', "%{$search}%")
                            ->orWhere('imei', 'like', "%{$search}%")
                            ->orWhere('model', 'like', "%{$search}%")
                            ->orWhere('purchase_from', 'like', "%{$search}%");
                    });
                })
                ->when(($isSold !== ''), function ($query) use ($isSold): void {
                    if ($isSold === 2) {
                        $query->where('is_sold', 0);
                    } else {
                        $query->where('is_sold', 1);
                    }
                })
                ->when($year, function ($query) use ($year): void {
                    $query->whereYear('purchase_date', $year);
                })
                ->when($storage, function ($query) use ($storage): void {
                    $query->where('storage', 'like', "%{$storage}");
                })
                ->when($color, function ($query) use ($color): void {
                    $query->where('color', $color);
                })
                ->where('deleted', 0)
                ->orderBy('id', 'asc')
                ->get();

            $callback = function () use ($allPurchases): void {
                $file = fopen('php://output', 'w+');

                // Add the CSV header (modify this based on your model)
                fputcsv($file, ['Sr No', 'Purchase Date', 'IMEI', 'Model', 'Storage', 'Color', 'Sell Date', 'Buy From', 'Mobile No', 'Buy Cost', 'Repairing', 'Buy Price', 'Sold', 'Remark'], ';'); // Specify the custom separator (;)

                // Add the data rows
                $sellDate = '';
                $totalCost = $repairingCost = $purchasePrice = 0;
                foreach ($allPurchases as $row) {
                    fputcsv($file, [
                        $row->id,
                        Carbon::parse($row->purchase_date)->format('d/m/Y'),
                        (string) $row->imei,
                        $row->model,
                        $row->storage,
                        $row->color,
                        ($row->sell_date) ? Carbon::parse($row->sell_date)->format('d/m/Y') : '',
                        $row->purchase_from,
                        $row->contactno,
                        $row->purchase_cost,
                        $row->repairing_charge ?: 0,
                        $row->purchase_price,
                        ($row->is_sold === 1) ? 'Yes' : 'No',
                        $row->remark,
                    ], ';');
                    $totalCost += $row->purchase_cost;
                    $repairingCost += $row->repairing_charge;
                    $purchasePrice += $row->purchase_price;
                }
                fputcsv($file, ['', '', '', '', '', '', '', '', '', '', '', '', '', ''], ';');
                fputcsv($file, ['', '', '', '', '', '', '', '', '', $totalCost, $repairingCost, $purchasePrice, '', ''], ';');

                fclose($file);
            };

            return Response::stream($callback, 200, $headers);
        }
        $allPurchases = Purchase::query()
            ->when($search, function ($query, $search): void {
                $query->where(function ($q) use ($search): void {
                    $q->where('id', 'like', "%{$search}%")
                        ->orWhere('imei', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%")
                        ->orWhere('purchase_from', 'like', "%{$search}%");
                });
            })
            ->when((!empty($isSold)), function ($query) use ($isSold): void {
                if ($isSold == 2) {
                    $query->where('is_sold', 0);
                } else {
                    $query->where('is_sold', 1);
                }
            })
            ->when($year, function ($query) use ($year): void {
                $query->whereYear('purchase_date', $year);
            })
            ->when($storage, function ($query) use ($storage): void {
                $query->where('storage', 'like', "%{$storage}");
            })
            ->when($color, function ($query) use ($color): void {
                $query->where('color', $color);
            })
            ->where('deleted', 0)
            ->orderBy('id', $sortDirection)
            ->paginate(20);
        $colors = Purchase::select('color')
            ->distinct()
            ->whereNotNull('color')
            ->when($search, function ($query, $search): void {
                $query->where(function ($q) use ($search): void {
                    $q->where('id', 'like', "%{$search}%")
                        ->orWhere('imei', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%")
                        ->orWhere('purchase_from', 'like', "%{$search}%");
                });
            })
            ->when(($isSold !== ''), function ($query) use ($isSold): void {
                if ($isSold === 2) {
                    $query->where('is_sold', 0);
                } else {
                    $query->where('is_sold', 1);
                }
            })
            ->when($year, function ($query) use ($year): void {
                $query->whereYear('purchase_date', $year);
            })
            ->when($storage, function ($query) use ($storage): void {
                $query->where('storage', 'like', "%{$storage}");
            })
            ->where('deleted', 0)
            ->orderBy('color', 'asc') // Optional: order by color alphabetically
            ->get();

        return view('purchase.purchases', [
            'allPurchases' => $allPurchases,
            'issold' => $isSold,
            'year' => $year,
            'storage' => $storage,
            'sortDirection' => $sortDirection,
            'totalItems' => $allPurchases->total(),
            'colors' => $colors,
            'selectedcolor' => $color,
        ]);
    }

    // New purchase form
    public function newPurchase()
    {
        $purchase = new Purchase();

        return view('purchase.newpurchase', ['purchase' => $purchase]);
    }

    // New multiple purchase form
    public function newMultiplePurchase()
    {
        return view('purchase.newpurchase-multiple');
    }

    public function purchaseDetail($id)
    {
        $purchase = Purchase::findOrFail($id);

        return view('purchase.purchasedetail', ['purchase' => $purchase]);
    }

    public function savePurchase(Request $request)
    {
        // Check if it's multiple stock entry
        if ($request->entry_type === 'multiple') {
            return $this->saveMultiplePurchases($request);
        }

        $request->validate([
            'model' => 'required',
            'imei' => 'required',
            'color' => 'required',
            'storage' => 'required',
            'purchase_cost' => 'required',
        ]);

        // If request has ID → update, else create new
        $purchase = $request->id
            ? Purchase::findOrFail($request->id)
            : new Purchase;

        // Assign common fields
        $purchase->device_type = $request->device_type;
        $purchase->model = $request->model;
        $purchase->imei = $request->imei;
        $purchase->purchase_date = $request->purchase_date ?? Carbon::now()->format('Y-m-d');
        $purchase->color = $request->color;
        $purchase->storage = $request->storage;
        $purchase->purchase_from = $request->purchase_from;
        $purchase->contactno = $request->contactno;
        $purchase->warrentydate = $request->warrentydate;
        $purchase->purchase_cost = $request->purchase_cost;
        $purchase->repairing_charge = $request->repairing_charge ?? 0;
        $purchase->remark = $request->remark;
        $purchase->user_id = Auth::id();

        // Calculate total purchase price
        $purchaseCost = $purchase->purchase_cost > 0 ? $purchase->purchase_cost : 0;
        $repairingCharge = $purchase->repairing_charge > 0 ? $purchase->repairing_charge : 0;
        $purchase->purchase_price = (float) ($purchaseCost + $repairingCharge);

        // Handle documents
        if ($request->hasFile('document') && count($request->file('document')) > 0) {
            $documents = [];
            foreach ($request->file('document') as $file) {
                $filename = date('YmdHi').'_'.$file->getClientOriginalName();
                $file->move(public_path('documents/purchases/'), $filename);
                $documents[] = 'documents/purchases/'.$filename;
            }
            $purchase->document = implode(',', $documents);
        }

        // Save or update
        $isNew = ! $request->id;
        $purchase->save();

        return redirect()
            ->route('allpurchases')
            ->withStatus($isNew ? 'Stock Added Successfully...' : 'Stock Updated Successfully...');
    }

    public function deleteStock($id, Request $request)
    {
        Purchase::where('id', $id)->update(['deleted' => 1]);

        return redirect()->route('allpurchases')->withStatus('Stock Deleted Successfully..');
    }

    public function editPurchase($id)
    {
        $purchase = Purchase::findOrFail($id);

        return view('purchase.newpurchase', ['purchase' => $purchase]);
    }

    public function updatePurchase(Request $request)
    {
        $request->validate([
            'model' => 'required',
            'imei' => 'required',
            'purchase_date' => 'required',
            'storage' => 'required',
            'purchase_cost' => 'required',
        ]);
        $purchase = Purchase::findOrFail($request->id);
        $purchase->model = $request->model;
        $purchase->device_type = $request->device_type;
        $purchase->imei = $request->imei;
        $purchase->purchase_date = $request->purchase_date;
        $purchase->color = $request->color;
        $purchase->storage = $request->storage;
        $purchase->purchase_from = $request->purchase_from;
        $purchase->contactno = $request->contactno;
        $purchase->purchase_cost = $request->purchase_cost;
        $purchase->repairing_charge = $request->repairing_charge;
        $purchase->warrentydate = $request->warrentydate;

        $repairingCharge = $purchase_price = $purchase->purchase_price = 0;
        if ($purchase->repairing_charge > 0) {
            $repairingCharge = $purchase->repairing_charge;
        }
        if ($purchase->purchase_cost > 0) {
            $purchaseCost = $purchase->purchase_cost;
        }
        $purchase->purchase_price = (float) ($purchaseCost + $repairingCharge);
        $purchase->remark = $request->remark;
        $purchase->user_id = Auth::user()->id;

        if ($request->file('document') !== null && count($request->file('document')) > 0) {
            $purchase->document = $this->uploadFiles($request->file('document'));
        }
        $purchase->update();

        return redirect()->route('allpurchases')->withStatus("Stock Updated Successfully.. :: '".$purchase->model."'");
    }

    // Function for uploading documents
    public function uploadFiles($documents): string
    {
        foreach ($documents as $file) {
            $filename = time().$file->getClientOriginalName();
            $file->move(public_path('documents/purchases/'), $filename);
            $docs[] = $filename;
        }

        return implode(',', $docs);
    }

    public function importStocks()
    {
        return view('purchase.import');
    }

    public function downloadStock(Request $request): void
    {
        echo 'Download Stocks';
        exit;
    }

    public function importStocksData(Request $request)
    {
        $request->validate([
            'stockdata' => 'required|mimes:csv,txt',
        ]);
        $file = $request->file('stockdata');

        if (($handle = fopen($file, 'r')) !== false) {
            fgetcsv($handle, 1000, ';');
            while (($data = fgetcsv($handle, 1000, ';')) !== false) {
                $is_sold = $data[8] !== null && $data[8] !== '' && $data[8] !== '0' ? 1 : 0;
                Purchase::create([
                    'purchase_date' => DateTimeImmutable::createFromFormat('d.m.Y', $data[1])->format('Y-m-d'),
                    'imei' => $data[2],
                    'model' => $data[3],
                    'storage' => $data[4],
                    'color' => $data[5],
                    'purchase_from' => $data[6],
                    'contactno' => ($data[7] !== null && $data[7] !== '' && $data[7] !== '0') ? $data[7] : null,
                    'purchase_cost' => trim(($data[9] !== null && $data[9] !== '' && $data[9] !== '0') ? $data[9] : 0),
                    'repairing_charge' => trim($data[10] !== null && $data[10] !== '' && $data[10] !== '0' ? $data[10] : 0),
                    'remark' => $data[11],
                    'purchase_price' => trim((string) $data[12]),
                    'user_id' => Auth::user()->id,
                    'device_type' => 'Phone',
                    'is_sold' => $is_sold,
                ]);
            }
            fclose($handle);
        }

        return redirect()->route('allpurchases')->withStatus('Stocks imported successfully.');
    }

    private function saveMultiplePurchases(Request $request)
    {
        $request->validate([
            'model' => 'required',
            'stock_items' => 'required|array|min:1',
            'stock_items.*.imei' => 'required',
            'stock_items.*.storage' => 'required',
            'stock_items.*.color' => 'required',
            'stock_items.*.purchase_cost' => 'required|numeric',
        ]);

        $now = Carbon::now();
        $userId = Auth::id();
        $purchaseDate = $request->purchase_date ?? $now->format('Y-m-d');

        $purchasesData = [];
        $totalCost = 0;

        foreach ($request->stock_items as $stockItem) {
            $purchaseCost = (float) ($stockItem['purchase_cost']);
            $repairingCharge = (float) ($request->repairing_charge ?? 0);
            $purchasePrice = $purchaseCost + $repairingCharge;

            $totalCost += $purchasePrice;

            $purchasesData[] = [
                'device_type' => $request->device_type,
                'model' => $request->model,
                'purchase_date' => $purchaseDate,
                'purchase_from' => $request->purchase_from ?? null,
                'contactno' => $request->contactno ?? null,
                'warrentydate' => $request->warrentydate ?? null,
                'repairing_charge' => $repairingCharge,
                'remark' => $request->remark ?? null,
                'user_id' => $userId,
                'imei' => $stockItem['imei'],
                'color' => $stockItem['color'],
                'storage' => $stockItem['storage'],
                'purchase_cost' => $purchaseCost,
                'purchase_price' => $purchasePrice,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // ✅ Insert all purchases in one go (1 DB query)
        $result = Purchase::insert($purchasesData);

        $message = count($purchasesData)." stock items added successfully for model '"
            .e($request->model)."' (Total Cost: ₹".number_format($totalCost, 2).')';

        return redirect()->route('allpurchases')->withStatus($result ? $message : 'Failed to add stock items');
    }
}
