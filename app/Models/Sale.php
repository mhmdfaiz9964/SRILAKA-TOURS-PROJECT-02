<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'invoice_number',
        'customer_id',
        'sale_date',
        'total_amount',
        'discount_amount',
        'paid_amount',
        'status',
        'notes',
        'transport_cost',
        'transport_cost',
        'salesman_id',
        'payment_method',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function salesman()
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }

    public function payments()
    {
        return $this->morphMany(Payment::class, 'transaction');
    }
}
