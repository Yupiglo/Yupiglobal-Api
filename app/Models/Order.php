<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'shipping_street',
        'shipping_city',
        'shipping_phone',
        'order_status',
        'payment_method',
        'is_paid',
        'is_delivered',
        'total_order_price',
        'order_at',
        'paid_at',
        'delivered_at',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'is_delivered' => 'boolean',
        'total_order_price' => 'decimal:2',
        'order_at' => 'datetime',
        'paid_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
