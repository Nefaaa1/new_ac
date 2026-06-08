<?php

namespace App\Livewire\Admin;

use App\Models\Action;
use App\Models\Contrat;
use App\Models\Site;
use App\Models\Ticket;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Dashboard extends Component
{
    /** Cartes de comptage (filtrées par l'accès de l'admin connecté). */
    #[Computed]
    public function stats(): array
    {
        $admin = auth()->user();

        return [
            [
                'label' => 'Sites',
                'value' => Site::accessibleBy($admin)->count(),
                'icon' => 'globe',
                'tone' => 'primary',
                'href' => 'admin.sites',
            ],
            [
                'label' => 'Clients',
                'value' => User::where('type', 'client')->accessibleBy($admin)->count(),
                'icon' => 'users',
                'tone' => 'secondary',
                'href' => 'admin.clients',
            ],
            [
                'label' => 'Contrats',
                'value' => Contrat::accessibleBy($admin)->count(),
                'icon' => 'file-text',
                'tone' => 'primary',
                'href' => 'admin.contrats',
            ],
            [
                'label' => 'Tickets ouverts',
                'value' => $this->openTicketsQuery()->count(),
                'icon' => 'ticket',
                'tone' => 'secondary',
                'href' => 'admin.tickets',
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
        $admin = auth()->user();
        $equipeIds = $admin->equipes->pluck('id');

        return $this->openTicketsQuery()
            ->with(['site' => fn ($q) => $q->withTrashed()->with('client.user'), 'statut'])
            ->where(function ($q) use ($admin, $equipeIds) {
                $q->where('utilisateur_id', $admin->id)
                    ->orWhereIn('equipe_id', $equipeIds);
            })
            ->orderByRaw("FIELD(importance, 'elevee', 'moyenne', 'faible')")
            ->orderBy('date')
            ->limit(6)
            ->get();
    }

    /** Activité du mois en cours sur les contrats accessibles. */
    #[Computed]
    public function activiteMois(): array
    {
        $admin = auth()->user();

        $actions = Action::whereHas('contrat', fn ($q) => $q->accessibleBy($admin))
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year);

        return [
            'actions' => (clone $actions)->count(),
            'heures' => Action::formatHeures((float) (clone $actions)->sum('temps')),
            'tickets_termines' => Ticket::whereHas('site', fn ($q) => $q->withTrashed()->accessibleBy($admin))
                ->whereNotNull('terminee_at')
                ->whereMonth('terminee_at', now()->month)
                ->whereYear('terminee_at', now()->year)
                ->count(),
        ];
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
