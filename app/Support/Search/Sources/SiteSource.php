<?php

namespace App\Support\Search\Sources;

use App\Models\Site;
use App\Support\Search\Search;
use App\Support\Search\SearchResult;
use App\Support\Search\SearchSource;

/**
 * Recherche dans les sites (nom / société du client).
 * Respecte les accès de l'admin connecté via le scope accessibleBy.
 */
class SiteSource implements SearchSource
{
    public function search(string $term): array
    {
        $admin = auth()->user();

        if (! $admin) {
            return [];
        }

        return Site::query()
            ->accessibleBy($admin)
            ->with('client')
            ->where(function ($q) use ($term) {
                $q->where('nom', 'like', "%{$term}%")
                    ->orWhereHas('client', fn ($c) => $c->where('societe', 'like', "%{$term}%"));
            })
            ->limit(Search::LIMIT)
            ->get()
            ->map(fn (Site $s) => new SearchResult(
                group: 'Sites',
                label: $s->nom,
                sublabel: $s->client?->societe,
                icon: 'globe',
                url: route('admin.sites.show', $s),
                score: str_starts_with(mb_strtolower($s->nom), mb_strtolower($term)) ? 100 : 50,
            ))
            ->all();
    }
}
