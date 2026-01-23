<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OutCheque extends Model
{
    protected $fillable = [
        'cheque_date',
        'amount',
        'cheque_number',
        'bank_id',
        'payee_name',
        'notes',
        'status'
    ];

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }
}
