<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Base de données d'un site (1-1). Mot de passe chiffré au repos. */
#[Fillable(['site_id', 'lien', 'serveur', 'username', 'mot_de_passe', 'client_visible'])]
class SiteBdd extends Model
{
    protected $table = 'site_bdd';

    protected function casts(): array
    {
        return [
            'mot_de_passe'   => 'encrypted',
            'client_visible' => 'boolean',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /** L'onglet contient-il au moins un identifiant renseigné ? (pour la liste des sites) */
    public function hasData(): bool
    {
        return filled($this->lien) || filled($this->serveur)
            || filled($this->username) || filled($this->getRawOriginal('mot_de_passe'));
    }
}
