<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\WithSorting;
use App\Models\Site;
use App\Models\Statut;
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

    /** Filtre statut (statut_id). */
    #[Url(except: '')]
    public string $statutFilter = '';

    /** Filtre paiement : '' (tous) | agence | direct. */
    #[Url(except: '')]
    public string $paiementFilter = '';

    public function mount(): void
    {
        $this->sortField = $this->sortField ?: 'nom';
    }

    /** Statuts proposés au filtre. */
    #[Computed]
    public function statutsList()
    {
        return Statut::orderBy('libelle')->get();
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
            })
            ->when($this->statutFilter !== '', fn ($q) => $q->where('statut_id', $this->statutFilter))
            // agence = hébergement avec paiement_agence ; direct = tout le reste (pas agence).
            ->when($this->paiementFilter === 'agence', fn ($q) => $q->whereHas('hebergement', fn ($h) => $h->where('paiement_agence', true)))
            ->when($this->paiementFilter === 'direct', fn ($q) => $q->whereDoesntHave('hebergement', fn ($h) => $h->where('paiement_agence', true)));

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
