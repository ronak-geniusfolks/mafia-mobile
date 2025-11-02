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
     * Create a new dealer
     *
     * @param array $data
     * @return self
     */
    public static function createDealer(array $data): self
    {
        return self::create([
            'name'           => $data['name'],
            'contact_number' => $data['contact_number'],
            'address'        => $data['address'] ?? null,
        ]);
    }

    /**
     * Update dealer information
     *
     * @param int $id
     * @param array $data
     * @return self
     */
    public static function updateDealer(int $id, array $data): self
    {
        $dealer = self::findOrFail($id);
        
        $dealer->update([
            'name'           => $data['name'],
            'contact_number' => $data['contact_number'],
            'address'        => $data['address'] ?? null,
        ]);

        return $dealer->fresh();
    }

    /**
     * Get dealer by ID
     *
     * @param int $id
     * @return self|null
     */
    public static function getDealerById(int $id): ?self
    {
        return self::find($id);
    }

    /**
     * Get dealer by ID or fail
     *
     * @param int $id
     * @return self
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public static function getDealerByIdOrFail(int $id): self
    {
        return self::findOrFail($id);
    }

    /**
     * Get all dealers (excluding soft deleted)
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAllDealers()
    {
        return self::all();
    }

    /**
     * Delete dealer (soft delete)
     *
     * @param int $id
     * @return bool
     */
    public static function deleteDealer(int $id): bool
    {
        $dealer = self::findOrFail($id);
        return $dealer->delete();
    }

    /**
     * Get all bills for this dealer.
     */
    public function bills()
    {
        return $this->hasMany(Bill::class);
    }

    /**
     * Get all payments made by this dealer.
     */
    public function payments()
    {
        return $this->hasMany(DealerPayment::class);
    }

    /**
     * Calculate total remaining amount for this dealer.
     */
    public function getTotalRemainingAmount()
    {
        $result = DB::table('bills')
            ->where('dealer_id', $this->id)
            ->where('payment_type', 'credit')
            ->selectRaw('SUM(credit_amount - COALESCE(paid_amount, 0)) as total_remaining')
            ->first();
        
        return max(0, floatval($result->total_remaining ?? 0));
    }

    /**
     * Get all pending bills for this dealer.
     */
    public function getPendingBills()
    {
        return $this->bills()
            ->where('payment_type', 'credit')
            ->whereRaw('(credit_amount - COALESCE(paid_amount, 0)) > 0')
            ->orderBy('bill_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();
    }
}
