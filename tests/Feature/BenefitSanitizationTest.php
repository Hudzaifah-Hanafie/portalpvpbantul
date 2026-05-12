<?php

namespace Tests\Feature;

use App\Models\Benefit;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BenefitSanitizationTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminUser(): User
    {
        $accessAdmin = Permission::firstOrCreate(['name' => 'access-admin'], ['label' => 'Akses Admin']);
        $role = Role::create(['name' => 'content-admin', 'label' => 'Content Admin']);
        $role->permissions()->attach([$accessAdmin->id]);

        $user = User::factory()->create();
        $user->syncRoles([$role->id]);

        return $user;
    }

    public function test_benefit_deskripsi_is_sanitized_on_store(): void
    {
        $admin = $this->createAdminUser();

        $payload = [
            'judul' => 'Benefit A',
            'deskripsi' => '<p>Halo <strong>Test</strong><script>alert(1)</script>'
                . '<img src="x" onerror="alert(1)"></p>',
            'is_active' => true,
            'urutan' => 1,
        ];

        $response = $this->actingAs($admin)->post('/admin/benefit', $payload);

        $response->assertRedirect('/admin/benefit');

        $benefit = Benefit::first();
        $this->assertNotNull($benefit);
        $this->assertStringContainsString('<strong>', $benefit->deskripsi);
        $this->assertStringNotContainsString('<script', $benefit->deskripsi);
        $this->assertStringNotContainsString('onerror', $benefit->deskripsi);
    }
}
