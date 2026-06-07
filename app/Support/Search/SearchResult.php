<?php

namespace App\Support\Search;

/**
 * Résultat de recherche normalisé : l'UI ne connaît jamais les modèles,
 * chaque source produit ces objets identiques.
 */
readonly class SearchResult
{
    public function __construct(
        public string $group,     // libellé du groupe affiché ("Clients", "Sites"…)
        public string $label,     // texte principal ("Antoine Fagnère")
        public ?string $sublabel, // texte secondaire (email, domaine…) ou null
        public string $icon,      // nom d'icône lucide (sans le préfixe)
        public string $url,       // destination (route absolue) du clic
        public int $score = 0,    // pertinence : plus haut = remonte en premier
    ) {}
}
