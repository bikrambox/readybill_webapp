<?php

namespace Modules\GroceryIndia\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $connection = 'grocery_india';
    protected $table = 'categories';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
        'sort_order',
    ];
    
}
