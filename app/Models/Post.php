<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'title',
        'content',
        'author',
        'image',
        'is_publish',
    ];

    protected $casts = [
        'is_publish' => 'boolean',
    ];
}
