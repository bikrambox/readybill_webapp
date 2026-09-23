<?php

namespace Modules\Core\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OTP extends Model
{
    use HasFactory;

    protected $connection = 'central';
    protected $table = 'o_t_p_s';
    protected $primaryKey = 'id';

    protected $fillable = [
        'dial_code',
        'mobile',
        'code',
        'count',
        'isVerify',
    ];
}