<?php

namespace Modules\GroceryGermany\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReportGeneration extends Model
{
    use HasFactory;

    protected $connection = 'grocery_germany';
    protected $table = 'report_generations';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'shop_id',
        'environment',
        'parameters',
        'status',
        'report_type',
        'file_path',
        'retry_count',
    ];

}
