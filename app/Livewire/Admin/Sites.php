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
            ->with(['client.user', 'statut'])
            ->when($this->search !== '', function ($query) {
                $term = '%'.$this->search.'%';
                $query->where(function ($sub) use ($term) {
                    $sub->where('nom', 'like', $term)
                        ->orWhereHas('client', fn ($c) => $c->where('societe', 'like', $term)
                            ->orWhereHas('user', fn ($u) => $u->where('nom', 'like', $term)
                                ->orWhere('prenom', 'like', $term)));
                });
            });

        $dir = $this->sortDir();

        match ($this->sortField) {
            'date_statut' => $query->orderBy('date_statut', $dir),
            'boutique'    => $query->orderBy('boutique_en_ligne', $dir),
            default       => $query->orderBy('nom', $dir), // nom
        };

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
