# IDEAS.md — pistes d'évolution

Idées notées pour plus tard (hors périmètre immédiat). À piocher quand le socle est en place.

## Favoris

- **Réordonnancement drag & drop** — glisser-déposer pour ordonner les favoris sur le dashboard.
  La colonne `position` est déjà prévue dans la table ; il restera à brancher une lib JS de tri
  (ex. SortableJS) + persister l'ordre (`wire:sortable`). _Effort : moyen._
- **Icône & couleur personnalisées** — laisser choisir une icône Lucide et une couleur (primary/
  secondary/neutre…) par favori, en plus du nom. _Effort : faible-moyen._
- **Favoris vers URL externes** — pouvoir épingler un lien hors application (doc, outil tiers),
  en plus des routes internes. Nécessite de gérer `url` en plus de `route_name`.
- **Raccourci clavier** — touche dédiée (ex. `f`) pour mettre/retirer la page courante des favoris.
- **Épingler dans la sidebar** — afficher les favoris aussi en haut de la sidebar, pas que sur l'accueil.

## Note / pense-bête

- **Plusieurs notes** — passer d'une note unique à une liste de notes titrées (mini bloc-notes).
- **Mode markdown ou checklist** — rendu markdown léger, ou cases à cocher pour des to-do.
- **Historique / versions** — garder les dernières versions de la note (annuler une suppression).

## Transverse

- **Pages récentes** — historique automatique des dernières pages visitées sur le dashboard
  (complément naturel des favoris, sans action manuelle).
- **Source de navigation unique** (`config/navigation.php` ou `App\Support\Navigation`) — centraliser
  `route → label → icon` pour la sidebar, le titre par défaut d'un favori et son icône. Refactor
  propre à faire au moment d'implémenter les favoris (évite la duplication actuelle dans le layout).
