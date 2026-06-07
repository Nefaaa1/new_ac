<?php

namespace App\Livewire\Admin;

use App\Models\Contrat;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Modelable;
use Livewire\Component;

/**
 * Champ de recherche de contrat avec autocomplétion (réutilisable).
 * Se branche sur une propriété du parent via `wire:model` (id du contrat).
 *
 * Usage : <livewire:admin.contrat-picker wire:model="contrat_id" label="Contrat" />
 * Pré-remplit le libellé en édition ; propose la création d'un contrat si rien ne correspond.
 */
class ContratPicker extends Component
{
    /** Id du contrat sélectionné, lié au parent par wire:model. */
    #[Modelable]
    public ?int $contratId = null;

    public ?string $label = 'Contrat';

    public string $placeholder = 'Rechercher un contrat…';

    /** Texte saisi dans le champ. */
    public string $search = '';

    /** Visibilité du menu déroulant (entanglé côté Alpine). */
    public bool $showResults = false;

    public function mount(): void
    {
        // Édition : pré-remplit le libellé si un contrat est déjà sélectionné.
        // withTrashed : un contrat supprimé (soft delete) reste affiché tant qu'il est lié.
        if ($this->contratId) {
            $this->search = Contrat::withTrashed()->accessibleBy(auth()->user())
                ->whereKey($this->contratId)
                ->value('libelle') ?? '';
        }
    }

    #[Computed]
    public function results()
    {
        $term = trim($this->search);

        if (mb_strlen($term) < 2) {
            return collect();
        }

        return Contrat::query()
            ->accessibleBy(auth()->user())
            ->with('client.user')
            ->where('libelle', 'like', "%{$term}%")
            ->orderBy('libelle')
            ->limit(8)
            ->get();
    }

    /** Toute frappe invalide la sélection précédente jusqu'à un nouveau choix. */
    public function updatedSearch(): void
    {
        $this->contratId = null;
        $this->showResults = true;
    }

    public function selectContrat(int $id): void
    {
        $contrat = Contrat::accessibleBy(auth()->user())->find($id);

        if (! $contrat) {
            return;
        }

        $this->contratId = $contrat->id;
        $this->search = $contrat->libelle;
        $this->showResults = false;
    }

    public function clearSelection(): void
    {
        $this->contratId = null;
        $this->search = '';
        $this->showResults = false;
    }

    /** Redirige vers la création d'un contrat, en pré-remplissant le libellé saisi. */
    public function createContrat()
    {
        return $this->redirectRoute('admin.contrats.create', ['libelle' => trim($this->search)], navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.contrat-picker');
    }
}
