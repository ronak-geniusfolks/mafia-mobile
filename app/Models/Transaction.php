<?php
namespace App\Models;

use Carbon\Carbon;
use App\Models\DayOpeningBalance;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_date',
        'transaction_type',
        'payment_method',
        'amount',
        'note',
        'opening_balance',
        'created_by',
    ];

    protected static function boot()
    {
        parent::boot();
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method');
    }

    /**
     * Retrieve transactions based on provided filters.
     *
     * @param array $columns Fields to retrieve
     * @param array $filters Filters to apply to the query
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTransactions(array $columns = [], array $filters = [])
    {
        $query = $this->with('paymentMethod:id,method_name')
            ->select($columns ?: '*')
            ->orderBy('created_at', 'desc');

        // Apply date filters
        if (! empty($filters['date_from'])) {
            $query->where('payment_date', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->where('payment_date', '<=', $filters['date_to']);
        }

        // Apply transaction type filter
        if (! empty($filters['transaction_type'])) {
            $query->where('transaction_type', $filters['transaction_type']);
        }

        // Apply payment method filter
        if (! empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        // If no date filters are set, show today's transactions by default
        if (empty($filters['date_from']) && empty($filters['date_to'])) {
            $query->where('payment_date', '=', Carbon::now('Asia/Kolkata')->toDateString());
        }

        return $query->get();
    }

    /**
     * Get the opening balance for a given date.
     *
     * If no date is provided, uses the current date.
     * If only date_from is set, uses that date.
     * If only date_to is set, uses that date.
     *
     * @param array $filters ['date_from' => 'YYYY-MM-DD', 'date_to' => 'YYYY-MM-DD']
     * @return array Opening balance
     * [
     *     'balance'      => int,
     *     'cash_balance' => int,
     *     'bank_balance' => int,
     * ]
     */
    public function getOpeningBalance(array $filters = [])
    {
        // Determine the date to query
        $date = $filters['date_from'] ?? $filters['date_to'] ?? date('Y-m-d');

        // Try to get the opening balance for the given date
        $openingData = DayOpeningBalance::where('date', $date)->first();

        if ($openingData) {
            // Return the opening balance for the given date
            return [
                'balance'      => $openingData->balance ?? 0,
                'cash_balance' => $openingData->cash_balance ?? 0,
                'bank_balance' => $openingData->bank_balance ?? 0,
            ];
        }

        // If no opening balance exists for the given date, try to get the last known opening balance before the given date
        $lastData = DayOpeningBalance::where('date', '<', $date)
            ->orderByDesc('date')
            ->first();

        // Return the last known opening balance before the given date
        return [
            'balance'      => $lastData->balance ?? 0,
            'cash_balance' => $lastData->cash_balance ?? 0,
            'bank_balance' => $lastData->bank_balance ?? 0,
        ];
    }
}
