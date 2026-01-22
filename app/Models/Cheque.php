<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cheque extends Model
{
    protected $fillable = [
        'cheque_number', 'cheque_date', 'bank_id', 'cheque_reason_id', 
        'amount', 'payer_name', 'payment_status', 'cheque_status', 'notes'
    ];

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function reason()
    {
        return $this->belongsTo(ChequeReason::class, 'cheque_reason_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
