<?php

namespace Tests\Feature;

use App\Livewire\Admin\Notepad;
use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NotepadTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_loads_the_existing_note(): void
    {
        $user = User::factory()->admin()->create();
        Note::create(['user_id' => $user->id, 'content' => 'Ne pas oublier']);

        Livewire::actingAs($user)
            ->test(Notepad::class)
            ->assertSet('content', 'Ne pas oublier');
    }

    public function test_it_auto_saves_on_change(): void
    {
        $user = User::factory()->admin()->create();

        Livewire::actingAs($user)
            ->test(Notepad::class)
            ->set('content', 'Acheter du café');

        $this->assertDatabaseHas('notes', [
            'user_id' => $user->id,
            'content' => 'Acheter du café',
        ]);
    }

    public function test_it_keeps_a_single_note_per_user(): void
    {
        $user = User::factory()->admin()->create();

        Livewire::actingAs($user)
            ->test(Notepad::class)
            ->set('content', 'v1')
            ->set('content', 'v2');

        $this->assertSame(1, Note::where('user_id', $user->id)->count());
        $this->assertDatabaseHas('notes', ['user_id' => $user->id, 'content' => 'v2']);
    }
}
