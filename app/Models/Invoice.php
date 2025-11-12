<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $table = 'invoices';

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'invoice_by', 'id');
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'item_id', 'id');
    }

    /**
     * Get all items for this invoice.
     */
    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /** Hide deleted rows everywhere you use ->notDeleted() */
    public function scopeNotDeleted($q)
    {
        return $q->where('deleted', 0);
    }
}
