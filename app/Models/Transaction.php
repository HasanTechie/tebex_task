<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Transaction extends Model
{
    protected static function booted()
    {
        static::creating(function ($model) {
            $model->public_id ??= (string) Str::ulid();
        });
    }
    protected $fillable = [
        'seller_id',
        'amount',
        'currency',
        'payment_provider',
        'customer_id',
        'idempotency_key',
        'gross_amount',
        'payment_provider_fee',
        'commission_rate',
        'commission_amount',
        'net_amount',
        'status',
    ];
    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }
}
