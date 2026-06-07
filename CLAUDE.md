# CLAUDE.md — new_ac

Intranet privé (login uniquement, pas d'inscription). Projet perso/portfolio.

## Stack
- Laravel 13 + Livewire 4 + Blade + Tailwind CSS + Vite 7
- Auth : login Livewire custom (composant `Auth\Login`), identifiant = `login` (pas l'email), pas de Breeze controllers ni d'inscription/reset/profil
- Icônes : `mallardduck/blade-lucide-icons` (`<x-lucide-...>`)
- DB : MySQL — Local via Docker (MySQL :3306, phpMyAdmin :8080)
- **Pas de React, pas de Vue**

## Environnements
- **Local** : Windows + XAMPP + Docker
- **VPS** : OVH Ubuntu 24.04, PHP 8.3-FPM, Nginx, `/var/www/monprojet`
- **CI/CD** : GitHub Actions → push `main` → deploy automatique via SSH (`deployer`)

## Design
- Fond : `zinc-950` — Police : Figtree
- `primary: '#00A4BC'` (teal) — `secondary: '#F6A900'` (amber)
- Style : uppercase, tracking large, lignes décoratives, épuré

## Routes (`routes/web.php`)
```
GET  /              → redirect login
GET  /login         → guest    (Livewire Auth\Login)
POST /logout        → auth     (closure : logout + invalidate + redirect login)
GET  /dashboard     → auth     → redirige vers admin/client selon le type

# Groupe admin (prefix admin, name admin.*, middleware auth + type:admin)
GET /admin                  admin.dashboard   (Admin\Dashboard)
GET /admin/sites            admin.sites       (Admin\Sites)
GET /admin/contrats         admin.contrats    (Admin\Contrats)
GET /admin/clients          admin.clients     (Admin\Clients)
GET /admin/actions          admin.actions     (Admin\Actions)
GET /admin/tickets          admin.tickets     (Admin\Tickets)
GET /admin/chatbots         admin.chatbots    (Admin\Chatbots)
GET /admin/status           admin.status      (Admin\Status)
GET /admin/profil           admin.profil      (Admin\Profil)
GET /admin/recap/actions    admin.recap.actions (Admin\Recap\Actions)
GET /admin/recap/tickets    admin.recap.tickets (Admin\Recap\Tickets)
GET /admin/gestion/admins   admin.gestion.admins (Admin\Gestion\Admins)  # +middleware can:manage-admins

# Groupe client (middleware auth + type:client)
GET /client                 client.dashboard  (Client\Dashboard)
```
Les groupes admin et client portent aussi le middleware `not-suspended` (déconnecte un compte suspendu).
Logout : `POST /logout` (route `logout`), appelé via `<form>` dans la topbar admin / le dashboard client.
**Pièges :** après modif des routes en local, penser à `php artisan route:clear` (cache). Re-`route:cache` en prod après deploy.

## Auth & User
- Table `users` : `id`, `type` (enum admin|client), `access_level` (enum full|restricted), `login` (unique), `password`, `nom`, `prenom`, `email` (unique), `email_secondaire?`, `telephone?`, `suspended_at?`, timestamps, `softDeletes`
- `User` : trait `SoftDeletes`, accessor `name` = "Prénom Nom", helpers `isAdmin()`, `hasFullAccess()`, `isSuspended()`, `isSuperAdmin()` (login === `User::SUPER_ADMIN_LOGIN`), `canAccess($resource)`
- Relations : `hasOne note()` (pense-bête), `hasMany favorites()`, `hasMany accessGrants()`
- Middleware `type` (alias dans `bootstrap/app.php`) → `EnsureUserType`, usage `->middleware('type:admin')` (403 sinon)
- Middleware `not-suspended` → `EnsureNotSuspended` : déconnecte immédiatement un compte suspendu (sur groupes admin/client)
- Seeder admin principal : `php artisan db:seed --class=AdminUserSeeder` (compte `antoinepw` = super-admin `access_level=full`, idempotent, mdp généré affiché une fois)

## Gestion des administrateurs & contrôle d'accès
- Page `Admin\Gestion\Admins` (groupe nav **Gestion**, couleur `rose`) : liste / création / édition / suspension / suppression d'admins.
  - Création : mot de passe **généré, affiché une seule fois** (comme le seeder).
  - **Suspension** = colonne `users.suspended_at` (pas un softDelete) : login refusé (`Auth\Login`) + session active coupée (`not-suspended`).
- **Niveaux d'accès** : `users.access_level` = `full` (voit tout) | `restricted` (limité aux grants).
- **Accès granulaire par ressource** : table polymorphe `access_grants` (`user_id`, `grantable_type`, `grantable_id`, unique). Modèle `AccessGrant`. Aujourd'hui on accorde des **clients** (`grantable_type = User::class`) ; sites/contrats câblés, inertes jusqu'à leurs modèles.
- **Application** : trait `App\Models\Concerns\RestrictsAccess` → scope `Model::accessibleBy($admin)` (renvoie tout si full, sinon filtre sur les grants). À poser sur les futurs `Site`/`Contrat`. `User::canAccess($resource)` pour un check unitaire.
- **Garde-fous** : Gate `manage-admins` (full only) sur la route gestion + sidebar (clé `'can'` du groupe Navigation) ; impossible de se suspendre/supprimer soi-même ; super-admin (`antoinepw`) protégé (ni suspension, ni rétrogradation).
- **Formulaire = slide-over** (panneau glissant depuis la droite) : toujours dans le DOM, visibilité pilotée par `x-data="{ open: @entangle('showForm') }"` + `x-transition` translate (pour animer entrée/sortie — ne PAS revenir à un `@if` qui casserait l'animation). Structure interne : en-tête figé / corps `flex-1 overflow-y-auto` / pied figé.

## Dashboard admin — note & favoris
- **Note pense-bête** : table `notes` (`user_id` unique, `content`) → `User hasOne`. Composant `Admin\Notepad`
  embarqué dans le dashboard, auto-save `wire:model.live.debounce.800ms` + hook `updatedContent` (updateOrCreate).
- **Favoris** : table `favorites` (`user_id`, `label`, `route_name`, `params` json, `position`, `unique(user_id,route_name)`).
  - `Admin\FavoriteToggle` (topbar) : étoile toggle sur la page courante ; popover Alpine pour nommer (pré-rempli).
  - `Admin\Favorites` (dashboard) : grille cliquable + renommer/supprimer ; garde `Route::has()` (ignore routes disparues).
  - Synchro topbar → dashboard via event Livewire `favorites-updated` (`#[On]`).
- **`App\Support\Navigation`** : SOURCE UNIQUE `route → label → icon`. `groups()` = sidebar (Informations/Récap),
  `pages()`/`find()` = lookup à plat (incl. dashboard, profil) pour le label/icône par défaut des favoris.
  Modifier la nav admin se fait ICI (la sidebar boucle dessus).

## Recherche universelle (topbar)
- Champ toujours visible dans la topbar admin (`<livewire:admin.global-search>`), dropdown au fil de la frappe
  (`wire:model.live.debounce.300ms`), résultats groupés par entité, **max 15** affichés, **décompte réel** indiqué en tête.
- **Pattern « providers » calqué sur `Navigation`** dans `app/Support/Search/` :
  - `SearchSource` (interface `search(string): SearchResult[]`), `SearchResult` (DTO readonly : group/label/sublabel/icon/url/score).
  - `Search` = registre : `query()` interroge toutes les `sources()`, trie par `score` desc, plafonne à `LIMIT` (15), regroupe. `MIN_CHARS=2`.
  - `Search/Sources/{Client,Site,Contrat}Source` : **STUBS qui renvoient `[]`** pour l'instant. ClientSource a l'implémentation réelle en commentaire (données users prêtes). Activer une entité = remplir son source, rien d'autre à toucher.
  - Ajouter une entité recherchable → nouvelle classe `SearchSource` + l'enregistrer dans `Search::sources()`.

## Structure views
```
layouts/guest.blade.php            ← login (carte blanche centrée)
layouts/admin.blade.php            ← espace admin : sidebar noire (zinc-950) + topbar (fond clair)
layouts/panel.blade.php            ← dashboard client (thème zinc-950)
livewire/auth/login.blade.php
livewire/admin/dashboard.blade.php  + profil + notepad + favorites + favorite-toggle + global-search
   + une vue par page (sites, contrats, clients, actions, tickets, chatbots, status)
   et livewire/admin/recap/{actions,tickets}.blade.php + admin/gestion/admins.blade.php
livewire/client/dashboard.blade.php
components/admin/page-header.blade.php  ← props: title, subtitle, icon (en-tête de page admin)
components/admin/empty-state.blade.php  ← props: icon, title (état « en construction »)
components/text-input.blade.php     ← props: label, size, name, error (erreur intégrée)
components/primary-button.blade.php ← props: icon (lucide), text, size, full ; hover inversé
components/input-label / input-error / auth-session-status
```
**Layout admin = app shell** : `body` en `h-screen overflow-hidden`, **sidebar + topbar figées**, seul le `<main>` scrolle (`flex-1 overflow-y-auto`). Ne pas remettre la topbar en `sticky` ni rendre le `body` scrollable.
Sidebar admin (`layouts/admin.blade.php`) : boucle sur `App\Support\Navigation::groups()` —
**Informations** (point `primary`), **Récap mensuel** (point `secondary`) et **Gestion** (point `rose`, clé `'can' => 'manage-admins'` → masqué aux admins restreints). Lien actif via `request()->routeIs()`.
Logo blanc `public/images/Logo-website-blanc.png` → retour dashboard.
Topbar : `<livewire:admin.global-search>` (recherche universelle) + `<livewire:admin.favorite-toggle>` (étoile) + dropdown utilisateur (Profil / Déconnexion).

## Composants Livewire (app/Livewire/)
- `Auth\Login` (#[Layout guest])
- `Admin\*` (#[Layout admin], full-page) : `Dashboard`, `Sites`, `Contrats`, `Clients`, `Actions`, `Tickets`,
  `Chatbots`, `Status`, `Profil`, `Recap\Actions`, `Recap\Tickets` — pages simples = en-tête + empty-state ;
  `Gestion\Admins` = CRUD admins + suspension + accès (cf. section dédiée)
- `Admin\*` (imbriqués, sans #[Layout]) : `Notepad` (note auto-save), `Favorites` (liste dashboard),
  `FavoriteToggle` (étoile topbar), `GlobalSearch` (recherche universelle topbar → `App\Support\Search`)
- `Client\Dashboard` (#[Layout panel])
- À venir : `Server` (reloadNginx), `Logs` (polling + pause), `Monitor` (CPU/RAM/disk)
- `App\Support\SystemMetrics` : lecture `/proc` Linux — **demo mode automatique sur Windows** (valeurs aléatoires + badge jaune), toujours conserver ce comportement

## Conventions & pièges connus
- `wire:model` property et `wire:submit` method → noms différents obligatoires (ex. propriété `login` → méthode `login_request`)
- Auth sur le champ `login` (pas l'email) : `Auth::attempt(['login' => ..., 'password' => ...])`
- Protéger les routes par rôle avec `type:admin` / `type:client`
- Modifier la nav admin (libellés/icônes/ordre) → `App\Support\Navigation` uniquement (sidebar + favoris en dépendent)
- Pistes d'évolution notées dans `IDEAS.md` (drag&drop favoris, icône/couleur, pages récentes, multi-notes…)
- Livewire 4 embarque Alpine.js → ne pas l'importer dans `app.js`
- Ajouter `./app/Livewire/**/*.php` dans `tailwind.config.js` content
- Guards `function_exists()` + `PHP_OS_FAMILY` pour toute fonction Linux-only
- `git reset --hard` avant `git pull` dans le deploy (évite conflits `package-lock.json`)
- Ne jamais commiter de credentials — `.env` non versionné

## graphify

This project has a knowledge graph at graphify-out/ with god nodes, community structure, and cross-file relationships.

Rules:
- For codebase questions, first run `graphify query "<question>"` when graphify-out/graph.json exists. Use `graphify path "<A>" "<B>"` for relationships and `graphify explain "<concept>"` for focused concepts. These return a scoped subgraph, usually much smaller than GRAPH_REPORT.md or raw grep output.
- If graphify-out/wiki/index.md exists, use it for broad navigation instead of raw source browsing.
- Read graphify-out/GRAPH_REPORT.md only for broad architecture review or when query/path/explain do not surface enough context.
- After modifying code, run `graphify update .` to keep the graph current (AST-only, no API cost).
