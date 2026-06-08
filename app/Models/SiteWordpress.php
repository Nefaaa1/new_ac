<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Accès WordPress d'un site (1-1). Mots de passe chiffrés au repos. */
#[Fillable([
    'site_id', 'lien_admin', 'identifiant_admin', 'mot_de_passe_admin',
    'lien_client', 'identifiant_client', 'mot_de_passe_client', 'client_visible',
])]
class SiteWordpress extends Model
{
    protected $table = 'site_wordpress';

    protected function casts(): array
    {
        return [
            'mot_de_passe_admin'  => 'encrypted',
            'mot_de_passe_client' => 'encrypted',
            'client_visible'      => 'boolean',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /** L'onglet contient-il au moins un identifiant renseigné ? (pour la liste des sites) */
    public function hasData(): bool
    {
        return filled($this->lien_admin) || filled($this->identifiant_admin) || filled($this->getRawOriginal('mot_de_passe_admin'))
            || filled($this->lien_client) || filled($this->identifiant_client) || filled($this->getRawOriginal('mot_de_passe_client'));
    }
}
