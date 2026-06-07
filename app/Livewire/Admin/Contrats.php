<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\WithSorting;
use App\Models\Contrat;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.admin')]
class Contrats extends Component
{
    use WithSorting;

    /** Recherche libre : libellé / site web / société / client. */
    #[Url(except: '')]
    public string $search = '';

    public function mount(): void
    {
        $this->sortField = $this->sortField ?: 'date_debut';
        $this->sortDirection = $this->sortDirection ?: 'desc';
    }

    #[Computed]
    public function contrats()
    {
        $query = Contrat::query()
            ->accessibleBy(auth()->user())
            ->with('client.user')
            ->withCount('reseaux')
            ->when($this->search !== '', function ($query) {
                $term = '%'.$this->search.'%';
                $query->where(function ($sub) use ($term) {
                    $sub->where('libelle', 'like', $term)
                        ->orWhere('site_web', 'like', $term)
                        ->orWhereHas('client', fn ($c) => $c->where('societe', 'like', $term)
                            ->orWhereHas('user', fn ($u) => $u->where('nom', 'like', $term)
                                ->orWhere('prenom', 'like', $term)));
                });
            });

        $dir = $this->sortDir();

        match ($this->sortField) {
            'libelle' => $query->orderBy('libelle', $dir),
            'type'    => $query->orderBy('type', $dir),
            'cycle'   => $query->orderBy('cycle_facturation', $dir),
            'credits' => $query->orderBy('credits', $dir),
            default   => $query->orderBy('date_debut', $dir), // date_debut
        };

        return $query->get();
    }

    public function deleteContrat(int $id): void
    {
        Contrat::accessibleBy(auth()->user())->findOrFail($id)->delete();

        unset($this->contrats);
    }

    public function render()
    {
        return view('livewire.admin.contrats');
    }
}
