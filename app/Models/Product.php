<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'type',
        'brand',
        'category',
        'price',
        'is_new',
        'is_sale',
        'discount',
        'img_cover',
        'variants',
        'images',
        'quantity',
        'sold',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'is_new' => 'boolean',
        'is_sale' => 'boolean',
        'variants' => 'array',
        'images' => 'array',
        'quantity' => 'integer',
        'sold' => 'integer',
    ];

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
