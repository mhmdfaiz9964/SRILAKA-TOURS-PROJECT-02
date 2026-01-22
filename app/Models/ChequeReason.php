<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChequeReason extends Model
{
    protected $fillable = ['reason'];

    public function cheques()
    {
        return $this->hasMany(Cheque::class);
    }
}
