<?php

namespace App\Support\Search;

/**
 * Une source de recherche (une entité : clients, sites, contrats…).
 * Ajouter une entité recherchable = créer une classe qui implémente ceci
 * et l'enregistrer dans App\Support\Search\Search::sources().
 */
interface SearchSource
{
    /**
     * @return array<int, SearchResult>
     */
    public function search(string $term): array;
}
