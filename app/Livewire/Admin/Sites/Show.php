<?php

namespace App\Livewire\Admin\Sites;

use App\Models\Site;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Show extends Component
{
    public Site $site;

    public function mount(Site $site): void
    {
        abort_unless(auth()->user()->canAccess($site), 403);

        $this->site = $site->load('client.user', 'statut', 'hebergement', 'ftp', 'bdd', 'wordpress');
    }

    public function deleteSite()
    {
        abort_unless(auth()->user()->canAccess($this->site), 403);

        $this->site->delete();

        return $this->redirectRoute('admin.sites', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.sites.show');
    }
}
