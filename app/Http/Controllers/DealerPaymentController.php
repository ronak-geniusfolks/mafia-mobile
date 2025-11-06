<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Dealer;
use App\Models\DealerPayment;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DealerPaymentController extends Controller
{
    /**
     * Display a listing of dealers with their remaining payment amounts.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Calculate statistics
        $statistics = $this->calculateStatistics();

        return view('dealer-payments.index', compact('statistics'));
    }

    /**
     * Get dealers with remaining amounts for DataTable (optimized query).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDealersData(Request $request)
    {
        // Optimized: Use single query with aggregation instead of loading all bills
        $dealers = Dealer::withPendingPayments()
            ->get()
            ->map(function ($dealer) {
                return [
                    'id' => $dealer->id,
                    'name' => $dealer->name,
                    'contact_number' => $dealer->contact_number,
                    'address' => $dealer->address,
                    'total_amount' => round($dealer->getTotalAmount(), 2),
                    'paid_amount' => round($dealer->getTotalPaidAmount(), 2),
                    'remaining_amount' => round($dealer->getTotalRemainingAmount(), 2),
                ];
            });

        // Get updated statistics
        $statistics = $this->calculateStatistics();

        return response()->json([
            'data' => $dealers,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Get pending bills for a specific dealer (optimized).
     *
     * @param  int  $dealerId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDealerBills($dealerId)
    {
        try {
            $dealer = Dealer::findOrFail($dealerId);
            $pendingBills = $dealer->getPendingBills();

            $bills = $pendingBills->map(function ($bill) {
                // Load payments for this bill
                $payments = $bill->payments()
                    ->whereNull('deleted_at')
                    ->orderBy('payment_date', 'asc')
                    ->orderBy('id', 'asc')
                    ->get();

                // bill_date is already cast to Carbon in Bill model
                return [
                    'id' => $bill->id,
                    'bill_no' => $bill->bill_no,
                    'bill_date' => $bill->bill_date->format('d/m/Y'),
                    'net_amount' => $bill->net_amount,
                    'credit_amount' => $bill->credit_amount,
                    'paid_amount' => $bill->paid_amount ?? 0,
                    'remaining_amount' => $bill->remaining_amount,
                    'is_fully_paid' => $bill->isFullyPaid(),
                    'payments' => $payments->map(function ($payment) {
                        return [
                            'id' => $payment->id,
                            'payment_amount' => $payment->payment_amount,
                            'payment_date' => $payment->payment_date->format('d/m/Y'),
                            'payment_type' => $payment->payment_type,
                            'note' => $payment->note ?? '',
                        ];
                    }),
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
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch dealer bills: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a new payment from dealer.
     *
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
            $paymentAmount = (float) ($request->payment_amount);
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
                    'note' => ($request->note ?? '').' (Advance payment - No pending bills)',
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
                        'note' => $request->note ?? ('Payment for bill '.$bill->bill_no),
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
            if ($remainingPayment > 0 && ! empty($pendingBills)) {
                $oldestBill = $pendingBills->first();

                // Create payment record for overpayment/advance
                DealerPayment::create([
                    'dealer_id' => $dealer->id,
                    'bill_id' => $oldestBill->id,
                    'payment_amount' => $remainingPayment,
                    'payment_date' => $paymentDate,
                    'payment_type' => $request->payment_type,
                    'note' => ($request->note ?? '').' (Advance payment: ₹'.number_format($remainingPayment, 2).')',
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
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Failed to process payment: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate statistics for dealer payments (optimized queries).
     */
    private function calculateStatistics(): array
    {
        // Use single query builder instance for better performance
        $today = Carbon::today()->format('Y-m-d');
        $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
        $endOfMonth = Carbon::now()->endOfMonth()->format('Y-m-d');

        // Optimized: Combine pending bills query
        $pendingBillsQuery = DB::table('bills')
            ->whereNull('bills.deleted_at')
            ->where('payment_type', 'credit')
            ->whereRaw('(credit_amount - COALESCE(paid_amount, 0)) > 0');

        // Total remaining amount - single optimized query
        $totalRemaining = (clone $pendingBillsQuery)
            ->selectRaw('SUM(credit_amount - COALESCE(paid_amount, 0)) as total')
            ->value('total');
        $totalRemaining = max(0, (float) ($totalRemaining ?? 0));

        // Total pending bills - use count from same query
        $totalPendingBills = (clone $pendingBillsQuery)->count();

        // Total dealers with pending payments - optimized with distinct
        $dealersWithPending = (clone $pendingBillsQuery)
            ->distinct('dealer_id')
            ->count('dealer_id');

        // Today's payments - exclude soft-deleted
        $todayPayments = DB::table('dealer_payments')
            ->whereNull('dealer_payments.deleted_at')
            ->where('payment_date', $today)
            ->sum('payment_amount');

        // Monthly payments - exclude soft-deleted
        $monthPayments = DB::table('dealer_payments')
            ->whereNull('dealer_payments.deleted_at')
            ->where('payment_date', '>=', $startOfMonth)
            ->where('payment_date', '<=', $endOfMonth)
            ->sum('payment_amount');

        return [
            'total_remaining' => round($totalRemaining, 2),
            'total_pending_bills' => $totalPendingBills,
            'dealers_with_pending' => $dealersWithPending,
            'today_payments' => round($todayPayments ?? 0, 2),
            'month_payments' => round($monthPayments ?? 0, 2),
        ];
    }
}
