<?php

namespace Modules\GroceryGermany\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Authentication\Entities\User;

class Preference extends Model
{
    use HasFactory;

    protected $connection = 'grocery_germany';
    protected $table = 'preferences';
    protected $primaryKey = 'id';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
