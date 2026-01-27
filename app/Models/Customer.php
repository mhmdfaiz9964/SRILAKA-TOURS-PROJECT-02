<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'full_name',
        'company_name',
        'mobile_number',
        'credit_limit',
        'status',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function getOutstandingAttribute()
    {
        $sales = $this->sales_sum_total_amount ?? $this->sales()->sum('total_amount');
        $payments = $this->payments_sum_amount ?? $this->payments()->sum('amount');
        return $sales - $payments;
    }
}
