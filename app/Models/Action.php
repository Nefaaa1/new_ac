<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['intitule', 'temps', 'date', 'type', 'contrat_id', 'commentaire'])]
class Action extends Model
{
    use HasFactory;
    use SoftDeletes;

    /** Types d'action : clé stockée => libellé affiché. */
    public const TYPES = [
        'site_web'         => 'Site web',
        'reseaux_sociaux'  => 'Réseaux sociaux',
        'redaction'        => 'Rédaction de contenu',
        'graphisme'        => 'Graphisme',
        'intranet'         => 'Intranet',
    ];

    protected function casts(): array
    {
        return [
            'date'  => 'date',
            'temps' => 'decimal:2',
        ];
    }

    public function contrat(): BelongsTo
    {
        return $this->belongsTo(Contrat::class);
    }

    /** Libellé lisible du type d'action. */
    public function typeLabel(): ?string
    {
        return self::TYPES[$this->type] ?? null;
    }
}
