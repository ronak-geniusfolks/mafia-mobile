<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Purchase extends Model
{
    use HasFactory;

    protected $table = 'purchases';

    protected $guarded = [];

    protected static function booted()
    {
        static::addGlobalScope('not_deleted', function (Builder $builder) {
            $builder->where('deleted', 0);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeNotSold($q)
    {
        return $q->where('is_sold', 0);
    }
}
