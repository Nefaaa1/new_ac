<?php

namespace App\Support\Search\Sources;

use App\Models\Contrat;
use App\Support\Search\Search;
use App\Support\Search\SearchResult;
use App\Support\Search\SearchSource;

/**
 * Recherche dans les contrats (libellé / site web / société du client).
 * Respecte les accès de l'admin connecté via le scope accessibleBy.
 */
class ContratSource implements SearchSource
{
    public function search(string $term): array
    {
        $admin = auth()->user();

        if (! $admin) {
            return [];
        }

        return Contrat::query()
            ->accessibleBy($admin)
            ->with('client')
            ->where(function ($q) use ($term) {
                $q->where('libelle', 'like', "%{$term}%")
                    ->orWhere('site_web', 'like', "%{$term}%")
                    ->orWhereHas('client', fn ($c) => $c->where('societe', 'like', "%{$term}%"));
            })
            ->limit(Search::LIMIT)
            ->get()
            ->map(fn (Contrat $c) => new SearchResult(
                group: 'Contrats',
                label: $c->libelle,
                sublabel: $c->client?->societe,
                icon: 'file-text',
                url: route('admin.contrats.show', $c),
                score: str_starts_with(mb_strtolower($c->libelle), mb_strtolower($term)) ? 100 : 50,
            ))
            ->all();
    }
}
