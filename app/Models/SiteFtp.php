<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Accès FTP d'un site (1-1). Mot de passe chiffré au repos. */
#[Fillable(['site_id', 'hote', 'identifiant', 'mot_de_passe', 'client_visible'])]
class SiteFtp extends Model
{
    protected $table = 'site_ftp';

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
        return filled($this->hote) || filled($this->identifiant) || filled($this->getRawOriginal('mot_de_passe'));
    }
}
