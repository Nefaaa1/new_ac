<?php

namespace Tests\Feature;

use App\Livewire\Admin\Clients;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ClientsCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_client_with_profile_and_password(): void
    {
        $component = Livewire::actingAs(User::factory()->admin()->create())
            ->test(Clients::class)
            ->call('create')
            ->set('civilite', 'Mme')
            ->set('prenom', 'Claire')
            ->set('nom', 'Durand')
            ->set('login', 'cdurand')
            ->set('email', 'claire@durand.fr')
            ->set('societe', 'Durand SARL')
            ->set('lienapp', 'https://app.durand.fr')
            ->set('email3', 'contact@durand.fr')
            ->call('save')
            ->assertSet('showForm', false);

        $this->assertNotNull($component->get('generatedPassword'));

        $this->assertDatabaseHas('users', [
            'login' => 'cdurand',
            'type' => 'client',
            'civilite' => 'Mme',
        ]);

        $user = User::where('login', 'cdurand')->firstOrFail();
        $this->assertDatabaseHas('clients', [
            'user_id' => $user->id,
            'societe' => 'Durand SARL',
            'lienapp' => 'https://app.durand.fr',
            'email3' => 'contact@durand.fr',
        ]);
    }

    public function test_it_updates_client_and_profile(): void
    {
        $client = User::factory()->create(['nom' => 'Ancien']);
        $client->client()->create(['societe' => 'Old Corp']);

        Livewire::actingAs(User::factory()->admin()->create())
            ->test(Clients::class)
            ->call('editClient', $client->id)
            ->set('nom', 'Nouveau')
            ->set('societe', 'New Corp')
            ->call('save');

        $this->assertDatabaseHas('users', ['id' => $client->id, 'nom' => 'Nouveau']);
        $this->assertDatabaseHas('clients', ['user_id' => $client->id, 'societe' => 'New Corp']);
        $this->assertDatabaseCount('clients', 1); // updateOrCreate, pas de doublon
    }

    public function test_email3_must_be_a_valid_email(): void
    {
        Livewire::actingAs(User::factory()->admin()->create())
            ->test(Clients::class)
            ->call('create')
            ->set('civilite', 'M')
            ->set('prenom', 'X')
            ->set('nom', 'Y')
            ->set('login', 'xy')
            ->set('email', 'x@y.fr')
            ->set('email3', 'pas-un-email')
            ->call('save')
            ->assertHasErrors('email3');
    }

    public function test_it_soft_deletes_a_client(): void
    {
        $client = User::factory()->create();

        Livewire::actingAs(User::factory()->admin()->create())
            ->test(Clients::class)
            ->call('deleteClient', $client->id);

        $this->assertSoftDeleted('users', ['id' => $client->id]);
    }

    public function test_restricted_admin_only_sees_granted_clients(): void
    {
        $granted = User::factory()->create(['prenom' => 'Cli', 'nom' => 'Visible']);
        $hidden = User::factory()->create(['prenom' => 'Cli', 'nom' => 'Cachee']);

        $admin = User::factory()->restricted()->create();
        $admin->accessGrants()->create(['grantable_type' => User::class, 'grantable_id' => $granted->id]);

        Livewire::actingAs($admin)
            ->test(Clients::class)
            ->assertSee('Visible')
            ->assertDontSee('Cachee');
    }

    public function test_global_search_finds_clients(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $c = User::factory()->create(['nom' => 'Zorglub']);
        $c->client()->create(['societe' => 'Zorg Inc']);

        $result = \App\Support\Search\Search::query('Zorg');

        $this->assertSame(1, $result['total']);
        $this->assertSame('Clients', $result['groups']['Clients'][0]->group);
    }

    public function test_open_query_param_opens_the_client(): void
    {
        $client = User::factory()->create(['nom' => 'Ouvert']);
        $client->client()->create(['societe' => 'OpenCorp']);

        Livewire::actingAs(User::factory()->admin()->create())
            ->withQueryParams(['open' => $client->id])
            ->test(Clients::class)
            ->assertSet('showForm', true)
            ->assertSet('editingId', $client->id)
            ->assertSet('nom', 'Ouvert')
            ->assertSet('societe', 'OpenCorp');
    }

    public function test_sorting_toggles_and_orders(): void
    {
        $z = User::factory()->create(['nom' => 'Zoulou', 'prenom' => 'A']);
        $z->client()->create(['societe' => 'Foo']);
        $y = User::factory()->create(['nom' => 'Yankee', 'prenom' => 'B']);
        $y->client()->create(['societe' => 'Bar']);

        $component = Livewire::actingAs(User::factory()->admin()->create())
            ->test(Clients::class)
            ->call('sortBy', 'nom')
            ->assertSet('sortField', 'nom')
            ->assertSet('sortDirection', 'asc')
            ->assertSeeInOrder(['Yankee', 'Zoulou']); // nom croissant

        $component->call('sortBy', 'nom')
            ->assertSet('sortDirection', 'desc')
            ->assertSeeInOrder(['Zoulou', 'Yankee']); // sens inverse
    }

    public function test_search_filters_by_societe(): void
    {
        $a = User::factory()->create(['prenom' => 'A', 'nom' => 'Alpha']);
        $a->client()->create(['societe' => 'Acme']);
        $b = User::factory()->create(['prenom' => 'B', 'nom' => 'Beta']);
        $b->client()->create(['societe' => 'Globex']);

        Livewire::actingAs(User::factory()->admin()->create())
            ->test(Clients::class)
            ->set('search', 'Acme')
            ->assertSee('Acme')
            ->assertDontSee('Globex');
    }

    public function test_full_admin_sees_all_clients(): void
    {
        User::factory()->create(['prenom' => 'Cli', 'nom' => 'Premier']);
        User::factory()->create(['prenom' => 'Cli', 'nom' => 'Second']);

        Livewire::actingAs(User::factory()->admin()->create())
            ->test(Clients::class)
            ->assertSee('Premier')
            ->assertSee('Second');
    }
}
