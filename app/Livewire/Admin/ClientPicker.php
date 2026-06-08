<?php

namespace App\Livewire\Admin;

use App\Models\Client;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Modelable;
use Livewire\Component;

/**
 * Champ de recherche de client avec autocomplétion (réutilisable).
 * Se branche sur une propriété du parent via `wire:model` (id de la fiche client = clients.id).
 *
 * Usage : <livewire:admin.client-picker wire:model="client_id" label="Client" />
 * Pré-remplit le libellé en édition ; recherche par société / nom / prénom (filtré par accès).
 */
class ClientPicker extends Component
{
    /** Id de la fiche client sélectionnée (clients.id), lié au parent par wire:model. */
    #[Modelable]
    public ?int $clientId = null;

    public ?string $label = 'Client';

    /** Affiche l'astérisque amber « champ obligatoire » sur le label. */
    public bool $required = false;

    public string $placeholder = 'Rechercher un client…';

    /** Texte saisi dans le champ. */
    public string $search = '';

    /** Visibilité du menu déroulant (entanglé côté Alpine). */
    public bool $showResults = false;

    public function mount(): void
    {
        // Édition : pré-remplit le libellé si un client est déjà sélectionné.
        if ($this->clientId) {
            $this->search = $this->labelFor($this->clientId);
        }
    }

    /** Libellé affiché pour une fiche client (société sinon nom complet). */
    protected function labelFor(int $id): string
    {
        $client = Client::with('user')->find($id);

        if (! $client) {
            return '';
        }

        return $client->societe ?: ($client->user?->name ?? '');
    }

    #[Computed]
    public function results()
    {
        $term = trim($this->search);

        if (mb_strlen($term) < 2) {
            return collect();
        }

        return Client::query()
            ->with('user')
            ->whereHas('user', fn ($u) => $u->where('type', 'client')->accessibleBy(auth()->user()))
            ->where(function ($q) use ($term) {
                $q->where('societe', 'like', "%{$term}%")
                    ->orWhereHas('user', fn ($u) => $u
                        ->where('nom', 'like', "%{$term}%")
                        ->orWhere('prenom', 'like', "%{$term}%"));
            })
            ->limit(8)
            ->get()
            ->sortBy(fn (Client $c) => $c->societe ?: $c->user?->name)
            ->values();
    }

    /** Toute frappe invalide la sélection précédente jusqu'à un nouveau choix. */
    public function updatedSearch(): void
    {
        $this->clientId = null;
        $this->showResults = true;
    }

    public function selectClient(int $id): void
    {
        $client = Client::with('user')
            ->whereHas('user', fn ($u) => $u->where('type', 'client')->accessibleBy(auth()->user()))
            ->find($id);

        if (! $client) {
            return;
        }

        $this->clientId = $client->id;
        $this->search = $client->societe ?: ($client->user?->name ?? '');
        $this->showResults = false;
    }

    public function clearSelection(): void
    {
        $this->clientId = null;
        $this->search = '';
        $this->showResults = false;
    }

    public function render()
    {
        return view('livewire.admin.client-picker');
    }
}
