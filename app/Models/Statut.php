<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Statut applicable à un site. `requiert_date` impose la saisie de `date_statut`
 * sur le site qui le porte (cf. Admin\Sites\Form).
 */
#[Fillable(['libelle', 'couleur', 'requiert_date'])]
class Statut extends Model
{
    use SoftDeletes;

    /** Couleur de repli si aucune n'est définie. */
    public const DEFAULT_COLOR = '#71717a'; // zinc-500

    protected function casts(): array
    {
        return [
            'requiert_date' => 'boolean',
        ];
    }

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    /** Couleur d'affichage (toujours un hex exploitable). */
    public function color(): string
    {
        return $this->couleur ?: self::DEFAULT_COLOR;
    }
}
