<?php

namespace App\Livewire\Admin\Gestion;

use App\Livewire\Concerns\WithSorting;
use App\Models\DevisStatut;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class DevisStatuts extends Component
{
    use AuthorizesRequests;
    use WithSorting;

    public bool $showForm = false;
    public ?int $editingId = null;

    // Champs du formulaire
    public string $libelle = '';
    public string $couleur = '#00A4BC';
    public int $position = 0;

    public function mount(): void
    {
        $this->authorize('manage-admins');
        $this->sortField = $this->sortField ?: 'position';
    }

    #[Computed]
    public function statuts()
    {
        $query = DevisStatut::withCount('tickets');

        $dir = $this->sortDir();

        match ($this->sortField) {
            'libelle' => $query->orderBy('libelle', $dir),
            'tickets' => $query->orderBy('tickets_count', $dir),
            default   => $query->orderBy('position', $dir)->orderBy('libelle'), // position
        };

        return $query->get();
    }

    public function create(): void
    {
        $this->reset(['editingId', 'libelle', 'position']);
        $this->couleur = '#00A4BC';
        $this->resetValidation();
        $this->showForm = true;
    }

    public function editStatut(int $id): void
    {
        $statut = DevisStatut::findOrFail($id);

        $this->editingId = $statut->id;
        $this->libelle = $statut->libelle;
        $this->couleur = $statut->couleur ?: DevisStatut::DEFAULT_COLOR;
        $this->position = $statut->position;
        $this->resetValidation();
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->authorize('manage-admins');

        $data = $this->validate([
            'libelle' => 'required|string|max:255',
            'couleur' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'position' => 'integer|min:0',
        ]);

        if ($this->editingId) {
            DevisStatut::findOrFail($this->editingId)->update($data);
        } else {
            DevisStatut::create($data);
        }

        $this->showForm = false;
    }

    public function deleteStatut(int $id): void
    {
        $this->authorize('manage-admins');

        // softDelete : les tickets gardent devis_statut_id mais la relation renverra null.
        DevisStatut::findOrFail($id)->delete();

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
            'position' => 'position',
        ];
    }

    public function render()
    {
        return view('livewire.admin.gestion.devis-statuts');
    }
}
