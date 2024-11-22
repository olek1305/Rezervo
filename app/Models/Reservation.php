<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    protected $fillable = ['user_id', 'reservation_date'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
