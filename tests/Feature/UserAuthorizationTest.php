<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_permission_cannot_access_user_management(): void
    {
        $role = Role::create(['name' => 'tester', 'label' => 'Tester']);
        $user = User::factory()->create();
        $user->syncRoles([$role->id]);

        $response = $this->actingAs($user)->get('/admin/users');

        $response->assertForbidden();
    }

    public function test_user_with_permission_can_access_user_management(): void
    {
        $accessAdmin = Permission::firstOrCreate(['name' => 'access-admin'], ['label' => 'Akses Admin']);
        $permission = Permission::firstOrCreate(['name' => 'manage-users'], ['label' => 'Kelola Pengguna']);
        $role = Role::create(['name' => 'manager', 'label' => 'Manager']);
        $role->permissions()->attach([$accessAdmin->id, $permission->id]);

        $user = User::factory()->create();
        $user->syncRoles([$role->id]);

        $response = $this->actingAs($user)->get('/admin/users');

        $response->assertOk();
    }
}
