<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bill extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bills';

    protected $guarded = [];

    protected $casts = [
        'bill_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'bill_by', 'id');
    }

    public function dealer()
    {
        return $this->belongsTo(Dealer::class, 'dealer_id', 'id');
    }

    /**
     * Get all items for this bill.
     */
    public function items()
    {
        return $this->hasMany(BillItem::class);
    }

    public function billItems()
    {
        return $this->hasMany(BillItem::class);
    }

    /**
     * Get all payments for this bill.
     */
    public function payments()
    {
        return $this->hasMany(DealerPayment::class);
    }

    /**
     * Calculate remaining amount for this bill.
     */
    public function getRemainingAmountAttribute()
    {
        $paidAmount = $this->paid_amount ?? 0;
        return max(0, $this->credit_amount - $paidAmount);
    }

    /**
     * Check if bill is fully paid.
     */
    public function isFullyPaid()
    {
        return $this->remaining_amount <= 0;
    }

    /** Hide deleted rows everywhere you use ->notDeleted() */
    public function scopeNotDeleted($q)
    {
        return $q->withoutTrashed();
    }
}
