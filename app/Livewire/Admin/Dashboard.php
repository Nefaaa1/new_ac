<?php

namespace App\Livewire\Admin;

use App\Models\Action;
use App\Models\Contrat;
use App\Models\DevisStatut;
use App\Models\Ticket;
use App\Models\TicketStatut;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.admin')]
class Dashboard extends Component
{
    /** Confirmation éphémère après une création express (ticket). */
    public ?string $flash = null;

    #[On('ticket-saved')]
    public function onTicketSaved(string $demande): void
    {
        $this->flash = 'Ticket « '.$demande.' » créé.';
    }

    /** Toast + re-rendu (compteurs / crédits) après une saisie express d'action. */
    #[On('action-saved')]
    public function onActionSaved(string $contrat): void
    {
        $this->flash = 'Action enregistrée sur « '.$contrat.' ».';
    }

    /**
     * Compteurs « ma charge » : 4 chiffres actionnables, chacun cliquable
     * vers la liste pré-filtrée correspondante.
     */
    #[Computed]
    public function counters(): array
    {
        $admin = auth()->user();

        $aTraiter = $this->openTicketsQuery()->where($this->mineScope())->count();

        // Sous-info du « À traiter » : la date d'un ticket = date de la demande
        // (pas une échéance) → retard = demande datée de plus d'un mois.
        $enRetard = $this->openTicketsQuery()
            ->where($this->mineScope())
            ->whereDate('date', '<', now()->subMonth()->toDateString())
            ->count();

        $firstDevis = DevisStatut::orderBy('position')->first();
        $aDeviser = $this->openTicketsQuery()
            ->where('a_deviser', true)
            ->when($firstDevis, fn ($q) => $q->where('devis_statut_id', $firstDevis->id))
            ->count();

        // Proxy en attendant le bouton « Terminer » (terminee_at) : statut clôture
        // + dernière modification dans le mois.
        $statutCloture = TicketStatut::where('cloture', true)->first();
        $clotures = Ticket::query()
            ->whereHas('site', fn ($q) => $q->withTrashed()->accessibleBy($admin))
            ->whereHas('statut', fn ($q) => $q->where('cloture', true))
            ->whereBetween('updated_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        $mesActions = Action::query()
            ->where('createur_id', $admin->id)
            ->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        return [
            [
                'label' => 'Tickets à traiter',
                'value' => $aTraiter,
                'sub' => $enRetard > 0 ? 'dont '.$enRetard.' en retard' : null,
                'icon' => 'list-checks',
                'tone' => 'primary',
                'href' => route('admin.tickets', ['assigneFilter' => 'u:'.$admin->id]),
            ],
            [
                'label' => 'À deviser',
                'value' => $aDeviser,
                'sub' => null,
                'icon' => 'file-text',
                'tone' => 'secondary',
                'href' => $firstDevis
                    ? route('admin.tickets', ['devisFilter' => $firstDevis->id])
                    : route('admin.tickets'),
            ],
            [
                'label' => 'Clôturés ce mois',
                'value' => $clotures,
                'sub' => null,
                'icon' => 'check-check',
                'tone' => 'emerald',
                'href' => $statutCloture
                    ? route('admin.tickets', ['statutFilter' => $statutCloture->id])
                    : route('admin.tickets'),
            ],
            [
                'label' => 'Mes actions ce mois',
                'value' => $mesActions,
                'sub' => null,
                'icon' => 'zap',
                'tone' => 'secondary',
                'href' => route('admin.actions'),
            ],
        ];
    }

    /**
     * Mes tickets à traiter : ouverts (statut non-clôture), attribués à moi
     * ou à une de mes équipes, triés par importance puis date.
     */
    #[Computed]
    public function myTickets()
    {
        return $this->openTicketsQuery()
            ->with(['site' => fn ($q) => $q->withTrashed()->with('client.user'), 'statut'])
            ->where($this->mineScope())
            ->orderByRaw("FIELD(importance, 'elevee', 'moyenne', 'faible')")
            ->orderBy('date')
            ->limit(8)
            ->get();
    }

    /**
     * Crédits du mois : les 3 contrats avec le plus de temps saisi ce mois-ci
     * (1 crédit = 1 h), avec jauge temps / crédits quand le contrat a des crédits.
     */
    #[Computed]
    public function creditsContrats()
    {
        $tempsMois = Action::selectRaw('COALESCE(SUM(temps), 0)')
            ->whereColumn('contrat_id', 'contrats.id')
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month);

        return Contrat::accessibleBy(auth()->user())
            ->select('contrats.*')
            ->selectSub($tempsMois, 'temps_mois')
            ->whereHas('actions', fn ($q) => $q->whereYear('date', now()->year)->whereMonth('date', now()->month))
            ->orderByDesc('temps_mois')
            ->limit(3)
            ->get()
            ->map(fn (Contrat $c) => [
                'contrat' => $c,
                'temps' => (float) $c->temps_mois,
                'credits' => (float) $c->credits,
                'pct' => $c->credits > 0
                    ? (int) round((float) $c->temps_mois / (float) $c->credits * 100)
                    : null,
            ]);
    }

    /** Scope « attribué à moi ou à une de mes équipes ». */
    protected function mineScope(): \Closure
    {
        $admin = auth()->user();
        $equipeIds = $admin->equipes->pluck('id');

        return function ($q) use ($admin, $equipeIds) {
            $q->where('utilisateur_id', $admin->id)
                ->orWhereIn('equipe_id', $equipeIds);
        };
    }

    /** Base : tickets ouverts (statut non marqué clôture) et accessibles. */
    protected function openTicketsQuery()
    {
        return Ticket::query()
            ->whereHas('site', fn ($q) => $q->withTrashed()->accessibleBy(auth()->user()))
            ->whereDoesntHave('statut', fn ($q) => $q->where('cloture', true));
    }

    public function render()
    {
        return view('livewire.admin.dashboard');
    }
}
