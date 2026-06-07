<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\WithSorting;
use App\Models\Action;
use App\Models\Contrat;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.admin')]
class Actions extends Component
{
    use WithSorting;

    public bool $showForm = false;
    public ?int $editingId = null;

    /** Force le remount du <livewire:contrat-picker> à chaque ouverture du formulaire. */
    public int $formNonce = 0;

    /** Recherche libre : intitulé / commentaire / contrat. */
    #[Url(except: '')]
    public string $search = '';

    // Champs du formulaire
    public string $intitule = '';
    public string $temps = '';
    public string $date = '';
    public string $type = '';
    public ?int $contrat_id = null;
    public string $commentaire = '';

    public function mount(): void
    {
        $this->sortField = $this->sortField ?: 'date';
        $this->sortDirection = $this->sortDirection ?: 'desc';
    }

    #[Computed]
    public function actions()
    {
        $query = Action::query()
            ->with('contrat.client.user')
            ->whereHas('contrat', fn ($q) => $q->accessibleBy(auth()->user()))
            ->when($this->search !== '', function ($query) {
                $term = '%'.$this->search.'%';
                $query->where(function ($sub) use ($term) {
                    $sub->where('intitule', 'like', $term)
                        ->orWhere('commentaire', 'like', $term)
                        ->orWhereHas('contrat', fn ($c) => $c->where('libelle', 'like', $term));
                });
            });

        $dir = $this->sortDir();

        match ($this->sortField) {
            'intitule' => $query->orderBy('intitule', $dir),
            'type'     => $query->orderBy('type', $dir),
            'temps'    => $query->orderBy('temps', $dir),
            default    => $query->orderBy('date', $dir)->orderByDesc('id'), // date
        };

        return $query->get();
    }

    public function create(): void
    {
        $this->reset(['editingId', 'intitule', 'temps', 'type', 'contrat_id', 'commentaire']);
        $this->date = now()->format('Y-m-d');
        $this->formNonce++;
        $this->resetValidation();
        $this->showForm = true;
    }

    public function editAction(int $id): void
    {
        $action = Action::whereHas('contrat', fn ($q) => $q->accessibleBy(auth()->user()))->findOrFail($id);

        $this->editingId = $action->id;
        $this->intitule = $action->intitule;
        $this->temps = (string) $action->temps;
        $this->date = $action->date->format('Y-m-d');
        $this->type = $action->type;
        $this->contrat_id = $action->contrat_id;
        $this->commentaire = $action->commentaire ?? '';
        $this->formNonce++;
        $this->resetValidation();
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'intitule' => 'required|string|max:255',
            'temps' => 'required|numeric|min:0',
            'date' => 'required|date',
            'type' => ['required', 'in:'.implode(',', array_keys(Action::TYPES))],
            'contrat_id' => ['required', 'integer', Rule::exists('contrats', 'id')],
            'commentaire' => 'nullable|string|max:2000',
        ]);

        // Le contrat doit être accessible à l'admin connecté.
        Contrat::accessibleBy(auth()->user())->findOrFail($data['contrat_id']);

        $attributes = [
            'intitule' => $data['intitule'],
            'temps' => $data['temps'],
            'date' => $data['date'],
            'type' => $data['type'],
            'contrat_id' => $data['contrat_id'],
            'commentaire' => $data['commentaire'] ?: null,
        ];

        if ($this->editingId) {
            $action = Action::whereHas('contrat', fn ($q) => $q->accessibleBy(auth()->user()))->findOrFail($this->editingId);
            $action->update($attributes);
        } else {
            Action::create($attributes);
        }

        $this->showForm = false;
    }

    public function deleteAction(int $id): void
    {
        Action::whereHas('contrat', fn ($q) => $q->accessibleBy(auth()->user()))->findOrFail($id)->delete();
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetValidation();
    }

    protected function validationAttributes(): array
    {
        return [
            'intitule' => 'intitulé',
            'contrat_id' => 'contrat',
            'temps' => 'temps',
        ];
    }

    public function render()
    {
        return view('livewire.admin.actions');
    }
}
