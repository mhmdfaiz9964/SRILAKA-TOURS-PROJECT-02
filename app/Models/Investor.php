<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Investor extends Model
{
    protected $fillable = [
        'name',
        'invest_amount',
        'expect_profit',
        'paid_profit',
        'collect_date',
        'refund_date',
        'notes',
        'status'
    ];
}
