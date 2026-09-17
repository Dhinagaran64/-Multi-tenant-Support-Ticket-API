<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookEvent extends Model
{
    protected $fillable = [
        'event_id',
        'event_type',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];
}
