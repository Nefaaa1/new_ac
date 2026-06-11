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
GET /admin/sites                admin.sites           (Admin\Sites)            # liste
GET /admin/sites/create         admin.sites.create    (Admin\Sites\Form)       # création (page pleine)
GET /admin/sites/{site}         admin.sites.show      (Admin\Sites\Show)       # fiche (page pleine)
GET /admin/sites/{site}/edit    admin.sites.edit      (Admin\Sites\Form)       # édition (même composant que create)
GET /admin/contrats             admin.contrats        (Admin\Contrats)         # liste
GET /admin/contrats/create      admin.contrats.create (Admin\Contrats\Form)    # création (page pleine)
GET /admin/contrats/{contrat}   admin.contrats.show   (Admin\Contrats\Show)    # fiche (page pleine)
GET /admin/contrats/{contrat}/edit admin.contrats.edit (Admin\Contrats\Form)   # édition (même composant que create)
GET /admin/clients          admin.clients     (Admin\Clients)
GET /admin/actions          admin.actions     (Admin\Actions)
GET /admin/tickets          admin.tickets     (Admin\Tickets)
GET /admin/chatbots         admin.chatbots    (Admin\Chatbots)
GET /admin/profil           admin.profil      (Admin\Profil)
GET /admin/recap/actions    admin.recap.actions (Admin\Recap\Actions)
GET /admin/recap/tickets    admin.recap.tickets (Admin\Recap\Tickets)
GET /admin/gestion/admins         admin.gestion.admins         (Admin\Gestion\Admins)        # +middleware can:manage-admins
GET /admin/gestion/equipes        admin.gestion.equipes        (Admin\Gestion\Equipes)       # équipes d'admins (DnD)
GET /admin/gestion/statuts        admin.gestion.statuts        (Admin\Gestion\Statuts)       # statuts de site
GET /admin/gestion/statuts-tickets admin.gestion.ticket-statuts (Admin\Gestion\TicketStatuts) # statuts de ticket
GET /admin/gestion/statuts-devis  admin.gestion.devis-statuts  (Admin\Gestion\DevisStatuts)  # états de devis
# (tout le groupe gestion porte le middleware can:manage-admins)

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
- **Accès granulaire par ressource** : table polymorphe `access_grants` (`user_id`, `grantable_type`, `grantable_id`, unique). Modèle `AccessGrant`. On accorde aujourd'hui des **clients** (`grantable_type = User::class`), des **contrats** (`grantable_type = Contrat::class`) **et des sites** (`grantable_type = Site::class`) via le form admin (cases à cocher `grantedClientIds` / `grantedContratIds` / `grantedSiteIds`, visibles si `accessLevel === restricted`).
- **Application** : trait `App\Models\Concerns\RestrictsAccess` → scope `Model::accessibleBy($admin)` (renvoie tout si full, sinon filtre sur les grants). Posé sur `User`(client), `Contrat` et `Site`. `User::canAccess($resource)` pour un check unitaire (utilisé par `Contrats\Form`/`Show` → `abort_unless(canAccess, 403)`). Sync centralisé : `Admins::syncGrants()` → `syncGrantsFor($admin, $type, $ids)` (purge + recrée par type ; full = purge tout). Ajouter un type accordable = un `array $grantedXIds` + une règle de validation + un appel `syncGrantsFor` + un bloc de cases dans la vue.
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
- **Form** : onglets (Alpine `x-show="$wire.activeTab===…"`, source de vérité = prop Livewire `activeTab` pour rester synchro avec `addReseau()`) — **Général** (champs contrat) + **Réseaux sociaux** (lignes répétables `public array $reseaux`, `addReseau()`/`removeReseau($i)` + `wire:key`). Champs **obligatoires** : `libelle`, `type`, `cycle_facturation`, `taux_horaire`, `credits` (validation `required` ; colonnes DB restent nullable — même convention que `societe`). `date_fin`, `client_id`, `site_web` facultatifs. **Client choisi via `<livewire:admin.client-picker wire:model="client_id">`** (autocomplétion). Mots de passe : input `:type="show?'text':'password'"` + œil Alpine. `save()` upsert le contrat puis `syncReseaux()` (updateOrCreate par `id`, supprime les `whereNotIn($keep)`), puis `redirectRoute('admin.contrats.show', navigate:true)`. Garde `abort_unless(canAccess, 403)` en édition.
- **Show** : onglets Alpine pur (`x-data="{ tab }"`, lecture seule) — **Général** (chiffres clés + `<dl>` détails), **Réseaux sociaux** (cartes, mot de passe masqué `••••` + œil reveal), **Actions**. Boutons Modifier / Supprimer (`deleteContrat()` → redirect liste).
  - Onglet **Actions** : computed `monthlyActions()` → 2 buckets (`Mois en cours` / `Mois précédent`) via `actionBucket($title, Carbon)` (`whereBetween('date', startOfMonth..endOfMonth)`, libellé mois FR `->locale('fr')->isoFormat('MMMM YYYY')`, total d'heures `Action::formatHeures()`). Helper `Action::formatHeures(float)` / `Action->tempsLabel()` = heures décimales → « 2,5 h » (réutilisé dans la liste Actions).
- **Liste** : tableau coloré + `WithSorting` (tri défaut `date_debut desc`) + recherche libre `#[Url(except:'')] $search` (libellé / site / société / nom-prénom client). Rattachement client affiché en chip société.
- **Recherche globale** : `ContratSource` **actif** (libellé / site / société, `accessibleBy`, deep-link `admin.contrats.show`).
- Sidebar : sous-pages contrats gardent **Contrats** actif via `routeIs($route.'.*')` (ajouté au layout) ; `<title>` retombe sur la rubrique parente (`Str::beforeLast(name,'.')`) si la sous-route n'est pas dans `Navigation`.
- **Pré-remplissage libellé** : `Contrats\Form` (création) lit `request()->query('libelle')` → permet d'arriver depuis l'autocomplétion d'action avec le libellé saisi déjà rempli.

## Actions (CRUD)
- `Admin\Actions` (route `admin.actions`) : CRUD en **slide-over** (même UX que clients/admins) — table colorée + `WithSorting` (défaut `date desc`) + recherche libre `#[Url(except:'')] $search` (intitulé / commentaire / libellé contrat) + soft delete.
  - **Filtres mois + année** (`#[Url(except:'')] $month` / `$year`, sentinelle `'all'` = tous) : `mount()` les initialise au **mois et année en cours** (sauf si déjà fournis par l'URL). `whereMonth`/`whereYear` appliqués si ≠ `'all'`. Computed `monthsList()` (libellés FR via Carbon) + `yearsList()` (années distinctes présentes + année courante/sélectionnée).
- Table `actions` : `id`, `intitule` (requis), `temps` (decimal 6,2, **heures** ex. 1.50 = 1h30, requis), `date` (requis), `type` (requis), `contrat_id` (FK `contrats` cascade, **requis**), `commentaire?`, timestamps, `softDeletes`. Modèle `Action` : `HasFactory + SoftDeletes`, casts `date`/`decimal:2`, `contrat() belongsTo`, const `TYPES` (site_web | reseaux_sociaux | redaction | graphisme | intranet) + `typeLabel()`. **Pas de `RestrictsAccess` direct** : l'accès est dérivé du contrat → la liste/edit/delete filtrent par `whereHas('contrat', withTrashed()->accessibleBy(...))` (un admin restreint ne voit que les actions de ses contrats accordés). `save()` revérifie `Contrat::withTrashed()->accessibleBy(...)->findOrFail()`.
- **Contrat supprimé (soft delete) → actions conservées + alerte** : la liste charge le contrat en `withTrashed()` (eager `with(['contrat'=>fn($q)=>$q->withTrashed()...])` + `whereHas(...withTrashed())`), donc une action dont le contrat est archivé **reste visible** avec un badge rouge « Contrat supprimé » (libellé barré, lien non cliquable car la fiche Show 404 sur un trashed). Édition/suppression restent possibles (guards en `withTrashed`) ; à l'édition, bannière d'alerte (`$contratTrashed` posé dans `editAction()`) et le `ContratPicker` pré-remplit le libellé du contrat archivé (mount en `withTrashed`) — on peut garder tel quel ou réaffecter à un contrat actif (la recherche du picker, elle, n'expose **que** les contrats actifs).
- Formulaire : `<x-date-input>` (date), `<x-text-input type=number step=0.25>` (temps), `<x-select>` (type), **`<livewire:admin.contrat-picker>`** (contrat, cf. ci-dessous), `<textarea>` (commentaire). Le picker est keyé `:key="'contrat-picker-'.$formNonce"` (`$formNonce++` à chaque `create()`/`editAction()`) pour **remonter proprement** à chaque ouverture (sinon il garderait l'état précédent — le slide-over reste dans le DOM).

## Sites (CRUD)
- **Vraie page par site** (PAS de slide-over, comme Contrats) : 3 composants full-page — `Admin\Sites` (liste), `Admin\Sites\Form` (création **et** édition, onglets), `Admin\Sites\Show` (fiche lecture seule, onglets). Routes sous `prefix('sites')->name('sites.')` : `create` / `{site}` (show) / `{site}/edit` — **`create` AVANT `{site}`**.
- Table `sites` : `id`, `nom` (requis), `client_id?` (FK `clients.id` `nullOnDelete` — **client facultatif**), `boutique_en_ligne` (bool, défaut false), `statut_id?` (FK `statuts.id` `nullOnDelete`), `date_statut?` (date — **requise si le statut choisi a `requiert_date=true`**), `mot_de_passe_complementaire?` (text, cast **`encrypted`** — champ texte libre), timestamps, `softDeletes`. Modèle `Site` : `HasFactory + RestrictsAccess + SoftDeletes`, relations `client()`/`statut() belongsTo`, `hebergement()`/`ftp()`/`bdd()`/`wordpress() hasOne`.
- **4 onglets credentials = 4 tables 1‑1** (`belongsTo Site`, FK `cascadeOnDelete`, `unique` sur `site_id`, **tous les `mot_de_passe*` cast `encrypted`**, chacune un `client_visible` bool pour l'espace client futur) : `site_hebergement` (`SiteHebergement`, const `PERIODES` mensuelle|annuelle + `paiement_agence` bool + **`paiement_mois`** texte libre), `site_ftp` (`SiteFtp`), `site_bdd` (`SiteBdd`), `site_wordpress` (`SiteWordpress` : accès admin + accès client). `protected $table` explicite sur chaque (noms singuliers). Chaque modèle expose **`hasData()`** (au moins un identifiant renseigné, mot de passe testé via `getRawOriginal` pour éviter le déchiffrement) → utilisé par la liste pour l'indicateur « renseigné ».
- **Form** : onglets (Livewire prop `activeTab`, `x-show="$wire.activeTab===…"`) Général + Hébergement + FTP + Base de données + WordPress. Onglet Général = champs `sites` (statut en `wire:model.live` → computed `dateRequise` affiche/active la date), **client choisi via `<livewire:admin.client-picker wire:model="client_id">`** (autocomplétion). **Onglet WordPress = 2 cartes côte à côte** (accès administrateur / accès client, `grid lg:grid-cols-2`). **Onglet Hébergement** : `paiement_mois` (champ libre) ne s'affiche que si `paiement_agence` coché (`x-show="$wire.hebergement.paiement_agence"`, checkbox en `wire:model.live`). Onglets credentials = **propriétés tableau** `public array $hebergement/$ftp/$bdd/$wordpress` (`wire:model="hebergement.nom"`…). `save()` upsert le site puis `updateOrCreate([], nullify($section))` sur chaque relation 1‑1 (la ligne existe toujours pour un site) → `redirectRoute('admin.sites.show')`. Garde `abort_unless(canAccess, 403)` en édition. `nullify()` = chaînes vides → null (les booléens restent intacts).
- **Show** : onglets Alpine pur (`x-data="{ tab }"`) Général (chiffres clés + mot de passe complémentaire masqué/reveal) + 4 onglets credentials (cartes, `<x-admin.cred-field>` pour chaque identifiant, mot de passe masqué `••••` + œil, badge `<x-admin.client-visible-badge>`). Statut affiché en chip **couleur hex inline** (`style="background-color:{couleur}1a; color:{couleur}"`).
- **Liste** : tableau coloré + `WithSorting` (seul `nom` triable) + recherche libre `#[Url(except:'')] $search` (nom / société / nom-prénom client), `accessibleBy`. **2 filtres** (`#[Url(except:'')]`) : `statutFilter` (par `statut_id`, options = `statutsList()`) et `paiementFilter` (`agence` = `whereHas('hebergement', paiement_agence=true)` / `direct` = `whereDoesntHave(...)`). Colonnes : **Site** (nom + chip statut dessous, et à droite picto paiement agence — avec `paiement_mois` en petit dessous s'il est rempli — + picto boutique), **Client**, puis **Hébergement / FTP / BDD / WordPress** = `<x-admin.fill-indicator :filled="$site->relation?->hasData()">` (check emerald = renseigné, tiret zinc = vide). Les 4 relations credentials sont eager-loaded.
- **Recherche globale** : `SiteSource` **actif** (nom / société, `accessibleBy`, deep-link `admin.sites.show`, icône `globe`).

## Tickets (CRUD)
- `Admin\Tickets` (route `admin.tickets`) : CRUD en **slide-over** (`max-w-xl`, UX clients/actions) — table colorée + `WithSorting` (défaut `date desc`, triable `date`/`demande`/`importance` — tri importance via `orderByRaw FIELD(faible,moyenne,elevee)`) + recherche libre `#[Url(except:'')] $search` (demande / descriptif / nom du site) + soft delete.
- Table `tickets` : `id`, `demande` (requis, titre), `descriptif?` (text), `site_id?` (FK `sites` `nullOnDelete`), `date?`, `statut_id?` (FK `ticket_statuts` `nullOnDelete`), `a_deviser` (bool, défaut false), `devis_statut_id?` (FK `devis_statuts` `nullOnDelete`), `temps_intervention?` (decimal 6,2 — heures), `importance` (string, défaut `moyenne`), `utilisateur_id?` (FK `users` = **attribué à**, admin), `createur_id?` (FK `users` = créateur, posé à `auth()->id()` à la création), timestamps, `softDeletes`. Modèle `Ticket` : `SoftDeletes`, casts `date`/`a_deviser` bool/`decimal:2`, relations `site()` / `statut() → TicketStatut` / `devisStatut() → DevisStatut` / `utilisateur()` / `createur()`. **Importance = constantes** `Ticket::IMPORTANCES` (faible|moyenne|elevee) + `IMPORTANCE_COLORS` (hex zinc/amber/rouge) ; helpers `importanceLabel()`/`importanceColor()`/`tempsLabel()` (réutilise `Action::formatHeures`).
- **Accès dérivé du site** (comme Action via contrat) : pas de `RestrictsAccess` direct → liste/edit/delete/save filtrent par `whereHas('site', withTrashed()->accessibleBy(...))` ; le site est eager-loaded en `withTrashed()` → un ticket dont le site est archivé reste visible (libellé barré + badge « archivé »). `save()` revérifie `Site::withTrashed()->accessibleBy()->findOrFail()`.
- **Attribution obligatoire = équipe OU utilisateur** (exactement un) : colonnes `utilisateur_id` / `equipe_id` (les deux nullable en DB, exclusivité **validée côté form**). Le formulaire pilote un **jeton unique** `public string $assigne` (`'u:<id>'` admin | `'e:<id>'` équipe ; select avec `<optgroup>` Équipes/Administrateurs, `required` + `Rule::in` des jetons valides) → `save()` parse le jeton en `utilisateur_id`/`equipe_id`. Liste : chip coloré de l'équipe (`Equipe::color()`) sinon avatar de l'admin. Filtre `assigneFilter` même jeton (`'none'` = non attribué). Relations `Ticket::equipe()` / `utilisateur()`.
- **Clôture (fondations, bouton à venir)** : flag `ticket_statuts.cloture` (bool, **un seul** statut de clôture — `TicketStatuts::save()` décoche les autres ; seedé sur « Terminée ») désigne le statut terminal ; colonnes `tickets.terminee_at` + `termine_par_id` (relation `terminePar()`) prêtes pour le futur bouton « Terminer ».
- **Devis** : `a_deviser` (checkbox `wire:model.live`) → si coché, bloc `devis_statut_id` visible (`x-show="$wire.a_deviser"`) ; `save()` neutralise `devis_statut_id` si non coché, sinon retombe sur le 1er état de devis. Liste : chip coloré de l'état de devis seulement si `a_deviser` (sinon `—`).
- **Création** : défauts posés dans `create()` → date du jour + statut « À faire » (1ʳᵉ position). Site choisi via **`<livewire:admin.site-picker wire:model="site_id">`** (keyé `'site-picker-'.$formNonce`, `$formNonce++` à chaque `create()`/`editTicket()` pour remonter le picker — slide-over toujours dans le DOM).
- **Liste** (colonnes) : **Site** (nom + société, badge archivé) · **Date** · **Demande** (descriptif tronqué) · **Importance** (chip couleur `IMPORTANCE_COLORS`, triable) · **Statut** (chip couleur) · **Attribué à** (avatar + nom) · **Temps** (si défini) · **Devis** (chip si `a_deviser`) · Actions.
- **4 filtres** (`#[Url(except:'')]`) : `assigneFilter` (`'none'` = non attribués, sinon jeton `u:<id>`/`e:<id>`), `statutFilter` (statut_id), `importanceFilter` (faible|moyenne|elevee), `devisFilter` (`'sans'` = pas de devis, sinon id `devis_statut` avec `a_deviser=true`).

## Statuts (rubrique Gestion)
- **3 référentiels configurables** dans le groupe **Gestion** (Gate `manage-admins`, full only), tous en **slide-over** (UX admins/actions), tous avec libellé + couleur (`<input type=color>` + champ hex) + `softDeletes` + helper `color()`/`DEFAULT_COLOR` :
  - `Admin\Gestion\Statuts` (`admin.gestion.statuts`, nav **« Statuts sites »**) → table `statuts` : + `requiert_date` (bool — impose `date_statut` sur les sites). Modèle `Statut` (`hasMany sites()`).
  - `Admin\Gestion\TicketStatuts` (`admin.gestion.ticket-statuts`, nav **« Statuts tickets »**) → table `ticket_statuts` : + `position` (ordre workflow) + **`cloture` (bool)** = LE statut terminal (un seul, `save()` décoche les autres). Modèle `TicketStatut` (cast `cloture` bool, `hasMany tickets(), 'statut_id'`). **Valeurs seedées** (À faire / En attente de retour / En cours / Terminée=clôture).
  - `Admin\Gestion\Equipes` (`admin.gestion.equipes`, nav **« Équipes »**) → table `equipes` (`nom`, `couleur`, softDeletes) + pivot `equipe_user`. Modèle `Equipe` (`belongsToMany members() = User`, `hasMany tickets()`, `color()`). CRUD slide-over ; **membres en drag & drop** (Alpine `teamMembers` dans `app.js` : 2 zones Disponibles/Membres, `memberIds` entanglé, réassignation du tableau pour synchro entangle, clic = bascule). `User::equipes()` côté inverse.
  - `Admin\Gestion\DevisStatuts` (`admin.gestion.devis-statuts`, nav **« Statuts devis »**) → table `devis_statuts` : + `position`. Modèle `DevisStatut` (`hasMany tickets(), 'devis_statut_id'`). **Valeurs seedées** (À deviser / En attente de validation / Validé / Refusé).
  - Suppression = soft delete → les lignes liées gardent la FK mais la relation renvoie null (pas de casse d'affichage).

## Autocomplétion contrat — `Admin\ContratPicker` (réutilisable)
- Composant Livewire **nested** branché par `wire:model` grâce à `#[Modelable] public ?int $contratId`. Usage : `<livewire:admin.contrat-picker wire:model="contrat_id" label="Contrat" :key="…" />`.
- Champ texte (autocomplete off) → `results()` (computed, `accessibleBy`, libellé `like`, min 2 car., limite 8) affichées dans un dropdown. `selectContrat($id)` fixe `contratId` + remplit le libellé ; **toute frappe (`updatedSearch`) ré-annule la sélection** (`contratId=null`) jusqu'à un nouveau choix → la validation `required` du parent reste fiable.
- **Création à la volée** : bouton « Créer le contrat « … » » → `createContrat()` = `redirectRoute('admin.contrats.create', ['libelle'=>$search], navigate:true)` (le libellé saisi pré-remplit le form contrat). ⚠️ redirige donc hors du formulaire en cours (comportement demandé).
- Erreur de validation : portée par le **parent** (`@error('contrat_id')` après la balise `<livewire:…>`), pas par le picker. Dropdown : Alpine `@entangle('showResults')` + `@click.outside`.

## Dashboard admin — cockpit sans scroll
- **Layout cockpit** (`Admin\Dashboard`) : root `flex min-h-0 flex-col gap-4 xl:h-full` → sur xl tout tient sans scroll, seule la liste « À traiter » scrolle en interne (`min-h-0` + `overflow-y-auto` obligatoires sur la chaîne flex). Zones : header (salutation + boutons « Nouvelle action » = focus du 1er input de la saisie express, « Nouveau ticket » = event `open-quick-ticket`) → 4 KPI → barre favoris (chips) → saisie express → zone flexible (tickets 2/3 + colonne droite 1/3 : crédits + pense-bête).
- **Computeds** : `counters()` = 4 chiffres actionnables cliquables vers les listes pré-filtrées — **Tickets à traiter** (miens, ouverts, sous-info rouge « dont X en retard » = demande datée de **plus d'un mois**, la date d'un ticket étant celle de la demande, pas une échéance), **À deviser** (1er état de devis), **Clôturés ce mois** (proxy : statut `cloture` + `updated_at` du mois, en attendant `terminee_at`), **Mes actions ce mois** (`actions.createur_id` = moi). `myTickets()` (limite 8, tri importance puis date). `creditsContrats()` = top 3 contrats par temps saisi ce mois (`selectSub` somme des actions), jauge temps/crédits (primary <80 %, secondary 80-100 %, rouge >100 % ; pas de jauge si `credits=0`). `openTicketsQuery()`/`mineScope()` = bases partagées.
- **`actions.createur_id`** (FK users `nullOnDelete`, nullable pour l'historique) : posé à `auth()->id()` à la création (`Admin\Actions::save()` + `QuickAction`), relation `Action::createur()`. Sert au compteur « Mes actions ce mois ».
- **Saisie express d'action** (`Admin\QuickAction`, embarqué) : une rangée `grid lg:grid-cols-12 items-end` (contrat-picker 3 / intitulé 3 / type 2 / temps 1 / date 2 / bouton icône `corner-down-left` ambre `h-11`). Liseré dégradé secondary→primary en tête + fond `from-secondary/[0.04]`. Tous les champs en taille **md** (le picker n'a pas de prop size → md partout pour l'alignement). Après save : la **date est conservée** (saisies en chaîne), reset du reste, `pickerNonce++`, dispatch `action-saved(contrat)` → le dashboard affiche un **toast fixe** bottom-right (`$flash`, auto-dismiss 4 s) et se re-rend ; la vue re-focus le picker (`x-on:action-saved.window`).
- **Ticket express** (`Admin\QuickTicket`, embarqué) : **modal centré** (PAS slide-over — overlay `backdrop-blur-sm`, `x-transition` scale, liseré primary). Ouvert par event `open-quick-ticket` (`#[On]` → défauts : date du jour, importance moyenne, assigné = moi). Champs : demande, descriptif, site-picker, date, importance, attribution (jeton `u:`/`e:` comme Tickets) ; statut auto = 1er du workflow. `save()` → dispatch `ticket-saved(demande)` → toast dashboard.
- **Titres de cartes normalisés** : `text-[11px] font-semibold uppercase tracking-[0.2em] text-zinc-500` + icône `h-3.5 w-3.5` (primary = données, secondary = outils de saisie/perso) ; chiffres en `tabular-nums`.
- **Note pense-bête** : table `notes` (`user_id` unique, `content`) → `User hasOne`. Composant `Admin\Notepad`
  embarqué dans le dashboard (compact : `xl:flex-1` absorbe l'élasticité de la colonne droite), auto-save `wire:model.live.debounce.800ms` + hook `updatedContent` (updateOrCreate).
- **Favoris** : table `favorites` (`user_id`, `label`, `route_name`, `params` json, `position`, `unique(user_id,route_name)`).
  - `Admin\FavoriteToggle` (topbar) : étoile toggle sur la page courante ; popover Alpine pour nommer (pré-rempli).
  - `Admin\Favorites` (dashboard) : **barre de chips** horizontale (croix de suppression au survol, `group-hover:pr-7`) ; plus de renommage ici (le nom se fixe à la création via le popover topbar) ; garde `Route::has()` (ignore routes disparues).
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
  - `Search/Sources/ClientSource` + `ContratSource` + `SiteSource` : **actifs** (filtrés par `accessibleBy`, deep-link vers la fiche). Activer une entité = remplir son source, rien d'autre à toucher.
  - Ajouter une entité recherchable → nouvelle classe `SearchSource` + l'enregistrer dans `Search::sources()`.

## Structure views
```
layouts/guest.blade.php            ← login (carte blanche centrée)
layouts/admin.blade.php            ← espace admin : sidebar noire (zinc-950) + topbar (fond clair)
layouts/panel.blade.php            ← dashboard client (thème zinc-950)
livewire/auth/login.blade.php
livewire/admin/dashboard.blade.php  + profil + notepad + favorites + favorite-toggle + global-search + contrat-picker
   + une vue par page (sites, contrats, clients, actions, tickets, chatbots)
   livewire/admin/contrats/{form,show}.blade.php + livewire/admin/sites/{form,show}.blade.php (fiches pleine page + onglets)
   et livewire/admin/recap/{actions,tickets}.blade.php + admin/gestion/{admins,statuts}.blade.php
livewire/client/dashboard.blade.php
components/admin/page-header.blade.php  ← props: title, subtitle, icon (en-tête de page admin)
components/admin/empty-state.blade.php  ← props: icon, title (état « en construction »)
components/admin/sort-header.blade.php  ← <th> triable : props field, label, sort, direction (→ sortBy)
components/admin/cred-field.blade.php   ← affiche un identifiant (props: label, value, password, link) — fiches Sites
components/admin/client-visible-badge.blade.php ← badge « Visible/Masqué client » (prop: visible)
components/admin/fill-indicator.blade.php ← pastille « renseigné/vide » (prop: filled) — liste Sites
components/field-label.blade.php    ← label partagé des champs (props: label, required) — astérisque amber « obligatoire »
components/text-input.blade.php     ← props: label, size, name, error (erreur intégrée)
components/select.blade.php          ← jumeau de text-input pour les <select> (options en slot)
components/textarea.blade.php        ← jumeau de text-input pour les <textarea> (props: label, name, rows, floatError)
components/checkbox.blade.php        ← case à cocher stylée (props: label, hint ; wire:model en attribut)
components/password-input.blade.php  ← input mot de passe + œil de révélation Alpine (props: label, name, floatError)
components/date-input.blade.php      ← sélecteur de date Flatpickr (props: label, name, model, floatError) — cf. § Dates
components/primary-button.blade.php ← props: icon (lucide), text, size, full ; hover inversé
components/input-label / input-error / auth-session-status
```
**Layout admin = app shell** : `body` en `h-screen overflow-hidden`, **sidebar + topbar figées**, seul le `<main>` scrolle (`flex-1 overflow-y-auto`). Ne pas remettre la topbar en `sticky` ni rendre le `body` scrollable.
**`<title>` dynamique** : le layout admin le dérive de `Navigation::find(route courante)` → `"{label} · {app.name}"` (mis à jour aussi via `wire:navigate`) ; guest = « Connexion · … », panel = « Espace client · … ». Favicon `public/favicon.ico` lié dans les 3 layouts. `APP_NAME="Partner Web Communication"` (penser à l'aligner dans le `.env` du VPS).
Sidebar admin (`layouts/admin.blade.php`) : boucle sur `App\Support\Navigation::groups()` —
**Informations** (point `primary`), **Récap mensuel** (point `secondary`) et **Gestion** (point `rose`, clé `'can' => 'manage-admins'` → masqué aux admins restreints ; contient Administrateurs + **Statuts sites / Statuts tickets / Statuts devis**). Lien actif via `request()->routeIs()`.
Logo blanc `public/images/Logo-website-blanc.png` → retour dashboard.
Topbar : `<livewire:admin.global-search>` (recherche universelle) + `<livewire:admin.favorite-toggle>` (étoile) + dropdown utilisateur (Profil / Déconnexion).

## Composants Livewire (app/Livewire/)
- `Auth\Login` (#[Layout guest])
- `Admin\*` (#[Layout admin], full-page) : `Dashboard`, `Chatbots`,
  `Profil`, `Recap\Actions`, `Recap\Tickets` — pages simples = en-tête + empty-state ;
  `Clients` = CRUD clients (cf. section Clients) ; `Contrats` (liste) + `Contrats\Form` + `Contrats\Show` = CRUD contrats pleine page (cf. section Contrats) ; `Sites` (liste) + `Sites\Form` + `Sites\Show` = CRUD sites pleine page (cf. section Sites) ; `Actions` = CRUD actions en slide-over (cf. section Actions) ; `Tickets` = CRUD tickets en slide-over + filtres (cf. section Tickets) ; `Gestion\Admins` = CRUD admins + suspension + accès, `Gestion\Equipes` = CRUD équipes (membres en drag & drop), `Gestion\Statuts` / `Gestion\TicketStatuts` / `Gestion\DevisStatuts` = CRUD des 3 référentiels de statuts (cf. sections dédiées)
- `Admin\*` (imbriqués, sans #[Layout]) : `Notepad` (note auto-save), `Favorites` (barre de chips dashboard),
  `QuickAction` (saisie express d'action, dashboard), `QuickTicket` (modal ticket express, dashboard),
  `FavoriteToggle` (étoile topbar), `GlobalSearch` (recherche universelle topbar → `App\Support\Search`),
  `ContratPicker` (autocomplétion contrat `#[Modelable]`, cf. section dédiée),
  `ClientPicker` (autocomplétion client `#[Modelable]`, calqué sur `ContratPicker` : recherche société/nom, filtré `accessibleBy`, **sans** bouton « créer » ; utilisé par `Sites\Form` et `Contrats\Form` pour `client_id`),
  `SitePicker` (autocomplétion site `#[Modelable]`, même patron : recherche nom/société, filtré `accessibleBy` ; utilisé par `Tickets` pour `site_id`)
- `Client\Dashboard` (#[Layout panel])
- À venir : `Server` (reloadNginx), `Logs` (polling + pause), `Monitor` (CPU/RAM/disk)
- `App\Support\SystemMetrics` : lecture `/proc` Linux — **demo mode automatique sur Windows** (valeurs aléatoires + badge jaune), toujours conserver ce comportement

## Conventions & pièges connus
- **Champ obligatoire = astérisque amber** : tous les composants de champ (`text-input`, `select`, `textarea`, `date-input`, `password-input`, `contrat-picker`) acceptent un prop booléen **`required`** → affiche un astérisque `secondary` après le label (via le composant partagé `<x-field-label>`) + `aria-required` sur l'input. **Convention : on marque le requis, on ne met PAS de mention « (facultatif) »** (l'absence d'astérisque suffit). Pas de `required` HTML natif (la validation reste Livewire côté serveur). Marquer un champ = ajouter `required` à la balise (aligner sur les règles `required` de validation du composant).
- Formulaires admin/clients : champs via `<x-text-input>` / `<x-select>` (cohérence design) avec **label au-dessus** + prop **`floatError`** (message d'erreur en position absolue → ne décale pas la mise en page) ; grilles 2-col en `items-start`, body en `space-y-6`. Erreurs auto-détectées via `name`. (Le login garde l'erreur inline classique, `floatError` non passé.) Login **auto-généré** `prénomnom` (collé, sans accent ni séparateur — `preg_replace('/[^a-z0-9]/','', Str::ascii(...))`) par le trait `App\Livewire\Concerns\GeneratesLogin` — création uniquement, toujours éditable, stoppé dès saisie manuelle (`loginManual`) ou en édition (`editingId`). Le composant hôte doit exposer `editingId/nom/prenom/login` ; mettre `nom`/`prenom`/`login` en `wire:model.live.debounce` (pour que le login s'affiche pendant la saisie).
- `wire:model` property et `wire:submit` method → noms différents obligatoires (ex. propriété `login` → méthode `login_request`)
- Auth sur le champ `login` (pas l'email) : `Auth::attempt(['login' => ..., 'password' => ...])`
- Protéger les routes par rôle avec `type:admin` / `type:client`
- Modifier la nav admin (libellés/icônes/ordre) → `App\Support\Navigation` uniquement (sidebar + favoris en dépendent)
- Pistes d'évolution notées dans `IDEAS.md` (drag&drop favoris, icône/couleur, pages récentes, multi-notes…)
- Livewire 4 embarque Alpine.js → ne pas l'importer/démarrer dans `app.js` (2ᵉ instance = casse `wire:navigate`). Pour **enrichir** Alpine, s'enregistrer sur `document.addEventListener('alpine:init', () => window.Alpine.data(...))` (c'est ce que fait le date picker).
- **Dates = Flatpickr** (pas l'input natif) : composant `<x-date-input label name model floatError />` (`model` = nom de la propriété Livewire). JS : `Alpine.data('datePicker', (model, classes))` dans `app.js` (locale FR, `dateFormat:'Y-m-d'` stocké / `altInput` affiché `j M Y`, `allowInput:false` → clic n'importe où ouvre le calendrier). Le champ est dans un `wire:ignore` (Flatpickr possède le DOM), le message d'erreur reste hors du `wire:ignore` pour se rafraîchir. Thème (teal/amber) dans `app.css` (overrides `.flatpickr-*` après `@import 'flatpickr/dist/flatpickr.css'`). `@this.set(model, str)` renvoie la valeur `Y-m-d` à Livewire. Le datePicker **`$wire.$watch(model, …)`** resynchronise le calendrier quand la propriété change côté serveur (défaut à la création, pré-remplissage en édition, reset) — nécessaire car le slide-over reste dans le DOM (`wire:ignore`) et Flatpickr ne relit pas `defaultDate`.
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
