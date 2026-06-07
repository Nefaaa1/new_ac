<?php

namespace App\Livewire\Admin;

use App\Support\Navigation;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;

class FavoriteToggle extends Component
{
    public string $route = '';

    #[Validate('required|string|max:50')]
    public string $label = '';

    public function mount(string $route): void
    {
        $this->route = $route;
        $this->label = Navigation::find($route)['label'] ?? '';
    }

    /**
     * Le favori existant pour la page courante (ou null).
     */
    #[Computed]
    public function favorite()
    {
        return auth()->user()->favorites()->where('route_name', $this->route)->first();
    }

    public function add(): void
    {
        $this->validate();

        $position = (int) auth()->user()->favorites()->max('position') + 1;

        auth()->user()->favorites()->updateOrCreate(
            ['route_name' => $this->route],
            ['label' => $this->label, 'position' => $position],
        );

        unset($this->favorite);
        $this->dispatch('favorites-updated');
    }

    public function remove(): void
    {
        auth()->user()->favorites()->where('route_name', $this->route)->delete();

        unset($this->favorite);
        $this->dispatch('favorites-updated');
    }

    public function render()
    {
        return view('livewire.admin.favorite-toggle');
    }
}
