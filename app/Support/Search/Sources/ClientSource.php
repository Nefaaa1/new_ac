<?php

namespace App\Support\Search\Sources;

use App\Models\User;
use App\Support\Search\Search;
use App\Support\Search\SearchResult;
use App\Support\Search\SearchSource;

/**
 * Recherche dans les clients (User type=client + fiche société).
 * Respecte les accès de l'admin connecté (un restreint ne trouve que ses clients).
 */
class ClientSource implements SearchSource
{
    public function search(string $term): array
    {
        $admin = auth()->user();

        if (! $admin) {
            return [];
        }

        return User::query()
            ->where('type', 'client')
            ->accessibleBy($admin)
            ->with('client')
            ->where(function ($q) use ($term) {
                $q->where('nom', 'like', "%{$term}%")
                    ->orWhere('prenom', 'like', "%{$term}%")
                    ->orWhereHas('client', fn ($c) => $c->where('societe', 'like', "%{$term}%"));
            })
            ->limit(Search::LIMIT)
            ->get()
            ->map(fn (User $u) => new SearchResult(
                group: 'Clients',
                label: $u->name,
                sublabel: $u->client?->societe,
                icon: 'users',
                url: route('admin.clients', ['search' => $u->nom, 'open' => $u->id]),
                score: str_starts_with(mb_strtolower($u->nom), mb_strtolower($term)) ? 100 : 50,
            ))
            ->all();
    }
}
