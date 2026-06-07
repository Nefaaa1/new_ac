<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Fiche métier d'un client (extension 1-1 du User type=client).
 * Le compte / l'auth restent sur User ; ici les champs spécifiques au client.
 */
#[Fillable(['user_id', 'societe', 'lienapp', 'email3'])]
class Client extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contrats(): HasMany
    {
        return $this->hasMany(Contrat::class);
    }
}
