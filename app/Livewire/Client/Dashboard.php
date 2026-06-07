<?php

namespace App\Livewire\Client;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.panel')]
class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.client.dashboard');
    }
}
