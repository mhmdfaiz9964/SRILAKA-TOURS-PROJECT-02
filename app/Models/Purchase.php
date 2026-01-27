<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'invoice_number',
        'supplier_id',
        'purchase_type',
        'purchase_date',
        'total_amount',
        'paid_amount',
        'status',
        'grn_number',
        'broker_cost',
        'transport_cost',
        'loading_cost',
        'unloading_cost',
        'labour_cost',
        'air_ticket_cost',
        'other_expenses',
        'duty_cost',
        'kuli_cost',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function investors()
    {
        return $this->hasMany(PurchaseInvestor::class);
    }
}
