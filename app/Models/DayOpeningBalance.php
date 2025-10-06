<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DayOpeningBalance extends Model
{
    protected $fillable = [
        'date',
        'balance',
        'cash_balance',
        'bank_balance',
    ];

    protected $casts = [
        'date'         => 'date',
        'balance'      => 'decimal:2',
        'cash_balance' => 'decimal:2',
        'bank_balance' => 'decimal:2',

    ];
}
