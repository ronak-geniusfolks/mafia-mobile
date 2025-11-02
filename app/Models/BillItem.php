<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    /**
     * Get the bill that owns the item.
     */
    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }

    /**
     * Get the purchase (product) associated with this bill item.
     */
    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'item_id', 'id');
    }
}
