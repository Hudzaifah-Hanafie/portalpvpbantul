<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createManagerUser(): User
    {
        $accessAdmin = Permission::firstOrCreate(['name' => 'access-admin'], ['label' => 'Akses Admin']);
        $manageAccess = Permission::firstOrCreate(['name' => 'manage-access'], ['label' => 'Kelola Role']);
        $role = Role::create(['name' => 'access-manager', 'label' => 'Access Manager']);
        $role->permissions()->attach([$accessAdmin->id, $manageAccess->id]);

        $user = User::factory()->create();
        $user->syncRoles([$role->id]);

        return $user;
    }

    public function test_user_without_permission_cannot_open_roles_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin/roles')->assertForbidden();
    }

    public function test_user_with_permission_can_create_role(): void
    {
        $manager = $this->createManagerUser();
        $contentPermission = Permission::create(['name' => 'manage-content', 'label' => 'Kelola Konten']);

        $response = $this->actingAs($manager)->post('/admin/roles', [
            'name' => 'quality',
            'label' => 'Quality Team',
            'permissions' => [$contentPermission->id],
        ]);

        $response->assertRedirect('/admin/roles');
        $this->assertDatabaseHas('roles', ['name' => 'quality']);
    }
}
