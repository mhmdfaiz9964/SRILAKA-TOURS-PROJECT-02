<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $fillable = ['name', 'code', 'logo'];

    public function cheques()
    {
        return $this->hasMany(Cheque::class);
    }
}
