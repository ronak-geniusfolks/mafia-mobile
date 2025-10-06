<?php

use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionObserverTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_updates_opening_balance_when_transaction_created()
    {
        $this->assertDatabaseMissing('day_opening_balances', ['date' => '2024-01-01']);

        Transaction::factory()->create([
            'payment_date' => '2024-01-01',
            'transaction_type' => 'credit',
            'amount' => 1000,
            'created_by' => 1,
        ]);

        $this->assertDatabaseHas('day_opening_balances', [
            'date'    => '2024-01-01 00:00:00',
            'balance' => 0, // since there’s no earlier transaction
        ]);
    }

    /** @test */
    public function it_updates_opening_balance_when_transaction_updated()
    {
        $transaction = Transaction::factory()->create([
            'payment_date' => '2024-01-01',
            'transaction_type' => 'credit',
            'amount' => 1000,
            'created_by' => 1,
        ]);

        $transaction->update(['amount' => 1000]);

        $this->assertDatabaseHas('day_opening_balances', [
            'date'    => '2024-01-01 00:00:00',
            'balance' => 0, // still 0 because no previous txn
        ]);
    }

    /** @test */
    public function it_updates_opening_balance_when_transaction_deleted()
    {
        $txn = Transaction::factory()->create([
            'payment_date' => '2024-01-01',
            'transaction_type' => 'credit',
            'amount' => 1000,
            'created_by' => 1,
        ]);

        $txn->delete();

        $this->assertDatabaseHas('day_opening_balances', [
            'date'    => '2024-01-01 00:00:00',
            'balance' => 0,
        ]);
    }
}