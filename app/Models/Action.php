<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['intitule', 'temps', 'date', 'type', 'contrat_id', 'commentaire', 'createur_id'])]
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

    /** Admin ayant saisi l'action (null pour l'historique antérieur). */
    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'createur_id');
    }

    /** Libellé lisible du type d'action. */
    public function typeLabel(): ?string
    {
        return self::TYPES[$this->type] ?? null;
    }

    /** Temps de cette action formaté (ex. « 2,5 h »). */
    public function tempsLabel(): string
    {
        return self::formatHeures((float) $this->temps);
    }

    /** Formate un nombre d'heures décimal en libellé court (ex. 2.50 → « 2,5 h »). */
    public static function formatHeures(float $heures): string
    {
        return rtrim(rtrim(number_format($heures, 2, ',', ' '), '0'), ',').' h';
    }

    /** Formate un nombre d'heures décimal en « 2h 30min » (ex. 2.50 → « 2h 30min »). */
    public static function formatHeuresHM(float $heures): string
    {
        $h = (int) floor($heures);
        $m = (int) round(($heures - $h) * 60);

        if ($m === 60) { // arrondi qui bascule à l'heure pleine
            $h++;
            $m = 0;
        }

        $parts = [];
        if ($h > 0) {
            $parts[] = $h.'h';
        }
        if ($m > 0) {
            $parts[] = $m.'min';
        }

        return $parts ? implode(' ', $parts) : '0min';
    }
}
