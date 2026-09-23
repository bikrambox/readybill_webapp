<?php

namespace Modules\Core\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Queries extends Model
{
    use HasFactory;

    protected $connection = 'central';
    protected $table = 'queries';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'attachment'
    ];

}
