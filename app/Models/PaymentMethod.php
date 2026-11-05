<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'name',
        'description',
        'created_at',
        'updated_at',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
