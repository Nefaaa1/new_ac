<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\GeneratesLogin;
use App\Livewire\Concerns\WithSorting;
use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.admin')]
class Clients extends Component
{
    use GeneratesLogin;
    use WithSorting;

    public bool $showForm = false;
    public ?int $editingId = null; // id du User (client)

    /** Recherche libre : nom / prénom / société. Partagé via l'URL (deep-link depuis la recherche globale). */
    #[Url(except: '')]
    public string $search = '';

    /** Deep-link : id d'un client à ouvrir directement (depuis la recherche globale). */
    #[Url(except: null)]
    public ?int $open = null;

    public function mount(): void
    {
        $this->sortField = $this->sortField ?: 'societe';

        // Ouvre directement le client ciblé par la recherche globale, s'il est accessible.
        if ($this->open && User::where('type', 'client')->accessibleBy(auth()->user())->whereKey($this->open)->exists()) {
            $this->editClient($this->open);
        }
    }

    // Compte / personne (sur users)
    public string $civilite = '';
    public string $nom = '';
    public string $prenom = '';
    public string $login = '';
    public string $email = '';
    public string $email_secondaire = '';
    public string $telephone = '';

    // Fiche métier (sur clients)
    public string $societe = '';
    public string $lienapp = '';
    public string $email3 = '';

    /** Mot de passe généré, affiché une seule fois après création. */
    public ?string $generatedPassword = null;

    #[Computed]
    public function clients()
    {
        $query = User::where('type', 'client')
            ->accessibleBy(auth()->user())
            ->with('client')
            ->when($this->search !== '', function ($query) {
                $term = '%'.$this->search.'%';
                $query->where(function ($sub) use ($term) {
                    $sub->where('nom', 'like', $term)
                        ->orWhere('prenom', 'like', $term)
                        ->orWhereHas('client', fn ($c) => $c->where('societe', 'like', $term));
                });
            });

        $dir = $this->sortDir();
        $societe = Client::select('societe')->whereColumn('clients.user_id', 'users.id');

        match ($this->sortField) {
            'nom' => $query->orderBy('nom', $dir)->orderBy('prenom', $dir),
            'email' => $query->orderBy('email', $dir),
            default => $query->orderBy($societe, $dir)->orderBy('nom'), // société
        };

        return $query->get();
    }

    public function create(): void
    {
        $this->reset([
            'editingId', 'civilite', 'nom', 'prenom', 'login', 'email',
            'email_secondaire', 'telephone', 'societe', 'lienapp', 'email3', 'generatedPassword', 'loginManual',
        ]);
        $this->resetValidation();
        $this->showForm = true;
    }

    public function editClient(int $id): void
    {
        $client = User::where('type', 'client')->accessibleBy(auth()->user())->with('client')->findOrFail($id);

        $this->editingId = $client->id;
        $this->loginManual = true; // utilisateur existant : pas de génération auto
        $this->civilite = $client->civilite ?? '';
        $this->nom = $client->nom;
        $this->prenom = $client->prenom;
        $this->login = $client->login;
        $this->email = $client->email;
        $this->email_secondaire = $client->email_secondaire ?? '';
        $this->telephone = $client->telephone ?? '';
        $this->societe = $client->client?->societe ?? '';
        $this->lienapp = $client->client?->lienapp ?? '';
        $this->email3 = $client->client?->email3 ?? '';
        $this->generatedPassword = null;
        $this->resetValidation();
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'civilite' => 'required|in:M,Mme',
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'login' => ['required', 'string', 'max:255', Rule::unique('users', 'login')->ignore($this->editingId)->whereNull('deleted_at')],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->editingId)->whereNull('deleted_at')],
            'email_secondaire' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:50',
            'societe' => 'required|string|max:255',
            'lienapp' => 'nullable|string|max:255',
            'email3' => 'nullable|email|max:255',
        ]);

        $userAttributes = [
            'civilite' => $data['civilite'],
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'login' => $data['login'],
            'email' => $data['email'],
            'email_secondaire' => $data['email_secondaire'] ?: null,
            'telephone' => $data['telephone'] ?: null,
        ];

        $clientAttributes = [
            'societe' => $data['societe'] ?: null,
            'lienapp' => $data['lienapp'] ?: null,
            'email3' => $data['email3'] ?: null,
        ];

        if ($this->editingId) {
            $client = User::where('type', 'client')->accessibleBy(auth()->user())->findOrFail($this->editingId);
            $client->update($userAttributes);
        } else {
            $password = Str::password(16);
            $client = User::create($userAttributes + [
                'type' => 'client',
                'password' => $password, // hashé par le cast
            ]);
            $this->generatedPassword = $password;
        }

        $client->client()->updateOrCreate(['user_id' => $client->id], $clientAttributes);

        $this->showForm = false;
    }

    public function deleteClient(int $id): void
    {
        User::where('type', 'client')->accessibleBy(auth()->user())->findOrFail($id)->delete();
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->open = null;
        $this->resetValidation();
    }

    public function dismissPassword(): void
    {
        $this->generatedPassword = null;
    }

    public function render()
    {
        return view('livewire.admin.clients');
    }
}
