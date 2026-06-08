<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Ticket d'intervention rattaché à un site.
 * L'accès est dérivé du site (cf. Admin\Tickets) — pas de RestrictsAccess direct.
 */
#[Fillable([
    'demande', 'descriptif', 'site_id', 'date', 'statut_id',
    'a_deviser', 'devis_statut_id', 'temps_intervention', 'importance',
    'utilisateur_id', 'equipe_id', 'createur_id', 'terminee_at', 'termine_par_id',
])]
class Ticket extends Model
{
    use SoftDeletes;

    /** Degré d'importance : clé stockée => libellé affiché. */
    public const IMPORTANCES = [
        'faible'  => 'Faible',
        'moyenne' => 'Moyenne',
        'elevee'  => 'Élevée',
    ];

    /** Couleur (hex) par degré d'importance. */
    public const IMPORTANCE_COLORS = [
        'faible'  => '#71717a', // zinc-500
        'moyenne' => '#F6A900', // amber (secondary)
        'elevee'  => '#ef4444', // red-500
    ];

    protected function casts(): array
    {
        return [
            'date'               => 'date',
            'a_deviser'          => 'boolean',
            'temps_intervention' => 'decimal:2',
            'terminee_at'        => 'datetime',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function statut(): BelongsTo
    {
        return $this->belongsTo(TicketStatut::class, 'statut_id');
    }

    public function devisStatut(): BelongsTo
    {
        return $this->belongsTo(DevisStatut::class, 'devis_statut_id');
    }

    /** Admin à qui le ticket est attribué (exclusif avec equipe). */
    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }

    /** Équipe à qui le ticket est attribué (exclusif avec utilisateur). */
    public function equipe(): BelongsTo
    {
        return $this->belongsTo(Equipe::class);
    }

    /** Admin ayant créé le ticket. */
    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'createur_id');
    }

    /** Admin ayant clôturé le ticket. */
    public function terminePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'termine_par_id');
    }

    /** Libellé lisible du degré d'importance. */
    public function importanceLabel(): ?string
    {
        return self::IMPORTANCES[$this->importance] ?? null;
    }

    /** Couleur (hex) du degré d'importance. */
    public function importanceColor(): string
    {
        return self::IMPORTANCE_COLORS[$this->importance] ?? '#71717a';
    }

    /** Temps d'intervention formaté (ex. « 2,5 h »), ou null si non défini. */
    public function tempsLabel(): ?string
    {
        return $this->temps_intervention !== null
            ? Action::formatHeures((float) $this->temps_intervention)
            : null;
    }
}
