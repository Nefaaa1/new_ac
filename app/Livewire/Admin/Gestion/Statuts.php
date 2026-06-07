<?php

namespace App\Livewire\Admin\Gestion;

use App\Livewire\Concerns\WithSorting;
use App\Models\Statut;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Statuts extends Component
{
    use AuthorizesRequests;
    use WithSorting;

    public bool $showForm = false;
    public ?int $editingId = null;

    // Champs du formulaire
    public string $libelle = '';
    public string $couleur = '#00A4BC';
    public bool $requiert_date = false;

    public function mount(): void
    {
        $this->authorize('manage-admins');
        $this->sortField = $this->sortField ?: 'libelle';
    }

    #[Computed]
    public function statuts()
    {
        $query = Statut::withCount('sites');

        $dir = $this->sortDir();

        match ($this->sortField) {
            'requiert_date' => $query->orderBy('requiert_date', $dir),
            'sites'         => $query->orderBy('sites_count', $dir),
            default         => $query->orderBy('libelle', $dir), // libellé
        };

        return $query->get();
    }

    public function create(): void
    {
        $this->reset(['editingId', 'libelle', 'requiert_date']);
        $this->couleur = '#00A4BC';
        $this->resetValidation();
        $this->showForm = true;
    }

    public function editStatut(int $id): void
    {
        $statut = Statut::findOrFail($id);

        $this->editingId = $statut->id;
        $this->libelle = $statut->libelle;
        $this->couleur = $statut->couleur ?: Statut::DEFAULT_COLOR;
        $this->requiert_date = $statut->requiert_date;
        $this->resetValidation();
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->authorize('manage-admins');

        $data = $this->validate([
            'libelle' => 'required|string|max:255',
            'couleur' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'requiert_date' => 'boolean',
        ]);

        $attributes = [
            'libelle' => $data['libelle'],
            'couleur' => $data['couleur'],
            'requiert_date' => $data['requiert_date'],
        ];

        if ($this->editingId) {
            Statut::findOrFail($this->editingId)->update($attributes);
        } else {
            Statut::create($attributes);
        }

        $this->showForm = false;
    }

    public function deleteStatut(int $id): void
    {
        $this->authorize('manage-admins');

        // softDelete : les sites portant ce statut conservent statut_id mais
        // la relation renverra null (statut filtré) — pas de casse côté affichage.
        Statut::findOrFail($id)->delete();

        unset($this->statuts);
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetValidation();
    }

    protected function validationAttributes(): array
    {
        return [
            'libelle' => 'libellé',
            'couleur' => 'couleur',
        ];
    }

    public function render()
    {
        return view('livewire.admin.gestion.statuts');
    }
}
