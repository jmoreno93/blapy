<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatUser extends Model
{
    protected $fillable = ['platform', 'external_id', 'username', 'phone', 'user_id'];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
