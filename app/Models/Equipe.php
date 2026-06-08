<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Équipe d'administrateurs (cible d'attribution des tickets). */
#[Fillable(['nom', 'couleur'])]
class Equipe extends Model
{
    use SoftDeletes;

    /** Couleur de repli si aucune n'est définie. */
    public const DEFAULT_COLOR = '#00A4BC'; // primary

    /** Membres de l'équipe (administrateurs). */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'equipe_user');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /** Couleur d'affichage (toujours un hex exploitable). */
    public function color(): string
    {
        return $this->couleur ?: self::DEFAULT_COLOR;
    }
}
