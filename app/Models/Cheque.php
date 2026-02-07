<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cheque extends Model
{
    protected $fillable = [
        'cheque_number', 'cheque_date', 'bank_id', 'amount', 'payer_name', 
        'payee_name', 'payment_status', 'notes', 'type', 'third_party_name',
        'third_party_payment_status', 'third_party_notes', 'return_reason', 'return_notes'
    ];

    protected $casts = [
        'cheque_date' => 'date',
    ];

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function reminders()
    {
        return $this->hasMany(Reminder::class);
    }

    public function latestPayment()
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }
}
