<?php

namespace Modules\GroceryGermany\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Authentication\Entities\User;

class Notification extends Model
{
    use HasFactory;

    protected $connection = 'grocery_germany';
    protected $table = 'notifications';
    protected $primaryKey = 'id';

    protected $fillable = ['user_id', 'type', 'title', 'message', 'data', 'is_read'];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
