<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\WithSorting;
use App\Models\DevisStatut;
use App\Models\Equipe;
use App\Models\Site;
use App\Models\Ticket;
use App\Models\TicketStatut;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.admin')]
class Tickets extends Component
{
    use WithSorting;

    public bool $showForm = false;
    public ?int $editingId = null;

    /** Force le remount du <livewire:site-picker> à chaque ouverture du formulaire. */
    public int $formNonce = 0;

    /** Recherche libre : demande / descriptif / site. */
    #[Url(except: '')]
    public string $search = '';

    /** Filtre attribution : '' (tous) | 'none' (non attribués) | 'u:<id>' admin | 'e:<id>' équipe. */
    #[Url(except: '')]
    public string $assigneFilter = '';

    /** Filtre statut (statut_id). */
    #[Url(except: '')]
    public string $statutFilter = '';

    /** Filtre devis : '' (tous) | 'sans' (pas de devis) | id devis_statut. */
    #[Url(except: '')]
    public string $devisFilter = '';

    // Champs du formulaire
    public string $demande = '';
    public string $descriptif = '';
    public ?int $site_id = null;
    public string $date = '';
    public ?int $statut_id = null;
    public string $importance = 'moyenne';
    public bool $a_deviser = false;
    public ?int $devis_statut_id = null;
    public string $temps_intervention = '';

    /** Attribution (obligatoire) : 'u:<id>' admin | 'e:<id>' équipe. */
    public string $assigne = '';

    public function mount(): void
    {
        $this->sortField = $this->sortField ?: 'date';
        $this->sortDirection = $this->sortDirection ?: 'desc';
    }

    /** Statuts de ticket (ordre workflow). */
    #[Computed]
    public function statutsList()
    {
        return TicketStatut::orderBy('position')->orderBy('libelle')->get();
    }

    /** États de devis. */
    #[Computed]
    public function devisStatutsList()
    {
        return DevisStatut::orderBy('position')->orderBy('libelle')->get();
    }

    /** Admins (attribution + filtre). */
    #[Computed]
    public function adminsList()
    {
        return User::where('type', 'admin')
            ->whereNull('suspended_at')
            ->orderBy('nom')->orderBy('prenom')
            ->get();
    }

    /** Équipes (attribution + filtre). */
    #[Computed]
    public function equipesList()
    {
        return Equipe::withCount('members')->orderBy('nom')->get();
    }

    #[Computed]
    public function tickets()
    {
        $query = Ticket::query()
            ->with([
                'site' => fn ($q) => $q->withTrashed()->with('client.user'),
                'statut', 'devisStatut', 'utilisateur', 'equipe',
            ])
            ->whereHas('site', fn ($q) => $q->withTrashed()->accessibleBy(auth()->user()))
            ->when($this->search !== '', function ($query) {
                $term = '%'.$this->search.'%';
                $query->where(function ($sub) use ($term) {
                    $sub->where('demande', 'like', $term)
                        ->orWhere('descriptif', 'like', $term)
                        ->orWhereHas('site', fn ($s) => $s->withTrashed()->where('nom', 'like', $term));
                });
            })
            ->when($this->assigneFilter === 'none', fn ($q) => $q->whereNull('utilisateur_id')->whereNull('equipe_id'))
            ->when(str_starts_with($this->assigneFilter, 'u:'), fn ($q) => $q->where('utilisateur_id', (int) substr($this->assigneFilter, 2)))
            ->when(str_starts_with($this->assigneFilter, 'e:'), fn ($q) => $q->where('equipe_id', (int) substr($this->assigneFilter, 2)))
            ->when($this->statutFilter !== '', fn ($q) => $q->where('statut_id', $this->statutFilter))
            ->when($this->devisFilter === 'sans', fn ($q) => $q->where('a_deviser', false))
            ->when($this->devisFilter !== '' && $this->devisFilter !== 'sans', fn ($q) => $q->where('a_deviser', true)->where('devis_statut_id', $this->devisFilter));

        $dir = $this->sortDir();

        match ($this->sortField) {
            'demande' => $query->orderBy('demande', $dir),
            default   => $query->orderBy('date', $dir)->orderByDesc('id'), // date
        };

        return $query->get();
    }

    public function create(): void
    {
        $this->reset([
            'editingId', 'demande', 'descriptif', 'site_id', 'statut_id',
            'a_deviser', 'devis_statut_id', 'temps_intervention', 'assigne',
        ]);
        $this->importance = 'moyenne';
        $this->date = now()->format('Y-m-d');
        $this->statut_id = $this->statutsList->first()?->id; // « À faire » (position 1)
        $this->formNonce++;
        $this->resetValidation();
        $this->showForm = true;
    }

