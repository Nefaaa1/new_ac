<?php

namespace App\Livewire\Admin;

use App\Models\Equipe;
use App\Models\Site;
use App\Models\Ticket;
use App\Models\TicketStatut;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Création express d'un ticket depuis le dashboard (slide-over).
 * Version minimale du formulaire Tickets : demande + site + date + importance + attribution.
 * Statut posé automatiquement sur le 1er du workflow (« À faire »).
 */
class QuickTicket extends Component
{
    public bool $show = false;

    /** Remonte le <livewire:site-picker> à chaque ouverture (le panneau reste dans le DOM). */
    public int $formNonce = 0;

    public string $demande = '';
    public string $descriptif = '';
    public ?int $site_id = null;
    public string $date = '';
    public string $importance = 'moyenne';

    /** Attribution (obligatoire) : 'u:<id>' admin | 'e:<id>' équipe. Défaut = moi. */
    public string $assigne = '';

    #[On('open-quick-ticket')]
    public function open(): void
    {
        $this->reset(['demande', 'descriptif', 'site_id']);
        $this->date = now()->format('Y-m-d');
        $this->importance = 'moyenne';
        $this->assigne = 'u:'.auth()->id();
        $this->formNonce++;
        $this->resetValidation();
        $this->show = true;
    }

    /** Admins non suspendus (attribution). */
    #[Computed]
    public function adminsList()
    {
        return User::where('type', 'admin')
            ->whereNull('suspended_at')
            ->orderBy('nom')->orderBy('prenom')
            ->get();
    }

    /** Équipes (attribution). */
    #[Computed]
    public function equipesList()
    {
        return Equipe::withCount('members')->orderBy('nom')->get();
    }

    public function save(): void
    {
        $validAssignees = $this->adminsList->map(fn ($a) => 'u:'.$a->id)
            ->merge($this->equipesList->map(fn ($e) => 'e:'.$e->id))
            ->all();

        $data = $this->validate([
            'demande' => 'required|string|max:255',
            'descriptif' => 'nullable|string|max:5000',
            'site_id' => ['required', 'integer', Rule::exists('sites', 'id')],
            'date' => 'required|date',
            'importance' => ['required', 'in:'.implode(',', array_keys(Ticket::IMPORTANCES))],
            'assigne' => ['required', Rule::in($validAssignees)],
        ]);

        // Le site doit être accessible à l'admin connecté.
        Site::withTrashed()->accessibleBy(auth()->user())->findOrFail($data['site_id']);

        [$type, $assigneId] = explode(':', $data['assigne']);

        Ticket::create([
            'demande' => $data['demande'],
            'descriptif' => $data['descriptif'] ?: null,
            'site_id' => $data['site_id'],
            'date' => $data['date'],
            'statut_id' => TicketStatut::orderBy('position')->orderBy('libelle')->first()?->id,
            'importance' => $data['importance'],
            'utilisateur_id' => $type === 'u' ? (int) $assigneId : null,
            'equipe_id' => $type === 'e' ? (int) $assigneId : null,
            'createur_id' => auth()->id(),
        ]);

        $this->show = false;
        $this->dispatch('ticket-saved', demande: $data['demande']);
    }

    public function close(): void
    {
        $this->show = false;
        $this->resetValidation();
    }

    protected function validationAttributes(): array
    {
        return [
            'demande' => 'demande',
            'site_id' => 'site',
            'assigne' => 'attribution',
        ];
    }

    public function render()
    {
        return view('livewire.admin.quick-ticket');
    }
}
