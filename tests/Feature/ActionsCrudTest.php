<?php

namespace Tests\Feature;

use App\Livewire\Admin\Actions;
use App\Models\Action;
use App\Models\Contrat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ActionsCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_action(): void
    {
        $contrat = Contrat::factory()->create();

        Livewire::actingAs(User::factory()->admin()->create())
            ->test(Actions::class)
            ->call('create')
            ->set('intitule', 'Mise à jour page accueil')
            ->set('temps', '2.5')
            ->set('date', '2026-06-01')
            ->set('type', 'site_web')
            ->set('contrat_id', $contrat->id)
            ->set('commentaire', 'RAS')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showForm', false);

        $this->assertDatabaseHas('actions', [
            'intitule' => 'Mise à jour page accueil',
            'temps' => '2.50',
            'type' => 'site_web',
            'contrat_id' => $contrat->id,
            'commentaire' => 'RAS',
        ]);
    }

    public function test_required_fields_are_enforced(): void
    {
        Livewire::actingAs(User::factory()->admin()->create())
            ->test(Actions::class)
            ->call('create')
            ->set('intitule', '')
            ->set('temps', '')
            ->set('date', '')
            ->set('type', '')
            ->set('contrat_id', null)
            ->call('save')
            ->assertHasErrors([
                'intitule' => 'required',
                'temps' => 'required',
                'date' => 'required',
                'type' => 'required',
                'contrat_id' => 'required',
            ]);
    }

    public function test_it_updates_an_action(): void
    {
        $action = Action::factory()->create(['intitule' => 'Avant']);

        Livewire::actingAs(User::factory()->admin()->create())
            ->test(Actions::class)
            ->call('editAction', $action->id)
            ->assertSet('intitule', 'Avant')
            ->set('intitule', 'Après')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('actions', ['id' => $action->id, 'intitule' => 'Après']);
    }

    public function test_it_soft_deletes_an_action(): void
    {
        $action = Action::factory()->create();

        Livewire::actingAs(User::factory()->admin()->create())
            ->test(Actions::class)
            ->call('deleteAction', $action->id);

        $this->assertSoftDeleted('actions', ['id' => $action->id]);
    }

    public function test_search_filters_by_intitule(): void
    {
        Action::factory()->create(['intitule' => 'Refonte logo']);
        Action::factory()->create(['intitule' => 'Article blog']);

        Livewire::actingAs(User::factory()->admin()->create())
            ->test(Actions::class)
            ->set('search', 'logo')
            ->assertSee('Refonte logo')
            ->assertDontSee('Article blog');
    }

    public function test_restricted_admin_only_sees_actions_of_accessible_contrats(): void
    {
        $granted = Contrat::factory()->create();
        $hidden = Contrat::factory()->create();
        Action::factory()->create(['contrat_id' => $granted->id, 'intitule' => 'Action visible']);
        Action::factory()->create(['contrat_id' => $hidden->id, 'intitule' => 'Action cachee']);

        $admin = User::factory()->restricted()->create();
        $admin->accessGrants()->create(['grantable_type' => Contrat::class, 'grantable_id' => $granted->id]);

        Livewire::actingAs($admin)
            ->test(Actions::class)
            ->assertSee('Action visible')
            ->assertDontSee('Action cachee');
    }

    public function test_actions_of_a_soft_deleted_contrat_still_appear_with_alert(): void
    {
        $contrat = Contrat::factory()->create();
        Action::factory()->create(['contrat_id' => $contrat->id, 'intitule' => 'Action orpheline']);
        $contrat->delete(); // soft delete

        Livewire::actingAs(User::factory()->admin()->create())
            ->test(Actions::class)
            ->assertSee('Action orpheline')
            ->assertSee('Contrat supprimé');
    }

    public function test_orphaned_action_can_still_be_edited_and_deleted(): void
    {
        $contrat = Contrat::factory()->create();
        $action = Action::factory()->create(['contrat_id' => $contrat->id]);
        $contrat->delete();

        $component = Livewire::actingAs(User::factory()->admin()->create())
            ->test(Actions::class)
            ->call('editAction', $action->id)
            ->assertSet('contratTrashed', true)
            ->set('intitule', 'Mise à jour orpheline')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('actions', ['id' => $action->id, 'intitule' => 'Mise à jour orpheline']);

        $component->call('deleteAction', $action->id);
        $this->assertSoftDeleted('actions', ['id' => $action->id]);
    }

    public function test_restricted_admin_still_sees_orphaned_action_of_granted_contrat(): void
    {
        $contrat = Contrat::factory()->create();
        Action::factory()->create(['contrat_id' => $contrat->id, 'intitule' => 'Visible orpheline']);

        $admin = User::factory()->restricted()->create();
        $admin->accessGrants()->create(['grantable_type' => Contrat::class, 'grantable_id' => $contrat->id]);
        $contrat->delete();

        Livewire::actingAs($admin)
            ->test(Actions::class)
            ->assertSee('Visible orpheline');
    }

    public function test_sorting_toggles_and_orders_by_intitule(): void
    {
        Action::factory()->create(['intitule' => 'Zeta']);
        Action::factory()->create(['intitule' => 'Alpha']);

        $component = Livewire::actingAs(User::factory()->admin()->create())
            ->test(Actions::class)
            ->call('sortBy', 'intitule')
            ->assertSet('sortDirection', 'asc')
            ->assertSeeInOrder(['Alpha', 'Zeta']);

        $component->call('sortBy', 'intitule')
            ->assertSet('sortDirection', 'desc')
            ->assertSeeInOrder(['Zeta', 'Alpha']);
    }
}
