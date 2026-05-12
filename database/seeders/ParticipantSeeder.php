<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class ParticipantSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::where('name', 'participant')->first();

        for ($i = 1; $i <= 30; $i++) {
            $user = User::updateOrCreate(
                ['email' => "peserta{$i}@pvp.test"],
                [
                    'name' => "Peserta Dummy {$i}",
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                ]
            );

            if ($role) {
                $user->roles()->syncWithoutDetaching([$role->id]);
            }
        }
    }
}
