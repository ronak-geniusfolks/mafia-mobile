<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Dealer extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'dealers';

    protected $fillable = [
        'name',
        'address',
        'contact_number',
    ];

    /**
     * Get all bills for this dealer.
     */
    public function bills()
    {
        return $this->hasMany(Bill::class);
    }

    /**
     * Get all credit bills for this dealer.
     */
    public function creditBills()
    {
        return $this->hasMany(Bill::class)->where('payment_type', 'credit');
    }

    /**
     * Get all payments made by this dealer.
     */
    public function payments()
    {
        return $this->hasMany(DealerPayment::class);
    }

    /**
     * Scope: Dealers with pending payments.
     */
    public function scopeWithPendingPayments($query)
    {
        return $query->whereHas('creditBills', function ($q) {
            $q->whereRaw('(credit_amount - COALESCE(paid_amount, 0)) > 0');
        });
    }

    /**
     * Calculate total amount for this dealer (optimized).
     */
    public function getTotalAmount(): float
    {
        return $this->creditBills()->sum('total_amount');
    }

    /**
     * Calculate total paid amount for this dealer (optimized).
     */
    public function getTotalPaidAmount(): float
    {
        return $this->creditBills()->sum('paid_amount');
    }

    /**
     * Calculate total remaining amount for this dealer (optimized).
     */
    public function getTotalRemainingAmount(): float
    {
        // Use a single optimized query instead of loading all bills
        $result = $this->creditBills()
            ->selectRaw('SUM(credit_amount - COALESCE(paid_amount, 0)) as total_remaining')
            ->value('total_remaining');

        return max(0, floatval($result ?? 0));
    }

    /**
     * Get all pending bills for this dealer (with eager loading optimization).
     */
    public function getPendingBills()
    {
        return $this->creditBills()
            ->whereRaw('(credit_amount - COALESCE(paid_amount, 0)) > 0')
            ->orderBy('bill_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();
    }
}
