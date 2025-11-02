<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Dealer;
use App\Models\DealerPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DealerPaymentController extends Controller
{
    /**
     * Display a listing of dealers with their remaining payment amounts.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Calculate statistics
        $statistics = $this->calculateStatistics();
        
        return view('dealer-payments.index', compact('statistics'));
    }

    /**
     * Calculate statistics for dealer payments.
     *
     * @return array
     */
    private function calculateStatistics()
    {
        // Total remaining amount across all dealers
        $totalRemaining = DB::table('bills')
            ->where('payment_type', 'credit')
            ->selectRaw('SUM(IFNULL(credit_amount, 0) - IFNULL(paid_amount, 0)) as total')
            ->first();
        
        // Ensure non-negative
        $totalRemaining = max(0, floatval($totalRemaining->total ?? 0));

        // Total pending bills
        $totalPendingBills = DB::table('bills')
            ->where('payment_type', 'credit')
            ->whereRaw('(credit_amount - COALESCE(paid_amount, 0)) > 0')
            ->count();

        // Total dealers with pending payments
        $dealersWithPending = DB::table('dealers')
            ->whereIn('id', function ($query) {
                $query->select('dealer_id')
                    ->from('bills')
                    ->where('payment_type', 'credit')
                    ->whereRaw('(credit_amount - COALESCE(paid_amount, 0)) > 0');
            })
            ->count();

        // Today's payments received - use Carbon to ensure correct date comparison
        $today = Carbon::today()->format('Y-m-d');
        $todayPayments = DB::table('dealer_payments')
            ->whereDate('payment_date', $today)
            ->sum('payment_amount');

        // This month's payments received - use start and end of month for accuracy
        $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
        $endOfMonth = Carbon::now()->endOfMonth()->format('Y-m-d');
        $monthPayments = DB::table('dealer_payments')
            ->whereBetween('payment_date', [$startOfMonth, $endOfMonth])
            ->sum('payment_amount');

        return [
            'total_remaining' => round($totalRemaining, 2),
            'total_pending_bills' => $totalPendingBills,
            'dealers_with_pending' => $dealersWithPending,
            'today_payments' => round($todayPayments ?? 0, 2),
            'month_payments' => round($monthPayments ?? 0, 2),
        ];
    }

    /**
     * Get dealers with remaining amounts for DataTable.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDealersData(Request $request)
    {
        $dealers = Dealer::with('bills')
            ->whereHas('bills', function ($query) {
                $query->where('payment_type', 'credit')
                    ->whereRaw('(credit_amount - COALESCE(paid_amount, 0)) > 0');
            })
            ->get()
            ->map(function ($dealer) {
                $totalRemaining = $dealer->getTotalRemainingAmount();
                return [
                    'id' => $dealer->id,
                    'name' => $dealer->name,
                    'contact_number' => $dealer->contact_number,
                    'address' => $dealer->address,
                    'remaining_amount' => $totalRemaining,
                ];
            })
            ->filter(function ($dealer) {
                return $dealer['remaining_amount'] > 0;
            })
            ->values();

        // Get updated statistics
        $statistics = $this->calculateStatistics();

        return response()->json([
            'data' => $dealers,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Get pending bills for a specific dealer.
     *
     * @param int $dealerId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDealerBills($dealerId)
    {
        try {
            $dealer = Dealer::findOrFail($dealerId);
            $pendingBills = $dealer->getPendingBills();

            $bills = $pendingBills->map(function ($bill) {
                $remainingAmount = max(0, $bill->credit_amount - ($bill->paid_amount ?? 0));
                
                // Handle bill_date - convert to Carbon if it's a string
                $billDate = $bill->bill_date;
                if (!($billDate instanceof \Carbon\Carbon)) {
                    $billDate = Carbon::parse($billDate);
                }
                
                return [
                    'id' => $bill->id,
                    'bill_no' => $bill->bill_no,
                    'bill_date' => $billDate->format('d/m/Y'),
                    'net_amount' => $bill->net_amount,
                    'credit_amount' => $bill->credit_amount,
                    'paid_amount' => $bill->paid_amount ?? 0,
                    'remaining_amount' => $remainingAmount,
                    'is_fully_paid' => $remainingAmount <= 0,
                ];
            });

            $totalRemaining = $dealer->getTotalRemainingAmount();

            return response()->json([
                'status' => true,
                'dealer' => [
                    'id' => $dealer->id,
                    'name' => $dealer->name,
                    'contact_number' => $dealer->contact_number,
                ],
                'bills' => $bills,
                'total_remaining' => $totalRemaining,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch dealer bills: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a new payment from dealer.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storePayment(Request $request)
    {
        $request->validate([
            'dealer_id' => 'required|integer|exists:dealers,id',
            'payment_amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_type' => 'required|string|in:cash,credit',
            'note' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            // Convert payment date if needed (do this first)
            $paymentDate = $request->payment_date;
            if (preg_match('/\d{2}\/\d{2}\/\d{4}/', $paymentDate)) {
                $paymentDate = Carbon::createFromFormat('d/m/Y', $paymentDate)->format('Y-m-d');
            }

            $dealer = Dealer::findOrFail($request->dealer_id);
            $paymentAmount = floatval($request->payment_amount);
            $pendingBills = $dealer->getPendingBills();
            $totalRemaining = $dealer->getTotalRemainingAmount();

            // If no pending bills, create a payment record without bill allocation
            if ($pendingBills->isEmpty()) {
                // Create a general payment record for overpayment/advance
                DealerPayment::create([
                    'dealer_id' => $dealer->id,
                    'bill_id' => null,
                    'payment_amount' => $paymentAmount,
                    'payment_date' => $paymentDate,
                    'payment_type' => $request->payment_type,
                    'note' => ($request->note ?? '') . ' (Advance payment - No pending bills)',
                    'created_by' => Auth::id(),
                ]);

                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Advance payment recorded successfully (No pending bills).',
                    'data' => [
                        'payment_amount' => $paymentAmount,
                        'allocated_payments' => [['bill_no' => 'N/A', 'amount' => $paymentAmount, 'type' => 'advance']],
                    ],
                ]);
            }

            $remainingPayment = $paymentAmount;
            $allocatedPayments = [];
            $hasOverpayment = $paymentAmount > $totalRemaining;

            // Allocate payment to bills (oldest first)
            foreach ($pendingBills as $bill) {
                if ($remainingPayment <= 0) {
                    break;
                }

                $billRemaining = max(0, $bill->credit_amount - ($bill->paid_amount ?? 0));

                if ($billRemaining > 0) {
                    $allocationAmount = min($remainingPayment, $billRemaining);

                    // Update bill paid_amount
                    $newPaidAmount = ($bill->paid_amount ?? 0) + $allocationAmount;
                    $bill->update([
                        'paid_amount' => $newPaidAmount,
                        'is_paid' => ($newPaidAmount >= $bill->credit_amount) ? 1 : 0,
                    ]);

                    // Create payment record for this bill
                    $payment = DealerPayment::create([
                        'dealer_id' => $dealer->id,
                        'bill_id' => $bill->id,
                        'payment_amount' => $allocationAmount,
                        'payment_date' => $paymentDate,
                        'payment_type' => $request->payment_type,
                        'note' => $request->note ?? ('Payment for bill ' . $bill->bill_no),
                        'created_by' => Auth::id(),
                    ]);

                    $allocatedPayments[] = [
                        'bill_no' => $bill->bill_no,
                        'amount' => $allocationAmount,
                    ];

                    $remainingPayment -= $allocationAmount;
                }
            }

            // If there's an overpayment (remainingPayment > 0), allocate it to the oldest bill as advance
            if ($remainingPayment > 0 && !empty($pendingBills)) {
                $oldestBill = $pendingBills->first();
                
                // Create payment record for overpayment/advance
                DealerPayment::create([
                    'dealer_id' => $dealer->id,
                    'bill_id' => $oldestBill->id,
                    'payment_amount' => $remainingPayment,
                    'payment_date' => $paymentDate,
                    'payment_type' => $request->payment_type,
                    'note' => ($request->note ?? '') . ' (Advance payment: ₹' . number_format($remainingPayment, 2) . ')',
                    'created_by' => Auth::id(),
                ]);

                $allocatedPayments[] = [
                    'bill_no' => $oldestBill->bill_no,
                    'amount' => $remainingPayment,
                    'type' => 'advance',
                ];
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Payment processed successfully.',
                'data' => [
                    'payment_amount' => $paymentAmount,
                    'allocated_payments' => $allocatedPayments,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to process payment: ' . $e->getMessage(),
            ], 500);
        }
    }
}
