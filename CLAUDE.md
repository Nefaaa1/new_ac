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
GET /admin/contrats             admin.contrats        (Admin\Contrats)         # liste
GET /admin/contrats/create      admin.contrats.create (Admin\Contrats\Form)    # création (page pleine)
GET /admin/contrats/{contrat}   admin.contrats.show   (Admin\Contrats\Show)    # fiche (page pleine)
GET /admin/contrats/{contrat}/edit admin.contrats.edit (Admin\Contrats\Form)   # édition (même composant que create)
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
- Table `users` : `id`, `type` (enum admin|client), `access_level` (enum full|restricted), `login` (unique), `password`, `civilite?` (enum M|Mme), `nom`, `prenom`, `email` (unique), `email_secondaire?`, `telephone?`, `suspended_at?`, timestamps, `softDeletes`
- `User` : traits `SoftDeletes` + `RestrictsAccess` (scope `accessibleBy($admin)`), accessor `name` = "Prénom Nom", helpers `isAdmin()`, `hasFullAccess()`, `isSuspended()`, `isSuperAdmin()` (login === `User::SUPER_ADMIN_LOGIN`), `canAccess($resource)`
- Relations : `hasOne note()` (pense-bête), `hasMany favorites()`, `hasMany accessGrants()`, `hasOne client()` (fiche métier si type=client)
- Middleware `type` (alias dans `bootstrap/app.php`) → `EnsureUserType`, usage `->middleware('type:admin')` (403 sinon)
- Middleware `not-suspended` → `EnsureNotSuspended` : déconnecte immédiatement un compte suspendu (sur groupes admin/client)
- Seeder admin principal : `php artisan db:seed --class=AdminUserSeeder` (compte `antoinepw` = super-admin `access_level=full`, idempotent, mdp généré affiché une fois)

## Gestion des administrateurs & contrôle d'accès
- Page `Admin\Gestion\Admins` (groupe nav **Gestion**, couleur `rose`) : liste / création / édition / suspension / suppression d'admins.
  - Création : mot de passe **généré, affiché une seule fois** (comme le seeder).
  - **Suspension** = colonne `users.suspended_at` (pas un softDelete) : login refusé (`Auth\Login`) + session active coupée (`not-suspended`).
