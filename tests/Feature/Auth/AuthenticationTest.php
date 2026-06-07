<?php

namespace Tests\Feature\Auth;

use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $this->get('/login')->assertStatus(200);
    }

    public function test_users_can_authenticate(): void
    {
        User::factory()->create(['login' => 'jdoe']);

        Livewire::test(Login::class)
            ->set('login', 'jdoe')
            ->set('password', 'password')
            ->call('login_request')
            ->assertSet('success', true)
            ->assertSet('redirectTo', route('client.dashboard'));

        $this->assertAuthenticated();
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        User::factory()->create(['login' => 'jdoe']);

        Livewire::test(Login::class)
            ->set('login', 'jdoe')
            ->set('password', 'wrong-password')
            ->call('login_request')
            ->assertHasErrors('login');

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
