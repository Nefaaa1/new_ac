<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Statut applicable à un ticket (workflow), géré dans la rubrique Gestion. */
#[Fillable(['libelle', 'couleur', 'position', 'cloture'])]
class TicketStatut extends Model
{
    use SoftDeletes;

    protected $table = 'ticket_statuts';

    /** Couleur de repli si aucune n'est définie. */
    public const DEFAULT_COLOR = '#71717a'; // zinc-500

    protected function casts(): array
    {
        return [
            'cloture' => 'boolean',
        ];
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'statut_id');
    }

    /** Couleur d'affichage (toujours un hex exploitable). */
    public function color(): string
    {
        return $this->couleur ?: self::DEFAULT_COLOR;
    }
}
