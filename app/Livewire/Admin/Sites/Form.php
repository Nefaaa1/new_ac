<?php

namespace App\Livewire\Admin\Sites;

use App\Models\Client;
use App\Models\Site;
use App\Models\SiteHebergement;
use App\Models\Statut;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Form extends Component
{
    public ?int $editingId = null;

    public string $activeTab = 'general';

    // Onglet Général
    public string $nom = '';
    public ?int $client_id = null;
    public bool $boutique_en_ligne = false;
    public ?int $statut_id = null;
    public string $date_statut = '';
    public string $mot_de_passe_complementaire = '';

    /** Onglet Hébergement (extension 1-1). */
    public array $hebergement = [
        'nom' => '', 'registrar' => '', 'identifiant' => '', 'mot_de_passe' => '',
        'periode_renouvellement' => '', 'paiement_agence' => false, 'client_visible' => false,
    ];

    /** Onglet FTP (extension 1-1). */
    public array $ftp = [
        'hote' => '', 'identifiant' => '', 'mot_de_passe' => '', 'client_visible' => false,
    ];

    /** Onglet Base de données (extension 1-1). */
    public array $bdd = [
        'lien' => '', 'serveur' => '', 'username' => '', 'mot_de_passe' => '', 'client_visible' => false,
    ];

    /** Onglet WordPress (extension 1-1). */
    public array $wordpress = [
        'lien_admin' => '', 'identifiant_admin' => '', 'mot_de_passe_admin' => '',
        'lien_client' => '', 'identifiant_client' => '', 'mot_de_passe_client' => '', 'client_visible' => false,
    ];

    public function mount(?Site $site = null): void
    {
        if ($site && $site->exists) {
            abort_unless(auth()->user()->canAccess($site), 403);

            $site->load('hebergement', 'ftp', 'bdd', 'wordpress');

            $this->editingId = $site->id;
            $this->nom = $site->nom;
            $this->client_id = $site->client_id;
            $this->boutique_en_ligne = $site->boutique_en_ligne;
            $this->statut_id = $site->statut_id;
            $this->date_statut = $site->date_statut?->format('Y-m-d') ?? '';
            $this->mot_de_passe_complementaire = $site->mot_de_passe_complementaire ?? '';

            if ($site->hebergement) {
                $this->hebergement = $site->hebergement->only(array_keys($this->hebergement));
            }
            if ($site->ftp) {
                $this->ftp = $site->ftp->only(array_keys($this->ftp));
            }
            if ($site->bdd) {
                $this->bdd = $site->bdd->only(array_keys($this->bdd));
            }
            if ($site->wordpress) {
                $this->wordpress = $site->wordpress->only(array_keys($this->wordpress));
            }
        }
    }

    /** Clients disponibles pour le rattachement (filtrés par accès). */
    #[Computed]
    public function clientsList()
    {
        return Client::query()
            ->with('user')
            ->whereHas('user', fn ($u) => $u->where('type', 'client')->accessibleBy(auth()->user()))
            ->get()
            ->sortBy(fn (Client $c) => $c->societe ?: $c->user?->name)
            ->values();
    }

    /** Statuts disponibles. */
    #[Computed]
    public function statutsList()
    {
        return Statut::orderBy('libelle')->get();
    }

    /** Le statut sélectionné impose-t-il une date ? */
    #[Computed]
    public function dateRequise(): bool
    {
        return (bool) $this->statutsList->firstWhere('id', $this->statut_id)?->requiert_date;
    }

    protected function rules(): array
    {
        return [
            'nom' => 'required|string|max:255',
            'client_id' => 'nullable|integer|exists:clients,id',
            'boutique_en_ligne' => 'boolean',
            'statut_id' => 'nullable|integer|exists:statuts,id',
            'date_statut' => [$this->dateRequise ? 'required' : 'nullable', 'date'],
            'mot_de_passe_complementaire' => 'nullable|string|max:5000',

            'hebergement.nom' => 'nullable|string|max:255',
            'hebergement.registrar' => 'nullable|string|max:255',
            'hebergement.identifiant' => 'nullable|string|max:255',
            'hebergement.mot_de_passe' => 'nullable|string|max:255',
            'hebergement.periode_renouvellement' => ['nullable', 'in:'.implode(',', array_keys(SiteHebergement::PERIODES))],
            'hebergement.paiement_agence' => 'boolean',
            'hebergement.client_visible' => 'boolean',

            'ftp.hote' => 'nullable|string|max:255',
            'ftp.identifiant' => 'nullable|string|max:255',
            'ftp.mot_de_passe' => 'nullable|string|max:255',
            'ftp.client_visible' => 'boolean',

            'bdd.lien' => 'nullable|string|max:255',
            'bdd.serveur' => 'nullable|string|max:255',
            'bdd.username' => 'nullable|string|max:255',
            'bdd.mot_de_passe' => 'nullable|string|max:255',
            'bdd.client_visible' => 'boolean',

            'wordpress.lien_admin' => 'nullable|string|max:255',
            'wordpress.identifiant_admin' => 'nullable|string|max:255',
            'wordpress.mot_de_passe_admin' => 'nullable|string|max:255',
            'wordpress.lien_client' => 'nullable|string|max:255',
            'wordpress.identifiant_client' => 'nullable|string|max:255',
            'wordpress.mot_de_passe_client' => 'nullable|string|max:255',
            'wordpress.client_visible' => 'boolean',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'nom' => 'nom du site',
            'date_statut' => 'date de statut',
        ];
    }

    public function save()
    {
        $data = $this->validate();

        $attributes = [
            'nom' => $data['nom'],
            'client_id' => $data['client_id'] ?: null,
            'boutique_en_ligne' => $data['boutique_en_ligne'],
            'statut_id' => $data['statut_id'] ?: null,
            'date_statut' => $data['date_statut'] ?: null,
            'mot_de_passe_complementaire' => $data['mot_de_passe_complementaire'] ?: null,
        ];

        if ($this->editingId) {
            $site = Site::accessibleBy(auth()->user())->findOrFail($this->editingId);
            $site->update($attributes);
        } else {
            $site = Site::create($attributes);
        }

        // Extensions 1-1 : toujours synchronisées (la ligne existe pour chaque site).
        $site->hebergement()->updateOrCreate([], $this->nullify($this->hebergement));
        $site->ftp()->updateOrCreate([], $this->nullify($this->ftp));
        $site->bdd()->updateOrCreate([], $this->nullify($this->bdd));
        $site->wordpress()->updateOrCreate([], $this->nullify($this->wordpress));

        return $this->redirectRoute('admin.sites.show', $site, navigate: true);
    }

    /** Convertit les chaînes vides en null (les booléens restent intacts). */
    protected function nullify(array $section): array
    {
        return array_map(fn ($v) => $v === '' ? null : $v, $section);
    }

    public function render()
    {
        return view('livewire.admin.sites.form');
    }
}
