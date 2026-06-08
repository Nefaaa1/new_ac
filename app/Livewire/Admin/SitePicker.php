<?php

namespace App\Livewire\Admin;

use App\Models\Site;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Modelable;
use Livewire\Component;

/**
 * Champ de recherche de site avec autocomplétion (réutilisable).
 * Se branche sur une propriété du parent via `wire:model` (id du site).
 *
 * Usage : <livewire:admin.site-picker wire:model="site_id" label="Site" />
 * Pré-remplit le libellé en édition ; recherche par nom / société (filtré par accès).
 */
class SitePicker extends Component
{
    /** Id du site sélectionné, lié au parent par wire:model. */
    #[Modelable]
    public ?int $siteId = null;

    public ?string $label = 'Site';

    /** Affiche l'astérisque amber « champ obligatoire » sur le label. */
    public bool $required = false;

    public string $placeholder = 'Rechercher un site…';

    /** Texte saisi dans le champ. */
    public string $search = '';

    /** Visibilité du menu déroulant (entanglé côté Alpine). */
    public bool $showResults = false;

    public function mount(): void
    {
        // Édition : pré-remplit le libellé si un site est déjà sélectionné.
        // withTrashed : un site archivé (soft delete) reste affiché tant qu'il est lié.
        if ($this->siteId) {
            $this->search = Site::withTrashed()->accessibleBy(auth()->user())
                ->whereKey($this->siteId)
                ->value('nom') ?? '';
        }
    }

    #[Computed]
    public function results()
    {
        $term = trim($this->search);

        if (mb_strlen($term) < 2) {
            return collect();
        }

        return Site::query()
            ->accessibleBy(auth()->user())
            ->with('client.user')
            ->where(function ($q) use ($term) {
                $q->where('nom', 'like', "%{$term}%")
                    ->orWhereHas('client', fn ($c) => $c->where('societe', 'like', "%{$term}%"));
            })
            ->orderBy('nom')
            ->limit(8)
            ->get();
    }

    /** Toute frappe invalide la sélection précédente jusqu'à un nouveau choix. */
    public function updatedSearch(): void
    {
        $this->siteId = null;
        $this->showResults = true;
    }

    public function selectSite(int $id): void
    {
        $site = Site::accessibleBy(auth()->user())->find($id);

        if (! $site) {
            return;
        }

        $this->siteId = $site->id;
        $this->search = $site->nom;
        $this->showResults = false;
    }

    public function clearSelection(): void
    {
        $this->siteId = null;
        $this->search = '';
        $this->showResults = false;
    }

    public function render()
    {
        return view('livewire.admin.site-picker');
    }
}
