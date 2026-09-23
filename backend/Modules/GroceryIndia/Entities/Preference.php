<?php

namespace Modules\GroceryIndia\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Authentication\Entities\User;

class preference extends Model
{
    use HasFactory;

    protected $connection = 'grocery_india';
    protected $table = 'preferences';
    protected $primaryKey = 'id';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
