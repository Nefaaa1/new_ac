<?php

namespace Tests\Feature;

use App\Livewire\Admin\Contrats;
use App\Livewire\Admin\Contrats\Form;
use App\Livewire\Admin\Contrats\Show;
use App\Models\Contrat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContratsCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_contrat_with_social_networks(): void
    {
        $client = User::factory()->create(['nom' => 'Martin']);
        $clientFiche = $client->client()->create(['societe' => 'Martin SARL']);

        Livewire::actingAs(User::factory()->admin()->create())
            ->test(Form::class)
            ->set('libelle', 'Community management')
            ->set('client_id', $clientFiche->id)
            ->set('type', 'fixe')
            ->set('cycle_facturation', 'mensuel')
            ->set('taux_horaire', '75')
            ->set('credits', '10')
            ->set('date_debut', '2026-01-01')
            ->call('addReseau')
            ->set('reseaux.0.reseau', 'facebook')
            ->set('reseaux.0.identifiant', 'martin.fb')
            ->set('reseaux.0.mot_de_passe', 'secret123')
            ->set('reseaux.0.gestion', 'agence')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('contrats', [
            'libelle' => 'Community management',
            'client_id' => $clientFiche->id,
            'type' => 'fixe',
            'cycle_facturation' => 'mensuel',
            'credits' => 10,
        ]);

        $contrat = Contrat::firstOrFail();
        $this->assertDatabaseHas('contrat_reseaux', [
            'contrat_id' => $contrat->id,
            'reseau' => 'facebook',
            'identifiant' => 'martin.fb',
            'gestion' => 'agence',
        ]);

        // Le mot de passe est chiffré : la valeur en clair ne doit pas être en base.
        $reseau = $contrat->reseaux()->first();
        $this->assertSame('secret123', $reseau->mot_de_passe);
        $this->assertNotSame('secret123', $reseau->getRawOriginal('mot_de_passe'));
    }

    public function test_libelle_is_required(): void
    {
        Livewire::actingAs(User::factory()->admin()->create())
            ->test(Form::class)
            ->set('libelle', '')
            ->call('save')
            ->assertHasErrors(['libelle' => 'required']);
    }

    public function test_reseau_type_is_required_when_a_row_exists(): void
    {
        Livewire::actingAs(User::factory()->admin()->create())
            ->test(Form::class)
            ->set('libelle', 'Test')
            ->call('addReseau')
            ->call('save')
            ->assertHasErrors(['reseaux.0.reseau']);
    }

    public function test_date_fin_must_be_after_date_debut(): void
    {
        Livewire::actingAs(User::factory()->admin()->create())
            ->test(Form::class)
            ->set('libelle', 'Test')
            ->set('date_debut', '2026-06-01')
            ->set('date_fin', '2026-05-01')
            ->call('save')
            ->assertHasErrors(['date_fin']);
    }

    public function test_it_updates_a_contrat_and_syncs_networks(): void
    {
        $contrat = Contrat::factory()->create(['libelle' => 'Ancien']);
        $contrat->reseaux()->create(['reseau' => 'instagram', 'identifiant' => 'old', 'position' => 0]);

        $component = Livewire::actingAs(User::factory()->admin()->create())
            ->test(Form::class, ['contrat' => $contrat])
            ->assertSet('libelle', 'Ancien')
            ->set('libelle', 'Nouveau')
            ->call('removeReseau', 0)
            ->call('addReseau')
            ->set('reseaux.0.reseau', 'linkedin')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('contrats', ['id' => $contrat->id, 'libelle' => 'Nouveau']);
        $this->assertDatabaseMissing('contrat_reseaux', ['contrat_id' => $contrat->id, 'reseau' => 'instagram']);
        $this->assertDatabaseHas('contrat_reseaux', ['contrat_id' => $contrat->id, 'reseau' => 'linkedin']);
        $this->assertSame(1, $contrat->reseaux()->count());
    }

    public function test_it_deletes_a_contrat_from_the_show_page(): void
    {
        $contrat = Contrat::factory()->create();

        Livewire::actingAs(User::factory()->admin()->create())
            ->test(Show::class, ['contrat' => $contrat])
            ->call('deleteContrat');

        $this->assertSoftDeleted('contrats', ['id' => $contrat->id]);
    }

    public function test_search_filters_by_libelle(): void
    {
        Contrat::factory()->create(['libelle' => 'Alpha Project']);
        Contrat::factory()->create(['libelle' => 'Beta Project']);

        Livewire::actingAs(User::factory()->admin()->create())
            ->test(Contrats::class)
            ->set('search', 'Alpha')
            ->assertSee('Alpha Project')
            ->assertDontSee('Beta Project');
    }

    public function test_global_search_finds_contrats(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Contrat::factory()->create(['libelle' => 'Zorglub Contract']);

        $result = \App\Support\Search\Search::query('Zorglub');

        $this->assertSame(1, $result['total']);
        $this->assertSame('Contrats', $result['groups']['Contrats'][0]->group);
    }

    public function test_restricted_admin_cannot_view_ungranted_contrat(): void
    {
        $contrat = Contrat::factory()->create();
        $admin = User::factory()->restricted()->create();

        Livewire::actingAs($admin)
            ->test(Show::class, ['contrat' => $contrat])
            ->assertStatus(403);
    }

    public function test_sorting_toggles_and_orders_by_libelle(): void
    {
        Contrat::factory()->create(['libelle' => 'Zeta']);
        Contrat::factory()->create(['libelle' => 'Alpha']);

        $component = Livewire::actingAs(User::factory()->admin()->create())
            ->test(Contrats::class)
            ->call('sortBy', 'libelle')
            ->assertSet('sortField', 'libelle')
            ->assertSet('sortDirection', 'asc')
            ->assertSeeInOrder(['Alpha', 'Zeta']);

        $component->call('sortBy', 'libelle')
            ->assertSet('sortDirection', 'desc')
            ->assertSeeInOrder(['Zeta', 'Alpha']);
    }
}
