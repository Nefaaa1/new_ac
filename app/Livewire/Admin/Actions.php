<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Actions extends Component
{
    public function render()
    {
        return view('livewire.admin.actions');
    }
}
