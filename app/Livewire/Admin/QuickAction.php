<?php

namespace App\Livewire\Admin;

use App\Models\Action;
use App\Models\Contrat;
use Livewire\Component;

/**
 * Saisie express d'une action (temps) depuis le dashboard.
 * Le geste le plus fréquent au quotidien : intitulé + contrat + temps, et c'est enregistré.
 */
class QuickAction extends Component
{
    public string $intitule = '';
    public ?int $contrat_id = null;
    public string $temps = '';
    public string $type = '';
    public string $date = '';

    /** Remonte le <livewire:contrat-picker> après un enregistrement. */
    public int $pickerNonce = 0;

    public function mount(): void
    {
        $this->date = now()->format('Y-m-d');
    }

    public function save(): void
    {
        $data = $this->validate([
            'intitule' => 'required|string|max:255',
            'contrat_id' => ['required', 'integer'],
            'temps' => 'required|numeric|min:0',
            'type' => ['required', 'in:'.implode(',', array_keys(Action::TYPES))],
            'date' => 'required|date',
        ]);

        // Le contrat doit être accessible à l'admin connecté.
        $contrat = Contrat::accessibleBy(auth()->user())->findOrFail($data['contrat_id']);

        Action::create($data + ['createur_id' => auth()->id()]);

        // Reset (la date est conservée : on enchaîne les saisies d'une même journée),
        // puis on remonte le picker (il garde sinon l'état précédent).
        $this->reset(['intitule', 'contrat_id', 'temps', 'type']);
        $this->pickerNonce++;
        $this->resetValidation();

        // Le dashboard affiche le toast et rafraîchit compteurs / crédits.
        $this->dispatch('action-saved', contrat: $contrat->libelle);
    }

    protected function validationAttributes(): array
    {
        return [
            'contrat_id' => 'contrat',
            'temps' => 'temps',
        ];
    }

    public function render()
    {
        return view('livewire.admin.quick-action');
    }
}
