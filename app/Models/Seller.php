<?php

namespace App\Models;

use Database\Factories\SellerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
    /** @use HasFactory<SellerFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'tier'
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