- **Niveaux d'accès** : `users.access_level` = `full` (voit tout) | `restricted` (limité aux grants).
- **Accès granulaire par ressource** : table polymorphe `access_grants` (`user_id`, `grantable_type`, `grantable_id`, unique). Modèle `AccessGrant`. On accorde aujourd'hui des **clients** (`grantable_type = User::class`) **et des contrats** (`grantable_type = Contrat::class`) via le form admin (cases à cocher `grantedClientIds` / `grantedContratIds`, visibles si `accessLevel === restricted`) ; **sites** câblés, inertes jusqu'à leur modèle.
- **Application** : trait `App\Models\Concerns\RestrictsAccess` → scope `Model::accessibleBy($admin)` (renvoie tout si full, sinon filtre sur les grants). Posé sur `User`(client) et `Contrat` ; à poser sur le futur `Site`. `User::canAccess($resource)` pour un check unitaire (utilisé par `Contrats\Form`/`Show` → `abort_unless(canAccess, 403)`). Sync centralisé : `Admins::syncGrants()` → `syncGrantsFor($admin, $type, $ids)` (purge + recrée par type ; full = purge tout). Ajouter un type accordable = un `array $grantedXIds` + une règle de validation + un appel `syncGrantsFor` + un bloc de cases dans la vue.
- **Garde-fous** : Gate `manage-admins` (full only) sur la route gestion + sidebar (clé `'can'` du groupe Navigation) ; impossible de se suspendre/supprimer soi-même ; super-admin (`antoinepw`) protégé (ni suspension, ni rétrogradation).
- **Formulaire = slide-over** (panneau glissant depuis la droite) : toujours dans le DOM, visibilité pilotée par `x-data="{ open: @entangle('showForm') }"` + `x-transition` translate (pour animer entrée/sortie — ne PAS revenir à un `@if` qui casserait l'animation). Structure interne : en-tête figé / corps `flex-1 overflow-y-auto` / pied figé.

## Clients (CRUD)
- **Un client = un `User` (type=client)** loginable + une **fiche métier 1‑1** dans la table `clients`.
- Table `clients` : `id` (identité métier, future FK pour contrats/sites), `user_id` (unique, `cascadeOnDelete`), `societe?`, `lienapp?`, `email3?`, timestamps. Modèle `Client belongsTo User` ; `User hasOne client()`.
- **Pattern = composition (extension 1‑1)**, PAS d'héritage : Eloquent n'a pas de STI natif → on garde l'auth sur `users` et les champs métier isolés dans `clients`. Étendre un client = ajouter une colonne à `clients` (ne pas polluer `users`).
- Page `Admin\Clients` (route `admin.clients`) : CRUD complet (même UX que `Gestion\Admins` — tableau + slide-over + mot de passe généré). `save()` crée/MAJ le `User` puis `updateOrCreate` la ligne `clients`.
- **`societe` obligatoire** (validation `required` ; colonne reste nullable en DB pour les anciennes lignes), **mise en avant** dans le tableau (chip + icône).
- **Colonnes triables** (clients + admins) via le trait `App\Livewire\Concerns\WithSorting` (`sortBy($field)` : clic = asc, re-clic = desc) + composant `<x-admin.sort-header>`. Tri par défaut clients = société (sous-requête `Client::select('societe')->whereColumn(...)`), admins = nom. Le composant traduit `$sortField` en `orderBy` (`match`), la société se trie via la sous-requête.
- **Recherche libre** : champ `<x-text-input>` (composant maison, cohérence design) + icône loupe/croix, `#[Url(except:'')] $search` (nom/prénom/société, `wire:model.live.debounce.300ms`).
- **Deep-link recherche globale** : `#[Url(except:null)] $open` (id client) → `mount()` ouvre directement le slide-over du client ciblé (si accessible) ; `closeForm()` remet `open=null`. Edit/delete/save sont gardés par `accessibleBy` (un restreint ne peut pas ouvrir un client non accordé).
- **Filtrage par accès** : la liste utilise `User::where('type','client')->accessibleBy(auth()->user())` → un admin restreint ne voit que ses clients accordés (grants `grantable_type = User::class`).
- **Tableaux admin (clients + gestion)** : en-tête `bg-primary text-white`, lignes zébrées `odd:bg-white even:bg-primary/[0.04]` + survol `hover:bg-secondary/10`, avatars en dégradé primary→secondary, icône `mail` secondary devant l'email.

## Contrats (CRUD)
- **Vraie page par contrat** (PAS de slide-over comme clients) : 3 composants full-page (`#[Layout admin]`) — `Admin\Contrats` (liste), `Admin\Contrats\Form` (création **et** édition), `Admin\Contrats\Show` (fiche lecture seule). Routes sous `prefix('contrats')->name('contrats.')` : `create` / `{contrat}` (show) / `{contrat}/edit` — **`create` déclaré AVANT `{contrat}`** (sinon "create" capté comme id).
- Table `contrats` : `id`, `libelle` (requis), `client_id?` (FK `clients.id`, `nullOnDelete` — contrat possible sans client), `site_web?`, `type?`, `date_debut?`, `date_fin?`, `taux_horaire?` (decimal 10,2), `cycle_facturation?`, `credits?` (uint, **1 crédit = 1h**), timestamps, `softDeletes`. Modèle `Contrat` : traits `HasFactory + RestrictsAccess + SoftDeletes`, casts dates + `decimal:2`, relations `client() belongsTo` / `reseaux() hasMany (orderBy position)`. Enums = **constantes de classe** `Contrat::TYPES` (horaire | horaire_sup | fixe | sup_temps_reel) et `Contrat::CYCLES` (mensuel | trimestriel | annuel), helpers `typeLabel()`/`cycleLabel()`.
- **Réseaux sociaux n‑aire** : table `contrat_reseaux` (`contrat_id` cascade, `reseau`, `identifiant?`, `mot_de_passe?`, `gestion?`, `position`). Modèle `ContratReseau` : **`mot_de_passe` cast `encrypted`** (chiffré au repos), `protected $table` explicite (pluriel irrégulier), constantes `RESEAUX` (facebook/instagram/x/linkedin/brevo/tiktok/pinterest/youtube → `[label, icon]` lucide brand) et `GESTION` (client | agence, **facultatif**), helpers `reseauLabel()`/`reseauIcon()`/`gestionLabel()`.
- **Form** : onglets (Alpine `x-show="$wire.activeTab===…"`, source de vérité = prop Livewire `activeTab` pour rester synchro avec `addReseau()`) — **Général** (champs contrat) + **Réseaux sociaux** (lignes répétables `public array $reseaux`, `addReseau()`/`removeReseau($i)` + `wire:key`). Champs **obligatoires** : `libelle`, `type`, `cycle_facturation`, `taux_horaire`, `credits` (validation `required` ; colonnes DB restent nullable — même convention que `societe`). `date_fin`, `client_id`, `site_web` facultatifs. Mots de passe : input `:type="show?'text':'password'"` + œil Alpine. `save()` upsert le contrat puis `syncReseaux()` (updateOrCreate par `id`, supprime les `whereNotIn($keep)`), puis `redirectRoute('admin.contrats.show', navigate:true)`. Garde `abort_unless(canAccess, 403)` en édition.
- **Show** : onglets Alpine pur (`x-data="{ tab }"`, lecture seule, pas de prop serveur), `<dl>` grille pour le Général + cartes réseaux (mot de passe masqué `••••` + œil reveal). Boutons Modifier / Supprimer (`deleteContrat()` → redirect liste).
- **Liste** : tableau coloré + `WithSorting` (tri défaut `date_debut desc`) + recherche libre `#[Url(except:'')] $search` (libellé / site / société / nom-prénom client). Rattachement client affiché en chip société.
- **Recherche globale** : `ContratSource` **actif** (libellé / site / société, `accessibleBy`, deep-link `admin.contrats.show`).
- Sidebar : sous-pages contrats gardent **Contrats** actif via `routeIs($route.'.*')` (ajouté au layout) ; `<title>` retombe sur la rubrique parente (`Str::beforeLast(name,'.')`) si la sous-route n'est pas dans `Navigation`.
- **Pré-remplissage libellé** : `Contrats\Form` (création) lit `request()->query('libelle')` → permet d'arriver depuis l'autocomplétion d'action avec le libellé saisi déjà rempli.

## Actions (CRUD)
- `Admin\Actions` (route `admin.actions`) : CRUD en **slide-over** (même UX que clients/admins) — table colorée + `WithSorting` (défaut `date desc`) + recherche libre `#[Url(except:'')] $search` (intitulé / commentaire / libellé contrat) + soft delete.
- Table `actions` : `id`, `intitule` (requis), `temps` (decimal 6,2, **heures** ex. 1.50 = 1h30, requis), `date` (requis), `type` (requis), `contrat_id` (FK `contrats` cascade, **requis**), `commentaire?`, timestamps, `softDeletes`. Modèle `Action` : `HasFactory + SoftDeletes`, casts `date`/`decimal:2`, `contrat() belongsTo`, const `TYPES` (site_web | reseaux_sociaux | redaction | graphisme | intranet) + `typeLabel()`. **Pas de `RestrictsAccess` direct** : l'accès est dérivé du contrat → la liste/edit/delete filtrent par `whereHas('contrat', accessibleBy(...))` (un admin restreint ne voit que les actions de ses contrats accordés). `save()` revérifie `Contrat::accessibleBy(...)->findOrFail()`.
- Formulaire : `<x-date-input>` (date), `<x-text-input type=number step=0.25>` (temps), `<x-select>` (type), **`<livewire:admin.contrat-picker>`** (contrat, cf. ci-dessous), `<textarea>` (commentaire). Le picker est keyé `:key="'contrat-picker-'.$formNonce"` (`$formNonce++` à chaque `create()`/`editAction()`) pour **remonter proprement** à chaque ouverture (sinon il garderait l'état précédent — le slide-over reste dans le DOM).

## Autocomplétion contrat — `Admin\ContratPicker` (réutilisable)
- Composant Livewire **nested** branché par `wire:model` grâce à `#[Modelable] public ?int $contratId`. Usage : `<livewire:admin.contrat-picker wire:model="contrat_id" label="Contrat" :key="…" />`.
- Champ texte (autocomplete off) → `results()` (computed, `accessibleBy`, libellé `like`, min 2 car., limite 8) affichées dans un dropdown. `selectContrat($id)` fixe `contratId` + remplit le libellé ; **toute frappe (`updatedSearch`) ré-annule la sélection** (`contratId=null`) jusqu'à un nouveau choix → la validation `required` du parent reste fiable.
- **Création à la volée** : bouton « Créer le contrat « … » » → `createContrat()` = `redirectRoute('admin.contrats.create', ['libelle'=>$search], navigate:true)` (le libellé saisi pré-remplit le form contrat). ⚠️ redirige donc hors du formulaire en cours (comportement demandé).
- Erreur de validation : portée par le **parent** (`@error('contrat_id')` après la balise `<livewire:…>`), pas par le picker. Dropdown : Alpine `@entangle('showResults')` + `@click.outside`.

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
  - `Search/Sources/ClientSource` + `ContratSource` : **actifs** (filtrés par `accessibleBy`, deep-link vers la fiche). `SiteSource` : **stub `[]`** jusqu'à son modèle. Activer une entité = remplir son source, rien d'autre à toucher.
  - Ajouter une entité recherchable → nouvelle classe `SearchSource` + l'enregistrer dans `Search::sources()`.

## Structure views
```
layouts/guest.blade.php            ← login (carte blanche centrée)
layouts/admin.blade.php            ← espace admin : sidebar noire (zinc-950) + topbar (fond clair)
layouts/panel.blade.php            ← dashboard client (thème zinc-950)
livewire/auth/login.blade.php
livewire/admin/dashboard.blade.php  + profil + notepad + favorites + favorite-toggle + global-search + contrat-picker
   + une vue par page (sites, contrats, clients, actions, tickets, chatbots, status)
   livewire/admin/contrats/{form,show}.blade.php (fiche contrat pleine page + onglets)
   et livewire/admin/recap/{actions,tickets}.blade.php + admin/gestion/admins.blade.php
livewire/client/dashboard.blade.php
components/admin/page-header.blade.php  ← props: title, subtitle, icon (en-tête de page admin)
components/admin/empty-state.blade.php  ← props: icon, title (état « en construction »)
components/admin/sort-header.blade.php  ← <th> triable : props field, label, sort, direction (→ sortBy)
components/text-input.blade.php     ← props: label, size, name, error (erreur intégrée)
components/select.blade.php          ← jumeau de text-input pour les <select> (options en slot)
components/date-input.blade.php      ← sélecteur de date Flatpickr (props: label, name, model, floatError) — cf. § Dates
components/primary-button.blade.php ← props: icon (lucide), text, size, full ; hover inversé
components/input-label / input-error / auth-session-status
```
**Layout admin = app shell** : `body` en `h-screen overflow-hidden`, **sidebar + topbar figées**, seul le `<main>` scrolle (`flex-1 overflow-y-auto`). Ne pas remettre la topbar en `sticky` ni rendre le `body` scrollable.
**`<title>` dynamique** : le layout admin le dérive de `Navigation::find(route courante)` → `"{label} · {app.name}"` (mis à jour aussi via `wire:navigate`) ; guest = « Connexion · … », panel = « Espace client · … ». Favicon `public/favicon.ico` lié dans les 3 layouts. `APP_NAME="Partner Web Communication"` (penser à l'aligner dans le `.env` du VPS).
Sidebar admin (`layouts/admin.blade.php`) : boucle sur `App\Support\Navigation::groups()` —
**Informations** (point `primary`), **Récap mensuel** (point `secondary`) et **Gestion** (point `rose`, clé `'can' => 'manage-admins'` → masqué aux admins restreints). Lien actif via `request()->routeIs()`.
Logo blanc `public/images/Logo-website-blanc.png` → retour dashboard.
Topbar : `<livewire:admin.global-search>` (recherche universelle) + `<livewire:admin.favorite-toggle>` (étoile) + dropdown utilisateur (Profil / Déconnexion).

## Composants Livewire (app/Livewire/)
- `Auth\Login` (#[Layout guest])
- `Admin\*` (#[Layout admin], full-page) : `Dashboard`, `Sites`, `Contrats`, `Actions`, `Tickets`,
  `Chatbots`, `Status`, `Profil`, `Recap\Actions`, `Recap\Tickets` — pages simples = en-tête + empty-state ;
  `Clients` = CRUD clients (cf. section Clients) ; `Contrats` (liste) + `Contrats\Form` + `Contrats\Show` = CRUD contrats pleine page (cf. section Contrats) ; `Actions` = CRUD actions en slide-over (cf. section Actions) ; `Gestion\Admins` = CRUD admins + suspension + accès (cf. section dédiée)
- `Admin\*` (imbriqués, sans #[Layout]) : `Notepad` (note auto-save), `Favorites` (liste dashboard),
  `FavoriteToggle` (étoile topbar), `GlobalSearch` (recherche universelle topbar → `App\Support\Search`),
  `ContratPicker` (autocomplétion contrat `#[Modelable]`, cf. section dédiée)
- `Client\Dashboard` (#[Layout panel])
- À venir : `Server` (reloadNginx), `Logs` (polling + pause), `Monitor` (CPU/RAM/disk)
- `App\Support\SystemMetrics` : lecture `/proc` Linux — **demo mode automatique sur Windows** (valeurs aléatoires + badge jaune), toujours conserver ce comportement

## Conventions & pièges connus
- Formulaires admin/clients : champs via `<x-text-input>` / `<x-select>` (cohérence design) avec **label au-dessus** + prop **`floatError`** (message d'erreur en position absolue → ne décale pas la mise en page) ; grilles 2-col en `items-start`, body en `space-y-6`. Erreurs auto-détectées via `name`. (Le login garde l'erreur inline classique, `floatError` non passé.) Login **auto-généré** `prénomnom` (collé, sans accent ni séparateur — `preg_replace('/[^a-z0-9]/','', Str::ascii(...))`) par le trait `App\Livewire\Concerns\GeneratesLogin` — création uniquement, toujours éditable, stoppé dès saisie manuelle (`loginManual`) ou en édition (`editingId`). Le composant hôte doit exposer `editingId/nom/prenom/login` ; mettre `nom`/`prenom`/`login` en `wire:model.live.debounce` (pour que le login s'affiche pendant la saisie).
- `wire:model` property et `wire:submit` method → noms différents obligatoires (ex. propriété `login` → méthode `login_request`)
- Auth sur le champ `login` (pas l'email) : `Auth::attempt(['login' => ..., 'password' => ...])`
- Protéger les routes par rôle avec `type:admin` / `type:client`
- Modifier la nav admin (libellés/icônes/ordre) → `App\Support\Navigation` uniquement (sidebar + favoris en dépendent)
- Pistes d'évolution notées dans `IDEAS.md` (drag&drop favoris, icône/couleur, pages récentes, multi-notes…)
- Livewire 4 embarque Alpine.js → ne pas l'importer/démarrer dans `app.js` (2ᵉ instance = casse `wire:navigate`). Pour **enrichir** Alpine, s'enregistrer sur `document.addEventListener('alpine:init', () => window.Alpine.data(...))` (c'est ce que fait le date picker).
- **Dates = Flatpickr** (pas l'input natif) : composant `<x-date-input label name model floatError />` (`model` = nom de la propriété Livewire). JS : `Alpine.data('datePicker', (model, classes))` dans `app.js` (locale FR, `dateFormat:'Y-m-d'` stocké / `altInput` affiché `j M Y`, `allowInput:false` → clic n'importe où ouvre le calendrier). Le champ est dans un `wire:ignore` (Flatpickr possède le DOM), le message d'erreur reste hors du `wire:ignore` pour se rafraîchir. Thème (teal/amber) dans `app.css` (overrides `.flatpickr-*` après `@import 'flatpickr/dist/flatpickr.css'`). `@this.set(model, str)` renvoie la valeur `Y-m-d` à Livewire.
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
