<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'tenant_id',
        'session_id',
        'event_type',
        'event_timestamp',
        'event_hash'
    ];
}
