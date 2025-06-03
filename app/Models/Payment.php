<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'subscription_id',
        'payment_provider_id',
        'external_payment_id',
        'amount',
        'currency',
        'status',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(PaymentProvider::class, 'payment_provider_id');
    }
}
