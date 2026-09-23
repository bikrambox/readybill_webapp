<?php

namespace Modules\Agent\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Authentication\Entities\User;

class AgentDetails extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'address',
        'pan_number',
        'photo',
        'aadhar_card',
        'qr_code',
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    

}
