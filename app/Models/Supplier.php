<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'full_name',
        'company_name',
        'contact_number',
        'status',
    ];

    public function getOutstandingAttribute()
    {
        $purchases = $this->purchases_sum_total_amount ?? $this->purchases()->sum('total_amount');
        $payments = $this->payments_sum_amount ?? $this->payments()->sum('amount');
        return $purchases - $payments;
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }
}
