<?php

namespace Modules\Authentication\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApiKey extends Model
{
    use HasFactory;

    protected $connection = 'central';
    protected $table = 'api_keys';
    protected $primaryKey = 'id';

    protected $fillable = [
        'key',
        'user_id'
    ];

    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
}
