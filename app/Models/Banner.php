<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'image',
        'title1',
        'title2',
        'sub_title1',
        'btn',
        'category',
        'is_active',
        'top_banner',
        'promotional_banner',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'top_banner' => 'boolean',
        'promotional_banner' => 'boolean',
        'sort_order' => 'integer',
    ];
}
