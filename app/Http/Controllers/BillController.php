<?php
namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Dealer;
use App\Models\DealerPayment;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BillController extends Controller
{
    public function index()
    {
        $allBills = Bill::with(['items', 'dealer'])->orderBy('bill_date', 'desc')->get();

        return view('bill.index', ['allBills' => $allBills]);
    }

    public function newBill()
    {
        $stocksModel = Purchase::where('deleted', 0)->orderBy('purchase_date', 'desc')->get();
        $dealers = Dealer::all();

        $lastBill   = Bill::orderBy('id', 'desc')->first();
        $currentYear   = date('Y');
        $nextBillNo = 1; // Default to 1
        if ($lastBill) {
            // Extract the year from the last bill number
            preg_match('/BL(\d{4})/', (string) $lastBill->bill_no, $matches);
            $lastYear = $matches[1] ?? null;

            if ($lastYear == $currentYear) {
                // If the year matches, increment the bill number
                $lastId        = intval(substr((string) $lastBill->bill_no, -4)); // Get the last numeric part
                $nextBillNo = $lastId + 1;
            }
        }

        $formattedNumber = 'BL' . $currentYear . str_pad($nextBillNo, 4, '0', STR_PAD_LEFT);

        return view('bill.add', [
            'stocksModel' => $stocksModel,
            'dealers' => $dealers,
            'lastId'      => $formattedNumber,
        ]);
    }

    public function createBill(Request $request)
    {
        // Convert date format from dd/mm/yyyy to Y-m-d using Carbon
        if ($request->bill_date) {
            try {
                $convertedDate = Carbon::createFromFormat('d/m/Y', $request->bill_date)->format('Y-m-d');
                $request->merge(['bill_date' => $convertedDate]);
            } catch (\Exception $e) {
                // If conversion fails, keep original value for validation to handle
            }
        }

        if ($request->warranty_expiry_date) {
            try {
                $convertedDate = Carbon::createFromFormat('d/m/Y', $request->warranty_expiry_date)->format('Y-m-d');
                $request->merge(['warranty_expiry_date' => $convertedDate]);
            } catch (\Exception $e) {
                // If conversion fails, keep original value for validation to handle
            }
        }

        // Validate request
        $request->validate([
            'items'             => 'required|array|min:1',
            'items.*.item_id'   => 'required|integer|exists:purchases,id',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.quantity'  => 'required|integer|min:1',
            'dealer_id'        => 'required|integer|exists:dealers,id',
            'bill_date'        => 'required|date',
            'bill_no'          => 'required|string',
            'total_amount'     => 'required|numeric|min:0',
            'net_amount'       => 'required|numeric|min:0',
            'payment_type'     => 'required|string|in:cash,credit',
        ]);

        // Use database transaction
        DB::beginTransaction();
        try {
            // Calculate cash and credit amounts based on payment type
            $cashAmount = 0;
            $creditAmount = 0;
            
            if ($request->payment_type === 'cash') {
                $cashAmount = $request->net_amount;
                $creditAmount = 0;
            } else {
                $cashAmount = $request->cash_amount ?? 0;
                $creditAmount = $request->net_amount - $cashAmount;
            }

            // Create bill
            $bill = Bill::create([
                'dealer_id'         => $request->dealer_id,
                'bill_date'         => $request->bill_date,
                'bill_no'           => $request->bill_no,
                'total_amount'      => $request->total_amount,
                'net_amount'        => $request->net_amount,
                'cgst_rate'         => $request->cgst_rate ?? 0,
                'sgst_rate'         => $request->sgst_rate ?? 0,
                'igst_rate'         => $request->igst_rate ?? 0,
                'cgst_amount'       => $request->cgst_amount ?? 0,
                'sgst_amount'       => $request->sgst_amount ?? 0,
                'igst_amount'       => $request->igst_amount ?? 0,
                'tax_amount'        => $request->tax_amount ?? 0,
                'discount'          => $request->discount_amount ?? 0,
                'discount_rate'     => $request->discount_rate ?? 0,
                'declaration'       => $request->declaration ?? null,
                'payment_type'      => $request->payment_type,
                'cash_amount'       => $cashAmount,
                'credit_amount'     => $creditAmount,
                'paid_amount'       => $request->payment_type === 'cash' ? $request->net_amount : 0,
                'is_paid'           => $request->payment_type === 'cash' ? 1 : 0,
                'bill_by'           => Auth::id(),
            ]);

            // If payment is cash, record the payment in dealer_payments table
            if ($request->payment_type === 'cash') {
                DealerPayment::create([
                    'dealer_id'       => $request->dealer_id,
                    'bill_id'         => $bill->id,
                    'payment_amount'  => $request->net_amount,
                    'payment_date'    => $request->bill_date,
                    'payment_type'    => 'cash',
                    'note'            => 'Full payment for bill ' . $bill->bill_no,
                    'created_by'      => Auth::id(),
                ]);
            }

            // Create bill items and mark purchases as sold
            $totalProfit = 0;
            foreach ($request->items as $item) {
                $purchase = Purchase::findOrFail($item['item_id']);

                // Check if item is already sold
                if ($purchase->is_sold) {
                    DB::rollBack();
                    return back()->withErrors(['error' => 'Item ' . $purchase->imei . ' is already sold.'])->withInput();
                }

                $unitPrice = $item['unit_price'];
                $totalAmount = $unitPrice * $item['quantity'];
                $profit = ($unitPrice - $purchase->purchase_price) * $item['quantity'];

                // Convert warranty date if provided
                $warrantyDate = null;
                if (!empty($item['warranty_expiry_date'])) {
                    $warrantyDate = Carbon::createFromFormat('d/m/Y', $item['warranty_expiry_date'])->format('Y-m-d');
                }

                // Create bill item
                BillItem::create([
                    'bill_id'            => $bill->id,
                    'item_id'            => $item['item_id'],
                    'item_description'   => $item['item_description'],
                    'quantity'           => $item['quantity'],
                    'unit_price'         => $unitPrice,
                    'total_amount'       => $totalAmount,
                    'warranty_expiry_date' => $warrantyDate,
                    'profit'             => $profit,
                ]);

                $totalProfit += $profit;

                // Mark purchase as sold
                $purchase->update([
                    'is_sold'   => 1,
                    'sell_date' => $request->bill_date,
                ]);
            }

            DB::commit();

            return redirect()->route('print-bill', $bill->id);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'An error occurred: ' . $e->getMessage()])->withInput();
        }
    }

    public function billDetail($id)
    {
        $bill = Bill::with(['items.purchase', 'user', 'dealer'])->findOrFail($id);
        $amountInWords = $this->amoutInWords(floatval($bill->net_amount));

        return view('bill.detail', ['bill' => $bill, 'amountInWords' => $amountInWords]);
    }

    public function printBill($id)
    {
        $bill       = Bill::with(['items.purchase', 'user', 'dealer'])->findOrFail($id);
        $amountInWords = $this->amoutInWords(floatval($bill->net_amount));

        return view('bill.print', ['bill' => $bill, 'amountInWords' => $amountInWords]);
    }

    public function fetchModelData($imei)
    {
        $purchase = Purchase::where('imei', 'LIKE', "%{$imei}%")
            ->where('is_sold', 0)
            ->first();
        if (!$purchase) {
            return response()->json([
                'error' => 'IMEI Not in stock',
            ]);
        }
        return response()->json([
            'purchase' => $purchase,
        ]);
    }

    public function fetchDealerData($id)
    {
        $dealer = Dealer::find($id);
        if (!$dealer) {
            return response()->json([
                'error' => 'Dealer not found',
            ], 404);
        }
        return response()->json([
            'dealer' => $dealer,
        ]);
    }

    public function editBill($id)
    {
        $bill     = Bill::with('items.purchase')->findOrFail($id);
        $stocksModel = Purchase::where('deleted', 0)->orderBy('purchase_date', 'desc')->get();
        $dealers = Dealer::all();

        return view('bill.edit', ['bill' => $bill, 'stocksModel' => $stocksModel, 'dealers' => $dealers]);
    }

    public function updateBill(Request $request, $id)
    {
        $bill = Bill::with('items.purchase')->findOrFail($id);

        // Convert date format from dd/mm/yyyy to Y-m-d using Carbon
        if ($request->bill_date) {
            try {
                $convertedDate = Carbon::createFromFormat('d/m/Y', $request->bill_date)->format('Y-m-d');
                $request->merge(['bill_date' => $convertedDate]);
            } catch (\Exception $e) {
                // If conversion fails, keep original value for validation to handle
            }
        }

        if ($request->warranty_expiry_date) {
            try {
                $convertedDate = Carbon::createFromFormat('d/m/Y', $request->warranty_expiry_date)->format('Y-m-d');
                $request->merge(['warranty_expiry_date' => $convertedDate]);
            } catch (\Exception $e) {
                // If conversion fails, keep original value for validation to handle
            }
        }

        // Validate dates after conversion
        $request->validate([
            'items'             => 'required|array|min:1',
            'items.*.item_id'   => 'required|integer|exists:purchases,id',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.quantity'  => 'required|integer|min:1',
            'dealer_id'        => 'required|integer|exists:dealers,id',
            'bill_date'        => 'required|date',
            'total_amount'     => 'required|numeric|min:0',
            'net_amount'       => 'required|numeric|min:0',
            'payment_type'     => 'required|string|in:cash,credit',
        ]);

        // Use database transaction
        DB::beginTransaction();
        try {
            // Mark all old purchases as unsold
            foreach ($bill->items as $item) {
                if ($item->purchase) {
                    $item->purchase->update([
                        'is_sold'   => 0,
                        'sell_date' => null
                    ]);
                }
            }

            // Delete old bill items
            $bill->items()->delete();

            // Calculate cash and credit amounts based on payment type
            $cashAmount = 0;
            $creditAmount = 0;
            
            if ($request->payment_type === 'cash') {
                $cashAmount = $request->net_amount;
                $creditAmount = 0;
            } else {
                $cashAmount = $request->cash_amount ?? 0;
                $creditAmount = $request->net_amount - $cashAmount;
            }

            // Update bill details
            $bill->update([
                'dealer_id'         => $request->dealer_id,
                'bill_date'         => $request->bill_date,
                'total_amount'      => $request->total_amount,
                'net_amount'        => $request->net_amount,
                'cgst_rate'         => $request->cgst_rate ?? 0,
                'sgst_rate'         => $request->sgst_rate ?? 0,
                'igst_rate'         => $request->igst_rate ?? 0,
                'cgst_amount'       => $request->cgst_amount ?? 0,
                'sgst_amount'       => $request->sgst_amount ?? 0,
                'igst_amount'       => $request->igst_amount ?? 0,
                'tax_amount'        => $request->tax_amount ?? 0,
                'discount'          => $request->discount_amount ?? 0,
                'discount_rate'     => $request->discount_rate ?? 0,
                'declaration'       => $request->declaration ?? null,
                'payment_type'      => $request->payment_type,
                'cash_amount'       => $cashAmount,
                'credit_amount'     => $creditAmount,
                'paid_amount'       => $request->payment_type === 'cash' ? $request->net_amount : ($bill->paid_amount ?? 0),
                'is_paid'           => $request->payment_type === 'cash' ? 1 : ($bill->is_paid ?? 0),
                'bill_by'           => Auth::id(),
            ]);

            // Handle payment record update for cash payments
            if ($request->payment_type === 'cash') {
                // Delete old payment records for this bill if any
                $bill->payments()->delete();
                
                // Create new payment record
                DealerPayment::create([
                    'dealer_id'       => $request->dealer_id,
                    'bill_id'         => $bill->id,
                    'payment_amount'  => $request->net_amount,
                    'payment_date'    => $request->bill_date,
                    'payment_type'    => 'cash',
                    'note'            => 'Full payment for bill ' . $bill->bill_no,
                    'created_by'      => Auth::id(),
                ]);
            }

            // Create new bill items and mark purchases as sold
            $totalProfit = 0;
            foreach ($request->items as $item) {
                $purchase = Purchase::findOrFail($item['item_id']);

                // Check if item is already sold
                if ($purchase->is_sold) {
                    DB::rollBack();
                    return back()->withErrors(['error' => 'Item ' . $purchase->imei . ' is already sold.'])->withInput();
                }

                $unitPrice = $item['unit_price'];
                $totalAmount = $unitPrice * $item['quantity'];
                $profit = ($unitPrice - $purchase->purchase_price) * $item['quantity'];

                // Convert warranty date if provided
                $warrantyDate = null;
                if (!empty($item['warranty_expiry_date'])) {
                    $warrantyDate = Carbon::createFromFormat('d/m/Y', $item['warranty_expiry_date'])->format('Y-m-d');
                }

                // Create bill item
                BillItem::create([
                    'bill_id'            => $bill->id,
                    'item_id'            => $item['item_id'],
                    'item_description'   => $item['item_description'],
                    'quantity'           => $item['quantity'],
                    'unit_price'         => $unitPrice,
                    'total_amount'       => $totalAmount,
                    'warranty_expiry_date' => $warrantyDate,
                    'profit'             => $profit,
                ]);

                $totalProfit += $profit;

                // Mark purchase as sold
                $purchase->update([
                    'is_sold'   => 1,
                    'sell_date' => $request->bill_date,
                ]);
            }

            DB::commit();

            return redirect()->route('allbills')->withStatus('Bill Updated Successfully..');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'An error occurred: ' . $e->getMessage()])->withInput();
        }
    }

    public function deleteBill($id)
    {
        DB::beginTransaction();
        try {
            $bill = Bill::with('items.purchase')->findOrFail($id);

            // Mark the purchases as unsold so they can be sold again
            foreach ($bill->items as $item) {
                if ($item->purchase) {
                    $item->purchase->update([
                        'is_sold' => 0,
                        'sell_date' => null
                    ]);
                }
            }

            // Soft delete bill items
            $bill->items()->delete();

            // Soft delete the bill
            $bill->delete();

            DB::commit();

            return redirect()->route('allbills')->withStatus('Bill Deleted Successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'An error occurred while deleting the bill.']);
        }
    }

    public function amoutInWords(float $amount): string
    {
        $amount_after_decimal = round($amount - ($num = floor($amount)), 2) * 100;
        // Check if there is any number after decimal
        $amt_hundred  = null;
        $count_length = strlen($num);
        $x            = 0;
        $string       = [];
        $change_words = [0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty', 70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety'];
        $here_digits  = ['', 'Hundred', 'Thousand', 'Lakh', 'Crore'];

        while ($x < $count_length) {
            $get_divider = ($x == 2) ? 10 : 100;
            $amount      = floor($num % $get_divider);
            $num         = floor($num / $get_divider);
            $x += $get_divider == 10 ? 1 : 2;
            if ($amount !== 0.0) {
                $add_plural  = (($counter = count($string)) && $amount > 9) ? 's' : null;
                $amt_hundred = ($counter == 1 && $string[0]) ? ' and ' : null;
                $string[]    = ($amount < 21) ? $change_words[$amount] . ' ' . $here_digits[$counter] . $add_plural . ' ' . $amt_hundred : $change_words[floor($amount / 10) * 10] . ' ' . $change_words[$amount % 10] . ' ' . $here_digits[$counter] . $add_plural . ' ' . $amt_hundred;
            } else {
                $string[] = null;
            }
        }
        $implode_to_Rupees = implode('', array_reverse($string));
        $get_paise         = ($amount_after_decimal > 0) ? 'And ' . ($change_words[$amount_after_decimal / 10] . '
        ' . $change_words[$amount_after_decimal % 10]) . ' Paise' : '';

        return ($implode_to_Rupees !== '' && $implode_to_Rupees !== '0' ? $implode_to_Rupees . 'Only ' : '') . $get_paise;
    }
}