    public function editTicket(int $id): void
    {
        $ticket = Ticket::whereHas('site', fn ($q) => $q->withTrashed()->accessibleBy(auth()->user()))
            ->findOrFail($id);

        $this->editingId = $ticket->id;
        $this->demande = $ticket->demande;
        $this->descriptif = $ticket->descriptif ?? '';
        $this->site_id = $ticket->site_id;
        $this->date = $ticket->date?->format('Y-m-d') ?? '';
        $this->statut_id = $ticket->statut_id;
        $this->importance = $ticket->importance;
        $this->a_deviser = $ticket->a_deviser;
        $this->devis_statut_id = $ticket->devis_statut_id;
        $this->temps_intervention = $ticket->temps_intervention !== null ? (string) $ticket->temps_intervention : '';
        $this->assigne = $ticket->equipe_id ? 'e:'.$ticket->equipe_id : ($ticket->utilisateur_id ? 'u:'.$ticket->utilisateur_id : '');
        $this->formNonce++;
        $this->resetValidation();
        $this->showForm = true;
    }

    public function save(): void
    {
        // Jetons d'attribution valides : 'u:<id>' (admins) + 'e:<id>' (équipes).
        $validAssignees = $this->adminsList->map(fn ($a) => 'u:'.$a->id)
            ->merge($this->equipesList->map(fn ($e) => 'e:'.$e->id))
            ->all();

        $data = $this->validate([
            'demande' => 'required|string|max:255',
            'descriptif' => 'nullable|string|max:5000',
            'site_id' => ['required', 'integer', Rule::exists('sites', 'id')],
            'date' => 'required|date',
            'statut_id' => ['required', 'integer', Rule::exists('ticket_statuts', 'id')],
            'importance' => ['required', 'in:'.implode(',', array_keys(Ticket::IMPORTANCES))],
            'a_deviser' => 'boolean',
            'devis_statut_id' => ['nullable', 'integer', Rule::exists('devis_statuts', 'id')],
            'temps_intervention' => 'nullable|numeric|min:0',
            'assigne' => ['required', Rule::in($validAssignees)],
        ]);

        // Le site doit être accessible à l'admin connecté (withTrashed : on tolère un site archivé).
        Site::withTrashed()->accessibleBy(auth()->user())->findOrFail($data['site_id']);

        // Attribution : exactement un de utilisateur_id / equipe_id selon le jeton.
        [$type, $assigneId] = explode(':', $data['assigne']);
        $utilisateurId = $type === 'u' ? (int) $assigneId : null;
        $equipeId = $type === 'e' ? (int) $assigneId : null;

        // Devis : si pas à deviser, on neutralise l'état ; sinon on retombe sur le 1er état par défaut.
        $devisStatutId = null;
        if ($data['a_deviser']) {
            $devisStatutId = $data['devis_statut_id'] ?: $this->devisStatutsList->first()?->id;
        }

        $attributes = [
            'demande' => $data['demande'],
            'descriptif' => $data['descriptif'] ?: null,
            'site_id' => $data['site_id'],
            'date' => $data['date'],
            'statut_id' => $data['statut_id'],
            'importance' => $data['importance'],
            'a_deviser' => $data['a_deviser'],
            'devis_statut_id' => $devisStatutId,
            'temps_intervention' => $data['temps_intervention'] !== '' ? $data['temps_intervention'] : null,
            'utilisateur_id' => $utilisateurId,
            'equipe_id' => $equipeId,
        ];

        if ($this->editingId) {
            $ticket = Ticket::whereHas('site', fn ($q) => $q->withTrashed()->accessibleBy(auth()->user()))->findOrFail($this->editingId);
            $ticket->update($attributes);
        } else {
            $attributes['createur_id'] = auth()->id();
            Ticket::create($attributes);
        }

        $this->showForm = false;
    }

    public function deleteTicket(int $id): void
    {
        Ticket::whereHas('site', fn ($q) => $q->withTrashed()->accessibleBy(auth()->user()))->findOrFail($id)->delete();
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetValidation();
    }

    protected function validationAttributes(): array
    {
        return [
            'demande' => 'demande',
            'site_id' => 'site',
            'statut_id' => 'statut',
            'devis_statut_id' => 'état du devis',
            'temps_intervention' => 'temps d’intervention',
            'assigne' => 'attribution',
        ];
    }

    public function render()
    {
        return view('livewire.admin.tickets');
    }
}
