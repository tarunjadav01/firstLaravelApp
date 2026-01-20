<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventSession extends Model
{
    protected $fillable = [
        'tenant_id',
        'session_id',
        'last_activity',
    ];
}
