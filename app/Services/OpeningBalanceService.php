<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DayOpeningBalance;
use App\Models\Transaction;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class OpeningBalanceService
{
    /**
     * Recalculate opening balances for all unique transaction dates.
     *
     * This function will recalculate the opening balance for all unique
     * transaction dates. It will get all unique transaction dates, loop
     * through each date, calculate the opening balance for that date, and
     * update the opening balance for that date in the database.
     *
     * @return array
     */
    public function recalculateAll()
    {
        try {
            // Get all unique transaction dates
            $uniqueDates = Transaction::select('payment_date')
                ->distinct()
                ->orderBy('payment_date')
                ->pluck('payment_date')
                ->toArray();

            // Ensure today's date is included in the list
            $today = Carbon::now('Asia/Kolkata')->toDateString();
            if (! in_array($today, $uniqueDates)) {
                $uniqueDates[] = $today;
                sort($uniqueDates);
            }

            // Recalculate opening balances for each date
            foreach ($uniqueDates as $date) {
                $openingBalance = $this->calculateBalanceBeforeDate($date);

                DayOpeningBalance::updateOrCreate(
                    ['date' => $date],
                    [
                        'balance' => $openingBalance['total'],
                        'cash_balance' => $openingBalance['cash'],
                        'bank_balance' => $openingBalance['bank'],
                    ]
                );
            }

            return [
                'success' => true,
                'message' => 'Opening balances recalculated for all dates.',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error recalculating all balances: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Recalculate opening balances starting from a specific date.
     *
     * This function takes a date in the `Y-m-d` format and recalculates the opening
     * balances starting from that date. It will first get all unique transaction
     * dates starting from the specified date, loop through each date, calculate
     * the opening balance for that date, and update the opening balance for that
     * date in the database.
     *
     * @param  string  $startDate
     * @return array
     */
    public function recalculateFrom($startDate)
    {
        try {
            // Get all unique transaction dates starting from the specified date
            $uniqueDates = Transaction::where('payment_date', '>=', $startDate)
                ->distinct()
                ->orderBy('payment_date')
                ->pluck('payment_date')
                ->toArray();

            // Add today's date to the list if it is not already present
            $today = Carbon::now('Asia/Kolkata')->toDateString();
            if (! in_array($today, $uniqueDates)) {
                $uniqueDates[] = $today;
                sort($uniqueDates);
            }

            // Recalculate opening balance for each date in the list
            foreach ($uniqueDates as $date) {
                $openingBalance = $this->calculateBalanceBeforeDate($date);

                // Update the opening balance for the current date
                DayOpeningBalance::updateOrCreate(
                    ['date' => $date],
                    [
                        'balance' => $openingBalance['total'],
                        'cash_balance' => $openingBalance['cash'],
                        'bank_balance' => $openingBalance['bank'],
                    ]
                );
            }

            return [
                'success' => true,
                'message' => 'Opening balances recalculated from '.$startDate,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error recalculating balances from '.$startDate.': '.$e->getMessage(),
            ];
        }
    }

    /**
     * Calculate the total net balance before a given date.
     *
     * This function takes a date in the `Y-m-d` format and returns the total net
     * balance before that date, i.e., the sum of all credits minus the sum of all
     * debits before that date. The total net balance is calculated separately for
     * cash and bank balances.
     *
     * @param  string  $date
     * @return array
     */
    private function calculateBalanceBeforeDate($date)
    {
        // Get the IDs of the cash and bank payment methods
        $cashMethodIds = DB::table('payment_methods')->where('slug', 'cash')->pluck('id')->toArray();
        $bankMethodIds = DB::table('payment_methods')->whereIn('slug', ['UPI', 'CC'])->pluck('id')->toArray();

        // Sum all credits and subtract all debits before the given date
        // for cash and bank balances separately
        $cashBalance = Transaction::where('payment_date', '<=', $date)
            ->whereIn('payment_method', $cashMethodIds)
            ->sum(DB::raw('CASE
                WHEN transaction_type = "credit" THEN amount
                WHEN transaction_type = "debit" THEN -amount
                ELSE 0
            END'));

        $bankBalance = Transaction::where('payment_date', '<=', $date)
            ->whereIn('payment_method', $bankMethodIds)
            ->sum(DB::raw('CASE
                WHEN transaction_type = "credit" THEN amount
                WHEN transaction_type = "debit" THEN -amount
                ELSE 0
            END'));

        // Return the total net balance for cash and bank, and the sum of both
        return [
            'cash' => $cashBalance,
            'bank' => $bankBalance,
            'total' => ($cashBalance + $bankBalance),
        ];
    }
}
