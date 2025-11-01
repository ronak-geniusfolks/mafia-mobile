<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
}
