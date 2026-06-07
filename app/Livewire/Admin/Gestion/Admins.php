<?php

namespace App\Livewire\Admin\Gestion;

use App\Livewire\Concerns\GeneratesLogin;
use App\Livewire\Concerns\WithSorting;
use App\Models\Contrat;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Admins extends Component
{
    use AuthorizesRequests;
    use GeneratesLogin;
    use WithSorting;

    public bool $showForm = false;
    public ?int $editingId = null;

    public string $civilite = '';
    public string $nom = '';
    public string $prenom = '';
    public string $login = '';
    public string $email = '';
    public string $email_secondaire = '';
    public string $telephone = '';
    public string $accessLevel = 'restricted';

    /** @var array<int, int> ids des clients accordés (accès granulaire) */
    public array $grantedClientIds = [];

    /** @var array<int, int> ids des contrats accordés (accès granulaire) */
    public array $grantedContratIds = [];

    /** @var array<int, int> ids des sites accordés (accès granulaire) */
    public array $grantedSiteIds = [];

    /** Mot de passe généré, affiché une seule fois après création. */
    public ?string $generatedPassword = null;

    public function mount(): void
    {
        $this->authorize('manage-admins');
        $this->sortField = $this->sortField ?: 'nom';
    }

    #[Computed]
    public function admins()
    {
        $query = User::where('type', 'admin')->withCount('accessGrants');

        $dir = $this->sortDir();

        match ($this->sortField) {
            'email' => $query->orderBy('email', $dir),
            'access_level' => $query->orderBy('access_level', $dir),
            'suspended_at' => $query->orderBy('suspended_at', $dir),
            default => $query->orderBy('nom', $dir)->orderBy('prenom', $dir), // administrateur
        };

        return $query->get();
    }

    #[Computed]
    public function clients()
    {
        return User::where('type', 'client')
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get();
    }

    #[Computed]
    public function contrats()
    {
        return Contrat::with('client.user')
            ->orderBy('libelle')
            ->get();
    }

    #[Computed]
    public function sites()
    {
        return Site::with('client.user')
            ->orderBy('nom')
            ->get();
    }

    public function create(): void
    {
        $this->reset(['editingId', 'civilite', 'nom', 'prenom', 'login', 'email', 'email_secondaire', 'telephone', 'generatedPassword', 'loginManual']);
        $this->accessLevel = 'restricted';
        $this->grantedClientIds = [];
        $this->grantedContratIds = [];
        $this->grantedSiteIds = [];
        $this->resetValidation();
        $this->showForm = true;
    }

    public function editAdmin(int $id): void
    {
        $admin = User::where('type', 'admin')->findOrFail($id);

        $this->editingId = $admin->id;
        $this->loginManual = true; // utilisateur existant : pas de génération auto
        $this->civilite = $admin->civilite ?? '';
        $this->nom = $admin->nom;
        $this->prenom = $admin->prenom;
        $this->login = $admin->login;
        $this->email = $admin->email;
        $this->email_secondaire = $admin->email_secondaire ?? '';
        $this->telephone = $admin->telephone ?? '';
        $this->accessLevel = $admin->access_level;
        $this->grantedClientIds = $admin->accessGrants()
            ->where('grantable_type', User::class)
            ->pluck('grantable_id')
            ->all();
        $this->grantedContratIds = $admin->accessGrants()
            ->where('grantable_type', Contrat::class)
            ->pluck('grantable_id')
            ->all();
        $this->grantedSiteIds = $admin->accessGrants()
            ->where('grantable_type', Site::class)
            ->pluck('grantable_id')
            ->all();
        $this->generatedPassword = null;
        $this->resetValidation();
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->authorize('manage-admins');

        $data = $this->validate([
            'civilite' => 'required|in:M,Mme',
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'login' => ['required', 'string', 'max:255', Rule::unique('users', 'login')->ignore($this->editingId)->whereNull('deleted_at')],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->editingId)->whereNull('deleted_at')],
            'email_secondaire' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:50',
            'accessLevel' => 'required|in:full,restricted',
            'grantedClientIds' => 'array',
            'grantedClientIds.*' => 'integer|exists:users,id',
            'grantedContratIds' => 'array',
            'grantedContratIds.*' => 'integer|exists:contrats,id',
            'grantedSiteIds' => 'array',
            'grantedSiteIds.*' => 'integer|exists:sites,id',
        ]);

        $attributes = [
            'civilite' => $data['civilite'],
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'login' => $data['login'],
            'email' => $data['email'],
            'email_secondaire' => $data['email_secondaire'] ?: null,
            'telephone' => $data['telephone'] ?: null,
            'access_level' => $data['accessLevel'],
        ];

        if ($this->editingId) {
            $admin = User::where('type', 'admin')->findOrFail($this->editingId);

            // Le super-admin ne peut pas être rétrogradé.
            if ($admin->isSuperAdmin()) {
                $attributes['access_level'] = 'full';
                $this->accessLevel = 'full';
            }

            $admin->update($attributes);
        } else {
            $password = Str::password(16);
            $admin = User::create($attributes + [
                'type' => 'admin',
                'password' => $password, // hashé par le cast
            ]);
            $this->generatedPassword = $password;
        }

        $this->syncGrants($admin);

        $this->showForm = false;
    }

    /**
     * Synchronise les accès granulaires (clients + contrats).
     */
    protected function syncGrants(User $admin): void
    {
        if ($admin->access_level === 'full') {
            $admin->accessGrants()->delete();

            return;
        }

        $this->syncGrantsFor($admin, User::class, $this->grantedClientIds);
        $this->syncGrantsFor($admin, Contrat::class, $this->grantedContratIds);
        $this->syncGrantsFor($admin, Site::class, $this->grantedSiteIds);
    }

    /**
     * Remplace les grants d'un type de ressource donné par la sélection courante.
     *
     * @param  array<int, int>  $ids
     */
    protected function syncGrantsFor(User $admin, string $type, array $ids): void
    {
        $admin->accessGrants()->where('grantable_type', $type)->delete();

        foreach (array_unique($ids) as $id) {
            $admin->accessGrants()->create([
                'grantable_type' => $type,
                'grantable_id' => $id,
            ]);
        }
    }

    public function toggleSuspend(int $id): void
    {
        $this->authorize('manage-admins');

        $admin = User::where('type', 'admin')->findOrFail($id);

        if ($this->isProtected($admin)) {
            return;
        }

        $admin->update(['suspended_at' => $admin->isSuspended() ? null : now()]);
    }

    public function deleteAdmin(int $id): void
    {
        $this->authorize('manage-admins');

        $admin = User::where('type', 'admin')->findOrFail($id);

        if ($this->isProtected($admin)) {
            return;
        }

        $admin->delete();
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetValidation();
    }

    public function dismissPassword(): void
    {
        $this->generatedPassword = null;
    }

    /**
     * Compte protégé contre suspension / suppression (soi-même ou super-admin).
     */
    protected function isProtected(User $admin): bool
    {
        return $admin->id === auth()->id() || $admin->isSuperAdmin();
    }

    public function render()
    {
        return view('livewire.admin.gestion.admins');
    }
}
