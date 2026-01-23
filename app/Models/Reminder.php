<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    protected $fillable = ['cheque_id', 'reminder_date', 'notes', 'is_read'];

    public function cheque()
    {
        return $this->belongsTo(Cheque::class);
    }
}
