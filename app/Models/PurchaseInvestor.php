<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseInvestor extends Model
{
    protected $fillable = [
        'purchase_id',
        'investor_name',
        'amount',
        'investor_id',
    ];

    public function investor()
    {
        return $this->belongsTo(Investor::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }
}
