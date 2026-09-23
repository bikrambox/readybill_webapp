<?php

namespace Modules\GroceryIndia\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Authentication\Entities\User;

class Staff extends Model
{
    use HasFactory;

    protected $connection = 'grocery_india';
    protected $table = 'staff';
    protected $primaryKey = 'staff_id';

    protected $fillable = [
        'staff_id',
        'name',
        'email',
        'user_id',
        'address',
        'addedBy',
        'photo',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


}
