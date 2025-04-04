<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\{HasMany, BelongsTo};

class Shortcut extends Model
{
    protected $fillable = ['user_id', 'name', 'icon', 'description', 'user_action_id'];

    protected $hidden = ['created_at', 'updated_at', 'user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function userAction(): HasMany
    {
        return $this->hasMany(UserAction::class);
    }
}
