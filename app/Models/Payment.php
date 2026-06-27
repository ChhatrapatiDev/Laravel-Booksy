<?php

namespace App\Models;

use App\PaymentMethod;
use App\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'method',
        'amount',
        'currency',
        'status',
        'transaction_id',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function casts(): array

    {
        return [
            'status' => PaymentStatus::class,
            'method' => PaymentMethod::class
        ];
    }
}
