<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiLoginRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_login_rate_limited_after_10_attempts(): void
    {
        $user = User::factory()->create([
            'email' => 'rate@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10']);

        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ])->assertStatus(401);
        }

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertStatus(429);
    }
}
