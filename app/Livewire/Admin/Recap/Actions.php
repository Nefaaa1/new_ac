<?php

namespace App\Livewire\Admin\Recap;

use App\Models\Action;
use App\Models\Contrat;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.admin')]
class Actions extends Component
{
    /** Taux des charges appliqués au total HT (repris de l'ancien intranet). */
    public const TVA = 0.20;
    public const RSI = 0.244;

    /** Recherche libre par libellé de contrat. */
    #[Url(except: '')]
    public string $search = '';

    /** Mois sélectionné (1..12). Défaut = mois en cours (posé au mount). */
    #[Url(except: '')]
    public string $month = '';

    /** Année sélectionnée (AAAA). Défaut = année en cours (posée au mount). */
    #[Url(except: '')]
    public string $year = '';

    public function mount(): void
    {
        $this->month = $this->month !== '' ? $this->month : (string) now()->month;
        $this->year = $this->year !== '' ? $this->year : (string) now()->year;
    }

    /** Libellés des mois (FR) pour le filtre. */
    #[Computed]
    public function monthsList(): array
    {
        return collect(range(1, 12))->mapWithKeys(fn ($m) => [
            $m => Str::ucfirst(Carbon::create(null, $m, 1)->locale('fr')->isoFormat('MMMM')),
        ])->all();
    }

    /** Années présentes dans les actions accessibles (+ année courante / sélectionnée). */
    #[Computed]
    public function yearsList(): array
    {
        $years = Action::query()
            ->whereHas('contrat', fn ($q) => $q->accessibleBy(auth()->user()))
            ->selectRaw('DISTINCT YEAR(date) as y')
            ->pluck('y')
            ->filter()
            ->map(fn ($y) => (int) $y)
            ->all();

        $years[] = (int) now()->year;

        if ($this->year !== '') {
            $years[] = (int) $this->year;
        }

        $years = array_values(array_unique($years));
        rsort($years);

        return $years;
    }

    /**
     * Une ligne par contrat : crédit/temps du mois, crédit/temps de l'année et montant à facturer.
     *
     * Équivalent Laravel de ContratV2::recap2() : on récupère les contrats accessibles avec
     * deux sous-requêtes agrégées (temps du mois + temps de l'année jusqu'au mois) puis on calcule
     * le « à facturer HT » selon le type de contrat (logique de l'ancien JS resultat()).
     */
    #[Computed]
    public function rows(): array
    {
        $month = (int) $this->month;
        $year = (int) $this->year;

        // Temps passé sur le mois sélectionné.
        $tempsMois = Action::selectRaw('COALESCE(SUM(temps), 0)')
            ->whereColumn('contrat_id', 'contrats.id')
            ->whereYear('date', $year)
            ->whereMonth('date', $month);

        // Temps passé sur l'année, du 1er janvier jusqu'au mois sélectionné inclus.
        $tempsAnnee = Action::selectRaw('COALESCE(SUM(temps), 0)')
            ->whereColumn('contrat_id', 'contrats.id')
            ->whereYear('date', $year)
            ->whereRaw('MONTH(date) <= ?', [$month]);

        // Nombre d'actions du mois (pour le statut des contrats fixes).
        $nbActions = Action::selectRaw('COUNT(*)')
            ->whereColumn('contrat_id', 'contrats.id')
            ->whereYear('date', $year)
            ->whereMonth('date', $month);

        $contrats = Contrat::accessibleBy(auth()->user())
            ->when($this->search !== '', fn ($q) => $q->where('libelle', 'like', '%'.$this->search.'%'))
            ->select('contrats.*')
            ->selectSub($tempsMois, 'temps_mois')
            ->selectSub($tempsAnnee, 'temps_annee')
            ->selectSub($nbActions, 'nb_actions')
            // Comme l'ancien recap2 : les contrats avec crédit OU ayant eu de l'activité ce mois-ci.
            ->where(function ($q) use ($month, $year) {
                $q->where('credits', '>', 0)
                    ->orWhereHas('actions', fn ($a) => $a->whereYear('date', $year)->whereMonth('date', $month));
            })
            ->orderBy('libelle')
            ->get();

        return $contrats->map(function (Contrat $c) {
            $credit = (float) $c->credits;
            $temps = (float) $c->temps_mois;
            $taux = (float) $c->taux_horaire;
            $heuresSup = max(0, $temps - $credit);

            $factHT = match ($c->type) {
                'horaire'        => $credit * $taux,
                'horaire_sup'    => ($credit + $heuresSup) * $taux,
                'fixe'           => $taux,
                'sup_temps_reel' => $temps * $taux,
                default          => 0.0,
            };

            $creditAnnuel = $credit * match ($c->cycle_facturation) {
                'mensuel'     => 12,
                'trimestriel' => 4,
                default       => 1,
            };

            // Statut visuel : fixe = activité ou non ; sinon comparaison crédit / temps.
            $statut = match (true) {
                $c->type === 'fixe' => (int) $c->nb_actions > 0 ? 'ok' : 'vide',
                $temps > $credit    => 'depassement',
                $temps == $credit && $credit > 0 => 'ok',
                default             => 'restant',
            };

            return [
                'contrat'        => $c,
                'type_label'     => $c->typeLabel(),
                'credit'         => $credit,
                'temps_mois'     => $temps,
                'credit_annuel'  => $creditAnnuel,
                'temps_annee'    => (float) $c->temps_annee,
                'heures_sup'     => $heuresSup,
                'fact_ht'        => round($factHT, 2),
                'statut'         => $statut,
                'is_fixe'        => $c->type === 'fixe',
            ];
        })->all();
    }

    /** Totaux financiers calculés sur la somme des « à facturer HT ». */
    #[Computed]
    public function totals(): array
    {
        $ht = collect($this->rows)->sum('fact_ht');

        return [
            'ht'  => round($ht, 2),
            'ttc' => round($ht * 1.2, 2),
            'tva' => round($ht * self::TVA, 2),
            'rsi' => round($ht * self::RSI, 2),
            'net' => round($ht * (1 - self::RSI), 2),
        ];
    }

    public function render()
    {
        return view('livewire.admin.recap.actions');
    }
}
