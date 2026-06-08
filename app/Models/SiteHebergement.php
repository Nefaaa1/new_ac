<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Hébergement d'un site (1-1). Mot de passe chiffré au repos. */
#[Fillable([
    'site_id', 'nom', 'registrar', 'identifiant', 'mot_de_passe',
    'periode_renouvellement', 'paiement_agence', 'paiement_mois', 'client_visible',
])]
class SiteHebergement extends Model
{
    protected $table = 'site_hebergement';

    /** Périodes de renouvellement : clé stockée => libellé affiché. */
    public const PERIODES = [
        'mensuelle' => 'Mensuelle',
        'annuelle'  => 'Annuelle',
    ];

    protected function casts(): array
    {
        return [
            'mot_de_passe'    => 'encrypted',
            'paiement_agence' => 'boolean',
            'client_visible'  => 'boolean',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function periodeLabel(): ?string
    {
        return self::PERIODES[$this->periode_renouvellement] ?? null;
    }

    /** L'onglet contient-il au moins un identifiant renseigné ? (pour la liste des sites) */
    public function hasData(): bool
    {
        return filled($this->nom) || filled($this->registrar)
            || filled($this->identifiant) || filled($this->getRawOriginal('mot_de_passe'));
    }
}
