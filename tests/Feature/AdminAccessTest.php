<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public static function adminRoutes(): array
    {
        return [
            ['admin.dashboard'],
            ['admin.sites'],
            ['admin.contrats'],
            ['admin.clients'],
            ['admin.actions'],
            ['admin.tickets'],
            ['admin.chatbots'],
            ['admin.profil'],
            ['admin.recap.actions'],
            ['admin.recap.tickets'],
        ];
    }

    #[DataProvider('adminRoutes')]
    public function test_admin_can_access_admin_pages(string $route): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route($route))->assertOk();
    }

    public function test_client_cannot_access_admin_pages(): void
    {
        $client = User::factory()->create(); // type client par défaut

        $this->actingAs($client)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }
}
