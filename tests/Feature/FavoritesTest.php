<?php

namespace Tests\Feature;

use App\Livewire\Admin\FavoriteToggle;
use App\Livewire\Admin\Favorites;
use App\Models\Favorite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FavoritesTest extends TestCase
{
    use RefreshDatabase;

    public function test_toggle_prefills_label_from_navigation(): void
    {
        $user = User::factory()->admin()->create();

        Livewire::actingAs($user)
            ->test(FavoriteToggle::class, ['route' => 'admin.sites'])
            ->assertSet('label', 'Sites');
    }

    public function test_it_adds_a_favorite(): void
    {
        $user = User::factory()->admin()->create();

        Livewire::actingAs($user)
            ->test(FavoriteToggle::class, ['route' => 'admin.sites'])
            ->set('label', 'Mes sites')
            ->call('add');

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'route_name' => 'admin.sites',
            'label' => 'Mes sites',
            'position' => 1,
        ]);
    }

    public function test_label_is_required_to_add(): void
    {
        $user = User::factory()->admin()->create();

        Livewire::actingAs($user)
            ->test(FavoriteToggle::class, ['route' => 'admin.sites'])
            ->set('label', '')
            ->call('add')
            ->assertHasErrors(['label' => 'required']);

        $this->assertDatabaseCount('favorites', 0);
    }

    public function test_it_does_not_duplicate_a_favorite(): void
    {
        $user = User::factory()->admin()->create();

        Livewire::actingAs($user)
            ->test(FavoriteToggle::class, ['route' => 'admin.sites'])
            ->set('label', 'A')
            ->call('add')
            ->set('label', 'B')
            ->call('add');

        $this->assertSame(1, Favorite::where('route_name', 'admin.sites')->count());
    }

    public function test_it_removes_a_favorite(): void
    {
        $user = User::factory()->admin()->create();
        $user->favorites()->create(['label' => 'X', 'route_name' => 'admin.sites', 'position' => 1]);

        Livewire::actingAs($user)
            ->test(FavoriteToggle::class, ['route' => 'admin.sites'])
            ->call('remove');

        $this->assertDatabaseCount('favorites', 0);
    }

    public function test_list_renames_and_deletes(): void
    {
        $user = User::factory()->admin()->create();
        $fav = $user->favorites()->create(['label' => 'Old', 'route_name' => 'admin.sites', 'position' => 1]);

        Livewire::actingAs($user)
            ->test(Favorites::class)
            ->call('edit', $fav->id)
            ->set('editingLabel', 'New')
            ->call('update');

        $this->assertDatabaseHas('favorites', ['id' => $fav->id, 'label' => 'New']);

        Livewire::actingAs($user)
            ->test(Favorites::class)
            ->call('remove', $fav->id);

        $this->assertDatabaseCount('favorites', 0);
    }

    public function test_list_ignores_unknown_routes(): void
    {
        $user = User::factory()->admin()->create();
        $user->favorites()->create(['label' => 'Ghost', 'route_name' => 'admin.removed', 'position' => 1]);

        Livewire::actingAs($user)
            ->test(Favorites::class)
            ->assertDontSee('Ghost');
    }
}
