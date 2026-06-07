<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Accès accordé à un admin restreint vers une ressource précise (polymorphe).
 */
#[Fillable(['user_id', 'grantable_type', 'grantable_id'])]
class AccessGrant extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function grantable(): MorphTo
    {
        return $this->morphTo();
    }
}
