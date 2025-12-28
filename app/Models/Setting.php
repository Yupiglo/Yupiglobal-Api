<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'site_name',
        'logo',
        'phone',
        'email',
        'address',
        'facebook',
        'instagram',
        'twitter',
        'youtube',
    ];

    protected $casts = [
        'site_name' => 'string',
        'logo' => 'string',
        'phone' => 'string',
        'email' => 'string',
        'address' => 'string',
        'facebook' => 'string',
        'instagram' => 'string',
        'twitter' => 'string',
        'youtube' => 'string',
    ];
}
