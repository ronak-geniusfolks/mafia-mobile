<?php
namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Dealer;
use App\Models\DealerPayment;
use App\Models\Purchase;
use App\Traits\ConvertsDates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BillController extends Controller
{
    use ConvertsDates;

    /**
     * Display a listing of bills.
     */
    public function index()
    {
        $allBills = Bill::with(['items', 'dealer', 'user'])
            ->notDeleted()
            ->orderBy('bill_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return view('bill.index', ['allBills' => $allBills]);
    }

    /**
     * Show the form for creating a new bill.
     */
    public function newBill()
    {
        // Only fetch unsold purchases - no need to load all
        $stocksModel = Purchase::where('deleted', 0)
            ->where('is_sold', 0)
            ->orderBy('purchase_date', 'desc')
            ->get();
        
        // Use simple query for dealers
        $dealers = Dealer::orderBy('name')->get();

        // Generate next bill number
        $nextBillNo = $this->generateNextBillNumber();

        return view('bill.add', [
            'stocksModel' => $stocksModel,
            'dealers' => $dealers,
            'lastId' => $nextBillNo,
        ]);
    }

    /**
     * Generate next bill number based on year.
     */
    private function generateNextBillNumber(): string
    {
        $currentYear = date('Y');
        $lastBill = Bill::where('bill_no', 'LIKE', "BL{$currentYear}%")
            ->orderByRaw('CAST(SUBSTRING(bill_no, -4) AS UNSIGNED) DESC')
            ->first();

        if ($lastBill) {
            preg_match('/BL\d{4}(\d{4})/', (string) $lastBill->bill_no, $matches);
            $nextBillNo = (intval($matches[1] ?? 0)) + 1;
        } else {
            $nextBillNo = 1;
        }

        return 'BL' . $currentYear . str_pad($nextBillNo, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Store a newly created bill.
     */
    public function createBill(Request $request)
    {
        // Convert date formats
        $request->merge([
            'bill_date' => $this->convertDateFormat($request->bill_date),
        ]);

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
            // Calculate payment amounts
            [$cashAmount, $creditAmount] = $this->calculatePaymentAmounts(
                $request->payment_type,
                $request->net_amount,
                $request->cash_amount ?? 0
            );

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

            // If payment is cash, record the payment
            if ($request->payment_type === 'cash') {
                $this->recordCashPayment($bill, $request->dealer_id, $request->bill_date);
            }

            // Create bill items and mark purchases as sold - optimized bulk operations
            $purchaseIds = collect($request->items)->pluck('item_id')->toArray();
            $purchases = Purchase::whereIn('id', $purchaseIds)
                ->where('is_sold', 0)
                ->get()
                ->keyBy('id');

            // Validate all purchases are available
            foreach ($request->items as $item) {
                if (!isset($purchases[$item['item_id']])) {
                    DB::rollBack();
                    $purchase = Purchase::find($item['item_id']);
                    $imei = $purchase ? $purchase->imei : $item['item_id'];
                    return back()->withErrors(['error' => "Item {$imei} is already sold or not found."])->withInput();
                }
            }

            // Process items
            $billItems = [];
            foreach ($request->items as $item) {
                $purchase = $purchases[$item['item_id']];
                $unitPrice = floatval($item['unit_price']);
                $quantity = intval($item['quantity']);

                $billItems[] = [
                    'bill_id' => $bill->id,
                    'item_id' => $item['item_id'],
                    'item_description' => $item['item_description'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_amount' => $unitPrice * $quantity,
                    'warranty_expiry_date' => !empty($item['warranty_expiry_date']) 
                        ? $this->convertDateFormat($item['warranty_expiry_date']) 
                        : null,
                    'profit' => ($unitPrice - $purchase->purchase_price) * $quantity,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Bulk insert bill items
            BillItem::insert($billItems);

            // Bulk update purchases as sold
            Purchase::whereIn('id', $purchaseIds)
                ->update([
                    'is_sold' => 1,
                    'sell_date' => $request->bill_date,
                ]);

            DB::commit();

            return redirect()->route('print-bill', $bill->id);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'An error occurred: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified bill.
     */
    public function billDetail($id)
    {
        $bill = Bill::with(['items.purchase', 'user', 'dealer'])
            ->notDeleted()
            ->findOrFail($id);
        
        $amountInWords = $this->amoutInWords(floatval($bill->net_amount));

        return view('bill.detail', compact('bill', 'amountInWords'));
    }

    /**
     * Print the specified bill.
     */
    public function printBill($id)
    {
        $bill = Bill::with(['items.purchase', 'user', 'dealer'])
            ->notDeleted()
            ->findOrFail($id);
        
        $amountInWords = $this->amoutInWords(floatval($bill->net_amount));

        return view('bill.print', compact('bill', 'amountInWords'));
    }

    /**
     * Fetch purchase data by IMEI.
     */
    public function fetchModelData($imei)
    {
        $purchase = Purchase::where('imei', 'LIKE', "%{$imei}%")
            ->where('is_sold', 0)
            ->where('deleted', 0)
            ->first();

        if (!$purchase) {
            return response()->json([
                'error' => 'IMEI Not in stock',
            ], 404);
        }

        return response()->json(['purchase' => $purchase]);
    }

    /**
     * Fetch dealer data by ID.
     */
    public function fetchDealerData($id)
    {
        $dealer = Dealer::find($id);

        if (!$dealer) {
            return response()->json([
                'error' => 'Dealer not found',
            ], 404);
        }

        return response()->json(['dealer' => $dealer]);
    }

    /**
     * Show the form for editing the specified bill.
     */
    public function editBill($id)
    {
        $bill = Bill::with('items.purchase')
            ->notDeleted()
            ->findOrFail($id);
        
        // Only fetch unsold purchases
        $stocksModel = Purchase::where('deleted', 0)
            ->where('is_sold', 0)
            ->orderBy('purchase_date', 'desc')
            ->get();
        
        $dealers = Dealer::orderBy('name')->get();

        return view('bill.edit', compact('bill', 'stocksModel', 'dealers'));
    }

    /**
     * Update the specified bill.
     */
    public function updateBill(Request $request, $id)
    {
        $bill = Bill::with('items.purchase')
            ->notDeleted()
            ->findOrFail($id);

        // Convert date formats
        $request->merge([
            'bill_date' => $this->convertDateFormat($request->bill_date),
        ]);

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
            // Get old purchase IDs and mark them as unsold (bulk update)
            $oldPurchaseIds = $bill->items->pluck('item_id')->filter()->toArray();
            if (!empty($oldPurchaseIds)) {
                Purchase::whereIn('id', $oldPurchaseIds)
                    ->update([
                        'is_sold' => 0,
                        'sell_date' => null,
                    ]);
            }

            // Delete old bill items
            $bill->items()->delete();

            // Calculate payment amounts
            [$cashAmount, $creditAmount] = $this->calculatePaymentAmounts(
                $request->payment_type,
                $request->net_amount,
                $request->cash_amount ?? 0
            );

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
                $this->recordCashPayment($bill, $request->dealer_id, $request->bill_date);
            }

            // Create new bill items and mark purchases as sold - optimized bulk operations
            $newPurchaseIds = collect($request->items)->pluck('item_id')->toArray();
            $purchases = Purchase::whereIn('id', $newPurchaseIds)
                ->where('is_sold', 0)
                ->get()
                ->keyBy('id');

            // Validate all purchases are available
            foreach ($request->items as $item) {
                if (!isset($purchases[$item['item_id']])) {
                    DB::rollBack();
                    $purchase = Purchase::find($item['item_id']);
                    $imei = $purchase ? $purchase->imei : $item['item_id'];
                    return back()->withErrors(['error' => "Item {$imei} is already sold or not found."])->withInput();
                }
            }

            // Process items
            $billItems = [];
            foreach ($request->items as $item) {
                $purchase = $purchases[$item['item_id']];
                $unitPrice = floatval($item['unit_price']);
                $quantity = intval($item['quantity']);

                $billItems[] = [
                    'bill_id' => $bill->id,
                    'item_id' => $item['item_id'],
                    'item_description' => $item['item_description'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_amount' => $unitPrice * $quantity,
                    'warranty_expiry_date' => !empty($item['warranty_expiry_date']) 
                        ? $this->convertDateFormat($item['warranty_expiry_date']) 
                        : null,
                    'profit' => ($unitPrice - $purchase->purchase_price) * $quantity,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Bulk insert bill items
            BillItem::insert($billItems);

            // Bulk update purchases as sold
            Purchase::whereIn('id', $newPurchaseIds)
                ->update([
                    'is_sold' => 1,
                    'sell_date' => $request->bill_date,
                ]);

            DB::commit();

            return redirect()->route('allbills')->withStatus('Bill Updated Successfully..');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'An error occurred: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified bill.
     */
    public function deleteBill($id)
    {
        DB::beginTransaction();
        try {
            $bill = Bill::with('items')->notDeleted()->findOrFail($id);

            // Mark purchases as unsold (bulk update)
            $purchaseIds = $bill->items->pluck('item_id')->filter()->toArray();
            if (!empty($purchaseIds)) {
                Purchase::whereIn('id', $purchaseIds)
                    ->update([
                        'is_sold' => 0,
                        'sell_date' => null,
                    ]);
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

    /**
     * Calculate cash and credit amounts based on payment type.
     *
     * @param string $paymentType
     * @param float $netAmount
     * @param float $cashAmount (not used for credit payments - always 0)
     * @return array [cash_amount, credit_amount]
     */
    private function calculatePaymentAmounts(string $paymentType, float $netAmount, float $cashAmount = 0): array
    {
        if ($paymentType === 'cash') {
            return [$netAmount, 0];
        }

        // For credit payments, cash_amount is always 0, credit_amount is the full net_amount
        return [0, $netAmount];
    }

    /**
     * Record cash payment for a bill.
     *
     * @param Bill $bill
     * @param int $dealerId
     * @param string $paymentDate
     * @return void
     */
    private function recordCashPayment(Bill $bill, int $dealerId, string $paymentDate): void
    {
        DealerPayment::create([
            'dealer_id' => $dealerId,
            'bill_id' => $bill->id,
            'payment_amount' => $bill->net_amount,
            'payment_date' => $paymentDate,
            'payment_type' => 'cash',
            'note' => 'Full payment for bill ' . $bill->bill_no,
            'created_by' => Auth::id(),
        ]);
    }

    /**
     * Convert amount to words (Indian numbering system).
     */
    protected function amoutInWords(float $amount): string
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
