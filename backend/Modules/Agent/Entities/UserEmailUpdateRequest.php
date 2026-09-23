<?php

namespace Modules\Agent\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserEmailUpdateRequest extends Model
{
    use HasFactory;
    protected $table = 'user_email_update_requests';
    protected $fillable = [
        'user_id',
        'old_email',
        'new_email',
        'token',
        'expires_at',
        'verified_at',
        'cancelled_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];
   
}
