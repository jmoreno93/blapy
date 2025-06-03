<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    protected $fillable = ['chat_user_id', 'start_date', 'end_date', 'status'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(ChatUser::class, 'chat_user_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
