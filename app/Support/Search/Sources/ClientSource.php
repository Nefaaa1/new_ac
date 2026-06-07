<?php

namespace App\Support\Search\Sources;

use App\Support\Search\SearchResult;
use App\Support\Search\SearchSource;

/**
 * Recherche dans les clients (User type=client).
 *
 * STUB — renvoie [] pour l'instant. Pour l'activer, décommenter le corps :
 * les données existent déjà (table users), il suffit de retirer le `return []`.
 */
class ClientSource implements SearchSource
{
    public function search(string $term): array
    {
        return [];

        // return \App\Models\User::query()
        //     ->where('type', 'client')
        //     ->where(fn ($q) => $q
        //         ->where('nom', 'like', "%{$term}%")
        //         ->orWhere('prenom', 'like', "%{$term}%")
        //         ->orWhere('email', 'like', "%{$term}%")
        //         ->orWhere('login', 'like', "%{$term}%"))
        //     ->limit(\App\Support\Search\Search::LIMIT)
        //     ->get()
        //     ->map(fn ($u) => new SearchResult(
        //         group: 'Clients',
        //         label: $u->name,
        //         sublabel: $u->email,
        //         icon: 'users',
        //         url: route('admin.clients'), // → route('admin.clients.show', $u) quand la fiche existera
        //         score: str_starts_with(mb_strtolower($u->nom), mb_strtolower($term)) ? 100 : 50,
        //     ))
        //     ->all();
    }
}
