<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class ImportHistory extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'import_histories';

    protected $fillable = [
        'filename',
        'processed_at',
        'products_count',
        'status' // success, failed
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];
}