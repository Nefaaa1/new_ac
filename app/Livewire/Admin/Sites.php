<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\WithSorting;
use App\Models\Site;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.admin')]
class Sites extends Component
{
    use WithSorting;

    /** Recherche libre : nom du site / société / client. */
    #[Url(except: '')]
    public string $search = '';

    public function mount(): void
    {
        $this->sortField = $this->sortField ?: 'nom';
    }

    #[Computed]
    public function sites()
    {
        $query = Site::query()
            ->accessibleBy(auth()->user())
            ->with(['client.user', 'statut', 'hebergement', 'ftp', 'bdd', 'wordpress'])
            ->when($this->search !== '', function ($query) {
                $term = '%'.$this->search.'%';
                $query->where(function ($sub) use ($term) {
                    $sub->where('nom', 'like', $term)
                        ->orWhereHas('client', fn ($c) => $c->where('societe', 'like', $term)
                            ->orWhereHas('user', fn ($u) => $u->where('nom', 'like', $term)
                                ->orWhere('prenom', 'like', $term)));
                });
            });

        // Seul le nom est triable dans la liste (les autres colonnes sont des indicateurs).
        $query->orderBy('nom', $this->sortDir());

        return $query->get();
    }

    public function deleteSite(int $id): void
    {
        Site::accessibleBy(auth()->user())->findOrFail($id)->delete();

        unset($this->sites);
    }

    public function render()
    {
        return view('livewire.admin.sites');
    }
}
