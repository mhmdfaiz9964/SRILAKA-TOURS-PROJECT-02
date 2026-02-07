<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BalanceSheetEntry extends Model
{
    use HasFactory;

    protected $fillable = ['date', 'category', 'name', 'amount'];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];
}
