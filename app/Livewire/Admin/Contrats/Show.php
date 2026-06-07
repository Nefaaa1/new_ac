<?php

namespace App\Livewire\Admin\Contrats;

use App\Models\Action;
use App\Models\Contrat;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Show extends Component
{
    public Contrat $contrat;

    public function mount(Contrat $contrat): void
    {
        abort_unless(auth()->user()->canAccess($contrat), 403);

        $this->contrat = $contrat->load('client.user', 'reseaux');
    }

    /**
     * Actions du contrat regroupées par mois : mois en cours puis mois précédent.
     *
     * @return array<int, array{title: string, label: string, count: int, totalLabel: string, actions: \Illuminate\Support\Collection}>
     */
    #[Computed]
    public function monthlyActions(): array
    {
        $now = now();

        return [
            $this->actionBucket('Mois en cours', $now),
            $this->actionBucket('Mois précédent', $now->copy()->subMonthNoOverflow()),
        ];
    }

    protected function actionBucket(string $title, Carbon $month): array
    {
        $actions = Action::where('contrat_id', $this->contrat->id)
            ->whereBetween('date', [
                $month->copy()->startOfMonth()->toDateString(),
                $month->copy()->endOfMonth()->toDateString(),
            ])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        return [
            'title' => $title,
            'label' => $month->copy()->locale('fr')->isoFormat('MMMM YYYY'),
            'count' => $actions->count(),
            'totalLabel' => Action::formatHeures((float) $actions->sum(fn (Action $a) => (float) $a->temps)),
            'actions' => $actions,
        ];
    }

    public function deleteContrat()
    {
        abort_unless(auth()->user()->canAccess($this->contrat), 403);

        $this->contrat->delete();

        return $this->redirectRoute('admin.contrats', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.contrats.show');
    }
}
