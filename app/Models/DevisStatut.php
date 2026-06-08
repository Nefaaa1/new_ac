<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** État de devis applicable à un ticket, géré dans la rubrique Gestion. */
#[Fillable(['libelle', 'couleur', 'position'])]
class DevisStatut extends Model
{
    use SoftDeletes;

    protected $table = 'devis_statuts';

    /** Couleur de repli si aucune n'est définie. */
    public const DEFAULT_COLOR = '#71717a'; // zinc-500

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'devis_statut_id');
    }

    /** Couleur d'affichage (toujours un hex exploitable). */
    public function color(): string
    {
        return $this->couleur ?: self::DEFAULT_COLOR;
    }
}
