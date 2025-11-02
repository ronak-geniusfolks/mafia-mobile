<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DealerPayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'dealer_payments';

    protected $fillable = [
        'dealer_id',
        'bill_id',
        'payment_amount',
        'payment_date',
        'payment_type',
        'note',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'payment_amount' => 'decimal:2',
    ];

    /**
     * Get the dealer that owns the payment.
     */
    public function dealer()
    {
        return $this->belongsTo(Dealer::class);
    }

    /**
     * Get the bill associated with the payment.
     */
    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }

    /**
     * Get the user who created the payment.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
