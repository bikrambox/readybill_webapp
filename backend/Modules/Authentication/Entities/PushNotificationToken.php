<?php

namespace Modules\Authentication\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PushNotificationToken extends Model
{
    use HasFactory;

    protected $connection = 'central';
    protected $table = 'push_notification_tokens';
    protected $primaryKey = 'id';

    protected $fillable = ['user_id', 'device_token'];
}
