<?php

namespace Tests\Feature;

use App\Livewire\Admin\ContratPicker;
use App\Models\Contrat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContratPickerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_prefills_the_label_for_an_existing_selection(): void
    {
        $contrat = Contrat::factory()->create(['libelle' => 'Déjà choisi']);

        Livewire::actingAs(User::factory()->admin()->create())
            ->test(ContratPicker::class, ['contratId' => $contrat->id])
            ->assertSet('contratId', $contrat->id)
            ->assertSet('search', 'Déjà choisi');
    }

    public function test_results_are_filtered_by_search(): void
    {
        Contrat::factory()->create(['libelle' => 'Alpha site']);
        Contrat::factory()->create(['libelle' => 'Beta site']);

        Livewire::actingAs(User::factory()->admin()->create())
            ->test(ContratPicker::class)
            ->set('search', 'Alpha')
            ->assertSee('Alpha site')
            ->assertDontSee('Beta site');
    }

    public function test_nothing_searched_below_two_characters(): void
    {
        Contrat::factory()->create(['libelle' => 'Alpha site']);

        Livewire::actingAs(User::factory()->admin()->create())
            ->test(ContratPicker::class)
            ->set('search', 'A')
            ->assertDontSee('Alpha site');
    }

    public function test_selecting_sets_the_bound_id_and_label(): void
    {
        $contrat = Contrat::factory()->create(['libelle' => 'Mon contrat']);

        Livewire::actingAs(User::factory()->admin()->create())
            ->test(ContratPicker::class)
            ->call('selectContrat', $contrat->id)
            ->assertSet('contratId', $contrat->id)
            ->assertSet('search', 'Mon contrat')
            ->assertSet('showResults', false);
    }

    public function test_typing_clears_previous_selection(): void
    {
        $contrat = Contrat::factory()->create();

        Livewire::actingAs(User::factory()->admin()->create())
            ->test(ContratPicker::class)
            ->call('selectContrat', $contrat->id)
            ->assertSet('contratId', $contrat->id)
            ->set('search', 'autre chose')
            ->assertSet('contratId', null);
    }

    public function test_create_contrat_redirects_with_prefilled_libelle(): void
    {
        Livewire::actingAs(User::factory()->admin()->create())
            ->test(ContratPicker::class)
            ->set('search', 'Nouveau contrat')
            ->call('createContrat')
            ->assertRedirect(route('admin.contrats.create', ['libelle' => 'Nouveau contrat']));
    }

    public function test_restricted_admin_only_finds_accessible_contrats(): void
    {
        $granted = Contrat::factory()->create(['libelle' => 'Visible AAA']);
        Contrat::factory()->create(['libelle' => 'Visible BBB']);

        $admin = User::factory()->restricted()->create();
        $admin->accessGrants()->create(['grantable_type' => Contrat::class, 'grantable_id' => $granted->id]);

        Livewire::actingAs($admin)
            ->test(ContratPicker::class)
            ->set('search', 'Visible')
            ->assertSee('Visible AAA')
            ->assertDontSee('Visible BBB');
    }
}
