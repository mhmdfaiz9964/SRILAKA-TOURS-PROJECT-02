<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['cheque_id', 'amount', 'payment_date', 'notes'];

    public function cheque()
    {
        return $this->belongsTo(Cheque::class);
    }
}
