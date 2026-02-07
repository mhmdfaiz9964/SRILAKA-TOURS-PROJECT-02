<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DailyLedgerEntry extends Model
{
    use HasFactory;

    protected $fillable = ['date', 'description', 'amount', 'type'];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];
}
