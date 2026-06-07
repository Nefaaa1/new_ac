<?php

namespace App\Support\Search\Sources;

use App\Support\Search\SearchResult;
use App\Support\Search\SearchSource;

/**
 * Recherche dans les sites.
 *
 * STUB — le modèle Site n'existe pas encore : renvoie []. Quand il existera,
 * implémenter ici comme dans ClientSource (icône suggérée : 'globe').
 */
class SiteSource implements SearchSource
{
    public function search(string $term): array
    {
        return [];
    }
}
