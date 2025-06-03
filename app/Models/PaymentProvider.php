<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentProvider extends Model
{
    protected $fillable = ['name', 'driver'];

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
