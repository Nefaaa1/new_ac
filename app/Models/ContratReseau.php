<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Compte de réseau social rattaché à un contrat.
 * Le mot de passe est chiffré au repos (cast 'encrypted').
 */
#[Fillable(['contrat_id', 'reseau', 'identifiant', 'mot_de_passe', 'gestion', 'position'])]
class ContratReseau extends Model
{
    protected $table = 'contrat_reseaux';

    /** Réseaux disponibles : clé stockée => [label, icône lucide]. */
    public const RESEAUX = [
        'facebook'  => ['label' => 'Facebook',    'icon' => 'facebook'],
        'instagram' => ['label' => 'Instagram',   'icon' => 'instagram'],
        'x'         => ['label' => 'X (Twitter)', 'icon' => 'twitter'],
        'linkedin'  => ['label' => 'LinkedIn',    'icon' => 'linkedin'],
        'brevo'     => ['label' => 'Brevo',       'icon' => 'mail'],
        'tiktok'    => ['label' => 'TikTok',      'icon' => 'music'],
        'pinterest' => ['label' => 'Pinterest',   'icon' => 'image'],
        'youtube'   => ['label' => 'YouTube',     'icon' => 'youtube'],
    ];

    /** Gestion du compte : clé stockée => libellé affiché. */
    public const GESTION = [
        'client' => 'Client',
        'agence' => 'Agence',
    ];

    protected function casts(): array
    {
        return [
            'mot_de_passe' => 'encrypted',
        ];
    }

    public function contrat(): BelongsTo
    {
        return $this->belongsTo(Contrat::class);
    }

    /** Libellé lisible du réseau. */
    public function reseauLabel(): string
    {
        return self::RESEAUX[$this->reseau]['label'] ?? ucfirst($this->reseau);
    }

    /** Icône lucide du réseau. */
    public function reseauIcon(): string
    {
        return self::RESEAUX[$this->reseau]['icon'] ?? 'share-2';
    }

    /** Libellé lisible de la gestion (Client/Agence) ou null. */
    public function gestionLabel(): ?string
    {
        return self::GESTION[$this->gestion] ?? null;
    }
}
