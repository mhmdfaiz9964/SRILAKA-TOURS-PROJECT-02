<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    protected $fillable = ['cheque_id', 'payer_name', 'reminder_date', 'notes', 'is_read'];

    protected $casts = [
        'reminder_date' => 'date',
    ];

    public function cheque()
    {
        return $this->belongsTo(Cheque::class);
    }
}
