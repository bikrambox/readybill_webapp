<?php

namespace Modules\Authentication\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    protected $connection = 'central';
    protected $table = 'notifications';
    protected $primaryKey = 'id';

    protected $fillable = ['user_id', 'message', 'read_at', 'status'];
    
}
