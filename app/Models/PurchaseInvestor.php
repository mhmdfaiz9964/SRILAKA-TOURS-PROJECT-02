<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseInvestor extends Model
{
    protected $fillable = [
        'purchase_id',
        'investor_name',
        'amount',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }
}
