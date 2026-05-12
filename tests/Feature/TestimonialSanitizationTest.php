<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestimonialSanitizationTest extends TestCase
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

    public function test_testimonial_message_is_sanitized(): void
    {
        $admin = $this->createAdminUser();

        $payload = [
            'nama' => 'Test User',
            'pesan' => '<p>Bagus <strong>sekali</strong><script>alert(9)</script></p>',
            'is_active' => true,
        ];

        $response = $this->actingAs($admin)->post('/admin/testimonial', $payload);

        $response->assertRedirect('/admin/testimonial');

        $testimonial = Testimonial::first();
        $this->assertNotNull($testimonial);
        $this->assertStringContainsString('<strong>', $testimonial->pesan);
        $this->assertStringNotContainsString('<script', $testimonial->pesan);
    }
}
