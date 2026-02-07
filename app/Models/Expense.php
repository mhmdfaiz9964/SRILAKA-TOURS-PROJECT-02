<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'reason',
        'amount',
        'paid_by',
        'notes',
        'payment_method',
        'cheque_number',
        'cheque_date',
        'bank_id',
        'payer_name',
        'expense_date',
        'category_id'
    ];

    protected $casts = [
        'expense_date' => 'date',
        'cheque_date' => 'date',
        'amount' => 'decimal:2'
    ];

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }
}
