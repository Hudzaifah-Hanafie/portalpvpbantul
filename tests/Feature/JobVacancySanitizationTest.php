<?php

namespace Tests\Feature;

use App\Models\JobVacancy;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobVacancySanitizationTest extends TestCase
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

    public function test_job_vacancy_description_is_sanitized(): void
    {
        $admin = $this->createAdminUser();

        $payload = [
            'judul' => 'Lowongan A',
            'deskripsi' => '<p>Halo <strong>Test</strong><script>alert(1)</script>'
                . '<img src="x" onerror="alert(1)"></p>',
            'kualifikasi' => '<ul><li>Skill</li></ul><script>alert(2)</script>',
            'is_active' => true,
        ];

        $response = $this->actingAs($admin)->post('/admin/lowongan', $payload);

        $response->assertRedirect('/admin/lowongan');

        $vacancy = JobVacancy::first();
        $this->assertNotNull($vacancy);
        $this->assertStringContainsString('<strong>', $vacancy->deskripsi);
        $this->assertStringNotContainsString('<script', $vacancy->deskripsi);
        $this->assertStringNotContainsString('onerror', $vacancy->deskripsi);
        $this->assertStringNotContainsString('<script', $vacancy->kualifikasi);
    }
}
