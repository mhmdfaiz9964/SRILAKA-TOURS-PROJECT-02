<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InCheque extends Model
{
    protected $fillable = [
        'cheque_date',
        'amount',
        'cheque_number',
        'bank_id',
        'payer_name',
        'notes',
        'status',
        'third_party_name'
    ];

    protected $casts = [
        'cheque_date' => 'date',
    ];

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function thirdPartyTransfer()
    {
        return $this->hasOne(ThirdPartyCheque::class, 'in_cheque_id');
    }
}
