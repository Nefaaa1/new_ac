<?php

namespace App\Livewire\Admin\Contrats;

use App\Models\Contrat;
use App\Models\ContratReseau;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Form extends Component
{
    public ?int $editingId = null;

    public string $activeTab = 'general';

    // Onglet Général
    public string $libelle = '';
    public ?int $client_id = null;
    public string $site_web = '';
    public string $type = '';
    public string $date_debut = '';
    public string $date_fin = '';
    public string $taux_horaire = '';
    public string $cycle_facturation = '';
    public string $credits = '';

    /**
     * Onglet Réseaux sociaux : lignes répétables.
     *
     * @var array<int, array{id: int|null, reseau: string, identifiant: string, mot_de_passe: string, gestion: string}>
     */
    public array $reseaux = [];

    public function mount(?Contrat $contrat = null): void
    {
        if ($contrat && $contrat->exists) {
            abort_unless(auth()->user()->canAccess($contrat), 403);

            $contrat->load('reseaux');

            $this->editingId = $contrat->id;
            $this->libelle = $contrat->libelle;
            $this->client_id = $contrat->client_id;
            $this->site_web = $contrat->site_web ?? '';
            $this->type = $contrat->type ?? '';
            $this->date_debut = $contrat->date_debut?->format('Y-m-d') ?? '';
            $this->date_fin = $contrat->date_fin?->format('Y-m-d') ?? '';
            $this->taux_horaire = $contrat->taux_horaire !== null ? (string) $contrat->taux_horaire : '';
            $this->cycle_facturation = $contrat->cycle_facturation ?? '';
            $this->credits = $contrat->credits !== null ? (string) $contrat->credits : '';

            $this->reseaux = $contrat->reseaux->map(fn (ContratReseau $r) => [
                'id' => $r->id,
                'reseau' => $r->reseau,
                'identifiant' => $r->identifiant ?? '',
                'mot_de_passe' => $r->mot_de_passe ?? '',
                'gestion' => $r->gestion ?? '',
            ])->all();

            return;
        }

        // Création : libellé pré-rempli (ex. depuis l'autocomplétion d'une action).
        $this->libelle = trim((string) request()->query('libelle'));
    }

    public function addReseau(): void
    {
        $this->reseaux[] = [
            'id' => null,
            'reseau' => '',
            'identifiant' => '',
            'mot_de_passe' => '',
            'gestion' => '',
        ];
        $this->activeTab = 'reseaux';
    }

    public function removeReseau(int $index): void
    {
        unset($this->reseaux[$index]);
        $this->reseaux = array_values($this->reseaux);
    }

    protected function rules(): array
    {
        return [
            'libelle' => 'required|string|max:255',
            'client_id' => 'nullable|integer|exists:clients,id',
            'site_web' => 'nullable|string|max:255',
            'type' => ['required', 'in:'.implode(',', array_keys(Contrat::TYPES))],
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'taux_horaire' => 'required|numeric|min:0',
            'cycle_facturation' => ['required', 'in:'.implode(',', array_keys(Contrat::CYCLES))],
            'credits' => 'required|integer|min:0',
            'reseaux' => 'array',
            'reseaux.*.reseau' => ['required', 'in:'.implode(',', array_keys(ContratReseau::RESEAUX))],
            'reseaux.*.identifiant' => 'nullable|string|max:255',
            'reseaux.*.mot_de_passe' => 'nullable|string|max:255',
            'reseaux.*.gestion' => ['nullable', 'in:'.implode(',', array_keys(ContratReseau::GESTION))],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'libelle' => 'libellé',
            'type' => 'type de contrat',
            'cycle_facturation' => 'cycle de facturation',
            'taux_horaire' => 'taux horaire',
            'credits' => 'nombre de crédits',
            'reseaux.*.reseau' => 'réseau',
        ];
    }

    public function save()
    {
        $data = $this->validate();

        $attributes = [
            'libelle' => $data['libelle'],
            'client_id' => $data['client_id'] ?: null,
            'site_web' => $data['site_web'] ?: null,
            'type' => $data['type'] ?: null,
            'date_debut' => $data['date_debut'] ?: null,
            'date_fin' => $data['date_fin'] ?: null,
            'taux_horaire' => $data['taux_horaire'] !== '' ? $data['taux_horaire'] : null,
            'cycle_facturation' => $data['cycle_facturation'] ?: null,
            'credits' => $data['credits'] !== '' ? $data['credits'] : null,
        ];

        if ($this->editingId) {
            $contrat = Contrat::accessibleBy(auth()->user())->findOrFail($this->editingId);
            $contrat->update($attributes);
        } else {
            $contrat = Contrat::create($attributes);
        }

        $this->syncReseaux($contrat);

        return $this->redirectRoute('admin.contrats.show', $contrat, navigate: true);
    }

    /** Crée / met à jour / supprime les comptes réseaux selon le formulaire. */
    protected function syncReseaux(Contrat $contrat): void
    {
        $keep = [];

        foreach (array_values($this->reseaux) as $i => $row) {
            $model = $contrat->reseaux()->updateOrCreate(
                ['id' => $row['id'] ?? null],
                [
                    'reseau' => $row['reseau'],
                    'identifiant' => $row['identifiant'] ?: null,
                    'mot_de_passe' => $row['mot_de_passe'] ?: null,
                    'gestion' => $row['gestion'] ?: null,
                    'position' => $i,
                ],
            );
            $keep[] = $model->id;
        }

        $contrat->reseaux()->whereNotIn('id', $keep)->delete();
    }

    public function render()
    {
        return view('livewire.admin.contrats.form');
    }
}
