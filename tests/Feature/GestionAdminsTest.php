<?php

namespace Tests\Feature;

use App\Livewire\Admin\Gestion\Admins;
use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GestionAdminsTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_admin_can_access_gestion(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.gestion.admins'))
            ->assertOk();
    }

    public function test_restricted_admin_is_forbidden(): void
    {
        $this->actingAs(User::factory()->restricted()->create())
            ->get(route('admin.gestion.admins'))
            ->assertForbidden();
    }

    public function test_client_is_forbidden(): void
    {
        $this->actingAs(User::factory()->create()) // type client par défaut
            ->get(route('admin.gestion.admins'))
            ->assertForbidden();
    }

    public function test_guest_is_redirected(): void
    {
        $this->get(route('admin.gestion.admins'))->assertRedirect(route('login'));
    }

    public function test_it_creates_an_admin_with_generated_password(): void
    {
        $component = Livewire::actingAs(User::factory()->admin()->create())
            ->test(Admins::class)
            ->call('create')
            ->set('prenom', 'Jean')
            ->set('nom', 'Dupont')
            ->set('login', 'jdupont')
            ->set('email', 'jean@dupont.fr')
            ->set('accessLevel', 'full')
            ->call('save')
            ->assertSet('showForm', false);

        $this->assertNotNull($component->get('generatedPassword'));
        $this->assertDatabaseHas('users', [
            'login' => 'jdupont',
            'type' => 'admin',
            'access_level' => 'full',
        ]);
    }

    public function test_restricted_admin_gets_client_grants(): void
    {
        $client = User::factory()->create(['nom' => 'Martin']);

        Livewire::actingAs(User::factory()->admin()->create())
            ->test(Admins::class)
            ->call('create')
            ->set('prenom', 'Amélie')
            ->set('nom', 'Roux')
            ->set('login', 'aroux')
            ->set('email', 'amelie@roux.fr')
            ->set('accessLevel', 'restricted')
            ->set('grantedClientIds', [$client->id])
            ->call('save');

        $admin = User::where('login', 'aroux')->firstOrFail();

        $this->assertDatabaseHas('access_grants', [
            'user_id' => $admin->id,
            'grantable_type' => User::class,
            'grantable_id' => $client->id,
        ]);
        $this->assertTrue($admin->canAccess($client));
    }

    public function test_switching_to_full_access_clears_grants(): void
    {
        $client = User::factory()->create();
        $admin = User::factory()->restricted()->create();
        $admin->accessGrants()->create(['grantable_type' => User::class, 'grantable_id' => $client->id]);

        Livewire::actingAs(User::factory()->admin()->create())
            ->test(Admins::class)
            ->call('editAdmin', $admin->id)
            ->set('accessLevel', 'full')
            ->call('save');

        $this->assertDatabaseCount('access_grants', 0);
    }

    public function test_login_uniqueness_is_enforced(): void
    {
        User::factory()->admin()->create(['login' => 'taken']);

        Livewire::actingAs(User::factory()->admin()->create())
            ->test(Admins::class)
            ->call('create')
            ->set('prenom', 'X')
            ->set('nom', 'Y')
            ->set('login', 'taken')
            ->set('email', 'x@y.fr')
            ->call('save')
            ->assertHasErrors('login');
    }

    public function test_admin_cannot_suspend_themselves(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(Admins::class)
            ->call('toggleSuspend', $admin->id);

        $this->assertNull($admin->fresh()->suspended_at);
    }

    public function test_super_admin_cannot_be_suspended_or_deleted(): void
    {
        $super = User::factory()->admin()->create(['login' => User::SUPER_ADMIN_LOGIN]);

        $component = Livewire::actingAs(User::factory()->admin()->create())->test(Admins::class);

        $component->call('toggleSuspend', $super->id);
        $this->assertNull($super->fresh()->suspended_at);

        $component->call('deleteAdmin', $super->id);
        $this->assertNotSoftDeleted('users', ['id' => $super->id]);
    }

    public function test_it_suspends_reactivates_and_deletes_an_admin(): void
    {
        $other = User::factory()->admin()->create();

        $component = Livewire::actingAs(User::factory()->admin()->create())->test(Admins::class);

        $component->call('toggleSuspend', $other->id);
        $this->assertNotNull($other->fresh()->suspended_at);

        $component->call('toggleSuspend', $other->id);
        $this->assertNull($other->fresh()->suspended_at);

        $component->call('deleteAdmin', $other->id);
        $this->assertSoftDeleted('users', ['id' => $other->id]);
    }

    public function test_suspended_user_cannot_login(): void
    {
        User::factory()->admin()->suspended()->create(['login' => 'sus']);

        Livewire::test(Login::class)
            ->set('login', 'sus')
            ->set('password', 'password')
            ->call('login_request')
            ->assertHasErrors('login')
            ->assertSet('success', false);

        $this->assertGuest();
    }

    public function test_suspension_kicks_an_active_session(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();

        $admin->update(['suspended_at' => now()]);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
