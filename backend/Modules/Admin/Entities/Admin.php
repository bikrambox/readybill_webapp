<?php

namespace Modules\Admin\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    use HasFactory;

    protected $connection = 'central';
    protected $table = 'admins';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'mobile',
        'email',
        'password',
        'ip_address',
        'last_logged_in',
        'current_logged_in'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}
