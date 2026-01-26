<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'cheque_id', 'amount', 'payment_date', 'notes', 
        'payment_method', 'bank_id', 'reference_number', 
        'payment_cheque_number', 'payment_cheque_date', 'document',
        'payable_id', 'payable_type', 'type',
        'transaction_id', 'transaction_type'
    ];

    public function payable()
    {
        return $this->morphTo();
    }

    public function cheque()
    {
        return $this->belongsTo(Cheque::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }
}
