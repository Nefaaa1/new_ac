<?php

namespace App\Livewire\Admin;

use App\Support\Navigation;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Favorites extends Component
{
    public ?int $editingId = null;

    #[Validate('required|string|max:50')]
    public string $editingLabel = '';

    /**
     * Rafraîchit la liste quand un favori est ajouté/retiré depuis la topbar.
     */
    #[On('favorites-updated')]
    public function refreshList(): void
    {
        // Le simple fait d'écouter l'événement déclenche un nouveau rendu.
    }

    public function edit(int $id): void
    {
        $favorite = auth()->user()->favorites()->findOrFail($id);

        $this->editingId = $favorite->id;
        $this->editingLabel = $favorite->label;
    }

    public function update(): void
    {
        $this->validate();

        auth()->user()->favorites()->whereKey($this->editingId)->update([
            'label' => $this->editingLabel,
        ]);

        $this->cancel();
    }

    public function cancel(): void
    {
        $this->reset('editingId', 'editingLabel');
        $this->resetValidation();
    }

    public function remove(int $id): void
    {
        auth()->user()->favorites()->whereKey($id)->delete();
    }

    public function render()
    {
        $favorites = auth()->user()->favorites()
            ->orderBy('position')
            ->orderBy('id')
            ->get()
            ->filter(fn ($favorite) => Route::has($favorite->route_name))
            ->map(function ($favorite) {
                $favorite->icon = Navigation::find($favorite->route_name)['icon'] ?? 'star';

                return $favorite;
            });

        return view('livewire.admin.favorites', ['favorites' => $favorites]);
    }
}
