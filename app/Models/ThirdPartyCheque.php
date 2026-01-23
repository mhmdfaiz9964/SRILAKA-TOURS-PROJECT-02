<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThirdPartyCheque extends Model
{
    protected $fillable = [
        'in_cheque_id',
        'third_party_name',
        'transfer_date',
        'status',
        'notes'
    ];

    public function inCheque()
    {
        return $this->belongsTo(InCheque::class, 'in_cheque_id');
    }
}
