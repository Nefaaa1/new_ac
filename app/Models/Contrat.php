<?php

namespace App\Models;

use App\Models\Concerns\RestrictsAccess;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'libelle', 'client_id', 'site_web', 'type', 'date_debut', 'date_fin',
    'taux_horaire', 'cycle_facturation', 'credits',
])]
class Contrat extends Model
{
    use HasFactory;
    use RestrictsAccess;
    use SoftDeletes;

    /** Types de contrat : clé stockée => libellé affiché. */
    public const TYPES = [
        'horaire'         => 'Contrat horaire sans heure sup',
        'horaire_sup'     => 'Contrat horaire + heure sup',
        'fixe'            => 'Contrat fixe',
        'sup_temps_reel'  => 'Contrat sup temps réel',
    ];

    /** Cycles de facturation : clé stockée => libellé affiché. */
    public const CYCLES = [
        'mensuel'      => 'Mensuelle',
        'trimestriel'  => 'Trimestriel',
        'annuel'       => 'Annuel',
    ];

    protected function casts(): array
    {
        return [
            'date_debut'   => 'date',
            'date_fin'     => 'date',
            'taux_horaire' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function reseaux(): HasMany
    {
        return $this->hasMany(ContratReseau::class)->orderBy('position');
    }

    /** Libellé lisible du type de contrat. */
    public function typeLabel(): ?string
    {
        return self::TYPES[$this->type] ?? null;
    }

    /** Libellé lisible du cycle de facturation. */
    public function cycleLabel(): ?string
    {
        return self::CYCLES[$this->cycle_facturation] ?? null;
    }
}
