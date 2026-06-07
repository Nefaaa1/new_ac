<?php

namespace App\Models;

use App\Models\Concerns\RestrictsAccess;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'nom', 'client_id', 'boutique_en_ligne', 'statut_id', 'date_statut',
    'mot_de_passe_complementaire',
])]
class Site extends Model
{
    use HasFactory;
    use RestrictsAccess;
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'boutique_en_ligne'           => 'boolean',
            'date_statut'                 => 'date',
            'mot_de_passe_complementaire' => 'encrypted',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function statut(): BelongsTo
    {
        return $this->belongsTo(Statut::class);
    }

    public function hebergement(): HasOne
    {
        return $this->hasOne(SiteHebergement::class);
    }

    public function ftp(): HasOne
    {
        return $this->hasOne(SiteFtp::class);
    }

    public function bdd(): HasOne
    {
        return $this->hasOne(SiteBdd::class);
    }

    public function wordpress(): HasOne
    {
        return $this->hasOne(SiteWordpress::class);
    }
}
