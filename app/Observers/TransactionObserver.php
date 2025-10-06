<?php
namespace App\Observers;

use App\Models\Transaction;
use App\Services\OpeningBalanceService;

// TODO: This observer class not in USE now because of the financial module calculation with each and every transaction
class TransactionObserver
{
    /**
     * Handle the Transaction "created" event.
     */
    public function created(Transaction $transaction): void
    {
        // app(OpeningBalanceService::class)->recalculateFrom($transaction->payment_date);
    }

    /**
     * Handle the Transaction "updated" event.
     */
    public function updated(Transaction $transaction): void
    {
        app(OpeningBalanceService::class)->recalculateFrom($transaction->payment_date);
    }

    /**
     * Handle the Transaction "deleted" event.
     */
    public function deleted(Transaction $transaction): void
    {
        app(OpeningBalanceService::class)->recalculateFrom($transaction->payment_date);
    }
}
