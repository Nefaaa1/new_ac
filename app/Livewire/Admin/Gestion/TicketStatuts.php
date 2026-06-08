<?php

namespace App\Livewire\Admin\Gestion;

use App\Livewire\Concerns\WithSorting;
use App\Models\TicketStatut;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class TicketStatuts extends Component
{
    use AuthorizesRequests;
    use WithSorting;

    public bool $showForm = false;
    public ?int $editingId = null;

    // Champs du formulaire
    public string $libelle = '';
    public string $couleur = '#00A4BC';
    public int $position = 0;
    public bool $cloture = false;

    public function mount(): void
    {
        $this->authorize('manage-admins');
        $this->sortField = $this->sortField ?: 'position';
    }

    #[Computed]
    public function statuts()
    {
        $query = TicketStatut::withCount('tickets');

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
        $this->reset(['editingId', 'libelle', 'position', 'cloture']);
        $this->couleur = '#00A4BC';
        $this->resetValidation();
        $this->showForm = true;
    }

    public function editStatut(int $id): void
    {
        $statut = TicketStatut::findOrFail($id);

        $this->editingId = $statut->id;
        $this->libelle = $statut->libelle;
        $this->couleur = $statut->couleur ?: TicketStatut::DEFAULT_COLOR;
        $this->position = $statut->position;
        $this->cloture = $statut->cloture;
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
            'cloture' => 'boolean',
        ]);

        // Un seul statut de clôture : si on coche, on décoche les autres.
        if ($data['cloture']) {
            TicketStatut::where('cloture', true)
                ->when($this->editingId, fn ($q) => $q->whereKeyNot($this->editingId))
                ->update(['cloture' => false]);
        }

        if ($this->editingId) {
            TicketStatut::findOrFail($this->editingId)->update($data);
        } else {
            TicketStatut::create($data);
        }

        $this->showForm = false;
    }

    public function deleteStatut(int $id): void
    {
        $this->authorize('manage-admins');

        // softDelete : les tickets gardent statut_id mais la relation renverra null.
        TicketStatut::findOrFail($id)->delete();

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
        return view('livewire.admin.gestion.ticket-statuts');
    }
}
