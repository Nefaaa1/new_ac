<?php

namespace App\Support\Search;

use App\Support\Search\Sources\ClientSource;
use App\Support\Search\Sources\ContratSource;
use App\Support\Search\Sources\SiteSource;

/**
 * Registre de la recherche universelle (calqué sur App\Support\Navigation).
 * SOURCE UNIQUE : on interroge ici toutes les entités recherchables, on agrège,
 * on classe par pertinence et on plafonne. L'UI (Admin\GlobalSearch) ne touche
 * jamais aux modèles directement.
 */
class Search
{
    /** Nombre maximum de résultats affichés (les plus pertinents). */
    public const LIMIT = 15;

    /** Nombre minimum de caractères avant de lancer une recherche. */
    public const MIN_CHARS = 2;

    /**
     * Les sources interrogées. Pour rendre une entité recherchable :
     * créer une classe SearchSource et l'ajouter à cette liste.
     *
     * @return array<int, SearchSource>
     */
    protected static function sources(): array
    {
        return [
            new ClientSource(),
            new SiteSource(),
            new ContratSource(),
        ];
    }

    /**
     * Lance la recherche sur toutes les sources.
     *
     * @return array{total: int, shown: int, limit: int, groups: array<string, array<int, SearchResult>>}
     */
    public static function query(string $term): array
    {
        $term = trim($term);

        if (mb_strlen($term) < self::MIN_CHARS) {
            return ['total' => 0, 'shown' => 0, 'limit' => self::LIMIT, 'groups' => []];
        }

        $all = [];
        foreach (static::sources() as $source) {
            array_push($all, ...$source->search($term));
        }

        // Les plus pertinents d'abord.
        usort($all, fn (SearchResult $a, SearchResult $b) => $b->score <=> $a->score);

        $total = count($all);
        $shown = array_slice($all, 0, self::LIMIT);

        // Regroupement par entité pour l'affichage.
        $groups = [];
        foreach ($shown as $result) {
            $groups[$result->group][] = $result;
        }

        return [
            'total'  => $total,
            'shown'  => count($shown),
            'limit'  => self::LIMIT,
            'groups' => $groups,
        ];
    }
}
