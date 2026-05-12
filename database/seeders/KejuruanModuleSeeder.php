<?php

namespace Database\Seeders;

use App\Models\CourseClass;
use App\Models\KejuruanModule;
use App\Models\Program;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class KejuruanModuleSeeder extends Seeder
{
    public function run(): void
    {
        $programs = Program::orderBy('judul')->get();
        if ($programs->isEmpty()) {
            $programs = collect([
                Program::create([
                    'judul' => 'Bangunan',
                    'deskripsi' => 'Program demo untuk modul kejuruan.',
                    'gambar' => null,
                ]),
            ]);
        }

        $instructor = $this->resolveInstructor();
        $admin = User::whereHas('roles', fn ($q) => $q->whereIn('name', ['superadmin', 'admin']))->first();

        $links = [
            'https://drive.google.com/file/d/%s/view',
            'https://onedrive.live.com/?id=%s',
            'https://www.dropbox.com/s/%s/modul.pdf?dl=0',
            'https://docs.google.com/document/d/%s/view',
        ];

        $modulesToCreate = 10;
        $programCount = max(1, $programs->count());

        for ($i = 1; $i <= $modulesToCreate; $i++) {
            $program = $programs[($i - 1) % $programCount];
            $title = sprintf('Modul %s #%d', $program->judul, $i);
            $link = sprintf($links[($i - 1) % count($links)], Str::uuid());

            KejuruanModule::firstOrCreate(
                [
                    'program_id' => $program->id,
                    'title' => $title,
                ],
                [
                    'description' => sprintf('Materi pelatihan %s (dummy) sesi %d.', $program->judul, $i),
                    'link_url' => $link,
                    'owner_id' => $instructor?->id,
                    'created_by' => $admin?->id ?? $instructor?->id,
                    'is_active' => true,
                ]
            );
        }
    }

    private function resolveInstructor(): ?User
    {
        $assignedInstructorId = CourseClass::whereNotNull('instructor_id')
            ->orderByDesc('created_at')
            ->value('instructor_id');

        if ($assignedInstructorId) {
            $assigned = User::find($assignedInstructorId);
            if ($assigned) {
                return $assigned;
            }
        }

        $instructor = User::whereHas('roles', fn ($q) => $q->whereIn('name', ['instructor', 'instruktur']))->first();
        if ($instructor) {
            return $instructor;
        }

        $user = User::create([
            'name' => 'Instruktur Demo',
            'email' => 'instruktur.demo@pvp.test',
            'password' => bcrypt('password'),
        ]);

        if ($role = Role::whereIn('name', ['instructor', 'instruktur'])->first()) {
            $user->roles()->syncWithoutDetaching([$role->id]);
        }

        return $user;
    }
}
