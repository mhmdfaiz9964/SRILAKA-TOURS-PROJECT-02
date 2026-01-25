<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThirdParty extends Model
{
    protected $fillable = ['name', 'contact_number', 'notes'];
}
