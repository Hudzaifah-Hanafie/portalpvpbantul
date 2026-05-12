<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogFilterTest extends TestCase
{
    use RefreshDatabase;

    private function createAuditor(): User
    {
        $accessAdmin = Permission::firstOrCreate(['name' => 'access-admin'], ['label' => 'Akses Admin']);
        $permission = Permission::firstOrCreate(['name' => 'manage-audit'], ['label' => 'Audit']);
        $role = Role::create(['name' => 'auditor', 'label' => 'Auditor']);
        $role->permissions()->attach([$accessAdmin->id, $permission->id]);

        $user = User::factory()->create();
        $user->syncRoles([$role->id]);

        return $user;
    }

    public function test_audit_logs_can_be_filtered_by_action(): void
    {
        $auditor = $this->createAuditor();

        $log1 = ActivityLog::create([
            'action' => 'user.created',
            'description' => 'dummy',
        ]);
        $log1->update([
            'created_at' => Carbon::parse('2024-01-01 10:00:00'),
            'updated_at' => Carbon::parse('2024-01-01 10:00:00'),
        ]);

        $log2 = ActivityLog::create([
            'action' => 'auth.login',
            'description' => 'dummy',
        ]);
        $log2->update([
            'created_at' => Carbon::parse('2024-02-01 12:00:00'),
            'updated_at' => Carbon::parse('2024-02-01 12:00:00'),
        ]);

        $response = $this->actingAs($auditor)->get('/admin/activity-logs?action=auth.login');

        $response->assertOk();
        $response->assertSee('auth.login');
        $logs = $response->viewData('logs');
        $this->assertCount(1, $logs);
        $this->assertEquals('auth.login', $logs->first()->action);
    }
}
