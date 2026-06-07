<?php

namespace App\Livewire\Admin\Contrats;

use App\Models\Contrat;
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
