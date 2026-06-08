<?php

namespace App\Livewire\Admin\Gestion;

use App\Livewire\Concerns\WithSorting;
use App\Models\Equipe;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Equipes extends Component
{
    use AuthorizesRequests;
    use WithSorting;

    public bool $showForm = false;
    public ?int $editingId = null;

    // Champs du formulaire
    public string $nom = '';
    public string $couleur = '#00A4BC';

    /** Ids des admins membres de l'équipe (piloté par le drag & drop). */
    public array $memberIds = [];

    public function mount(): void
    {
        $this->authorize('manage-admins');
        $this->sortField = $this->sortField ?: 'nom';
    }

    /** Tous les admins (membres possibles). */
    #[Computed]
    public function adminsList()
    {
        return User::where('type', 'admin')
            ->whereNull('suspended_at')
            ->orderBy('nom')->orderBy('prenom')
            ->get();
    }

    #[Computed]
    public function equipes()
    {
        $query = Equipe::withCount('members')->with('members');

        $dir = $this->sortDir();

        match ($this->sortField) {
            'members' => $query->orderBy('members_count', $dir),
            default   => $query->orderBy('nom', $dir), // nom
        };

        return $query->get();
    }

    public function create(): void
    {
        $this->reset(['editingId', 'nom', 'memberIds']);
        $this->couleur = '#00A4BC';
        $this->resetValidation();
        $this->showForm = true;
    }

    public function editEquipe(int $id): void
    {
        $equipe = Equipe::with('members')->findOrFail($id);

        $this->editingId = $equipe->id;
        $this->nom = $equipe->nom;
        $this->couleur = $equipe->couleur ?: Equipe::DEFAULT_COLOR;
        $this->memberIds = $equipe->members->pluck('id')->all();
        $this->resetValidation();
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->authorize('manage-admins');

        $data = $this->validate([
            'nom' => 'required|string|max:255',
            'couleur' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'memberIds' => 'array',
            'memberIds.*' => 'integer|exists:users,id',
        ]);

        if ($this->editingId) {
            $equipe = Equipe::findOrFail($this->editingId);
            $equipe->update(['nom' => $data['nom'], 'couleur' => $data['couleur']]);
        } else {
            $equipe = Equipe::create(['nom' => $data['nom'], 'couleur' => $data['couleur']]);
        }

        $equipe->members()->sync($data['memberIds']);

        $this->showForm = false;
    }

    public function deleteEquipe(int $id): void
    {
        $this->authorize('manage-admins');

        // softDelete : les tickets gardent equipe_id mais la relation renverra null.
        Equipe::findOrFail($id)->delete();

        unset($this->equipes);
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetValidation();
    }

    protected function validationAttributes(): array
    {
        return [
            'nom' => 'nom',
            'couleur' => 'couleur',
        ];
    }

    public function render()
    {
        return view('livewire.admin.gestion.equipes');
    }
}
