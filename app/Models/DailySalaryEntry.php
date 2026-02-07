<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailySalaryEntry extends Model
{
    protected $fillable = ['date', 'employee_name', 'amount'];
    
    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];
}
