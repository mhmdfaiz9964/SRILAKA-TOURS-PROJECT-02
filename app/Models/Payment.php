<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'cheque_id', 'amount', 'payment_date', 'notes', 
        'payment_method', 'bank_id', 'reference_number', 
        'payment_cheque_number', 'payment_cheque_date', 'document'
    ];

    public function cheque()
    {
        return $this->belongsTo(Cheque::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }
}
