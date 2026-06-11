<?php

namespace App\Livewire\Admin;

use App\Support\Navigation;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Barre de favoris du dashboard (chips compactes).
 * Le nommage se fait à la création via le popover de FavoriteToggle (topbar).
 */
class Favorites extends Component
{
    /**
     * Rafraîchit la liste quand un favori est ajouté/retiré depuis la topbar.
     */
    #[On('favorites-updated')]
    public function refreshList(): void
    {
        // Le simple fait d'écouter l'événement déclenche un nouveau rendu.
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
