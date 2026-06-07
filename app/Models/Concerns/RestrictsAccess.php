<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * À appliquer aux modèles soumis au contrôle d'accès des admins restreints
 * (futurs Site, Contrat, et Client le jour où il aura son propre modèle).
 *
 * Usage dans un listing : Site::accessibleBy(auth()->user())->get();
 * → renvoie tout pour un admin "full", uniquement les ressources accordées sinon.
 */
trait RestrictsAccess
{
    public function scopeAccessibleBy(Builder $query, User $admin): Builder
    {
        if ($admin->hasFullAccess()) {
            return $query;
        }

        $ids = $admin->accessGrants()
            ->where('grantable_type', (new static)->getMorphClass())
            ->pluck('grantable_id');

        return $query->whereKey($ids);
    }
}
