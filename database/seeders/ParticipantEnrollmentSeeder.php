<?php

namespace Database\Seeders;

use App\Models\CourseAssignment;
use App\Models\CourseClass;
use App\Models\CourseEnrollment;
use App\Models\CourseSubmission;
use App\Models\Program;
use App\Models\Role;
use App\Models\TrainingSchedule;
use App\Models\User;
use App\Models\UserEducation;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ParticipantEnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $participants = User::where('email', 'like', 'peserta%@pvp.test')
            ->orderBy('email')
            ->get();

        if ($participants->isEmpty()) {
            $participants = User::whereHas('roles', fn ($q) => $q->where('name', 'participant'))
                ->orderBy('email')
                ->take(30)
                ->get();
        }

        if ($participants->isEmpty()) {
            return;
        }

        $admin = User::whereHas('roles', fn ($q) => $q->whereIn('name', ['superadmin', 'admin']))->first();
        $instructor = User::whereHas('roles', fn ($q) => $q->whereIn('name', ['instructor', 'instruktur']))->first();
        $participantRole = Role::where('name', 'participant')->first();

        [$schedule, $class] = $this->resolveScheduleAndClass($admin, $instructor);
        if (! $schedule || ! $class) {
            return;
        }

        $assignments = CourseAssignment::where('course_class_id', $class->id)->get();
        if ($assignments->isEmpty()) {
            $assignments = $this->seedAssignments($class, $admin?->id ?? $instructor?->id);
        }

        foreach ($participants as $index => $user) {
            if ($participantRole) {
                $user->roles()->syncWithoutDetaching([$participantRole->id]);
            }

            if (! $user->nik) {
                $nik = '331111070292' . sprintf('%04d', ($index + 1));
                while (User::where('nik', $nik)->where('id', '!=', $user->id)->exists()) {
                    $nik = '331111070292' . sprintf('%04d', rand(1000, 9999));
                }
                $user->update(['nik' => $nik]);
            }

            UserProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'identity_number' => $user->nik,
                    'phone' => '08' . rand(111111111, 999999999),
                    'address' => 'Jl. Dummy No. ' . ($index + 1),
                    'domicile_address' => 'Surakarta',
                    'birth_place' => 'Surakarta',
                    'birth_date' => '200' . rand(1, 5) . '-0' . rand(1, 9) . '-0' . rand(1, 9),
                    'gender' => $index % 2 === 0 ? 'perempuan' : 'laki-laki',
                ]
            );

            UserEducation::updateOrCreate(
                ['user_id' => $user->id, 'sso_id' => null],
                [
                    'school_name' => 'SMK Dummy ' . ($index + 1),
                    'graduate_name' => $index % 2 === 0 ? 'S1' : 'SMK',
                    'study_field_name' => 'Teknik Gambar',
                    'start_year' => 2018,
                    'finish_year' => 2021,
                ]
            );

            CourseEnrollment::updateOrCreate(
                ['course_class_id' => $class->id, 'user_id' => $user->id],
                [
                    'status' => 'approved',
                    'admin_status' => 'verified',
                    'created_by' => $admin?->id,
                    'written_score' => rand(60, 90),
                    'interview_score' => rand(60, 90),
                    'final_score' => rand(70, 95),
                    'pre_test_score' => rand(60, 85),
                    'post_test_score' => rand(70, 95),
                    'practice_score' => rand(70, 95),
                    'attitude_score' => rand(80, 100),
                    'attendance_rate' => rand(85, 100),
                    'final_grade' => rand(75, 95),
                    'competency_status' => 'competent',
                ]
            );

            foreach ($assignments as $assignment) {
                CourseSubmission::updateOrCreate(
                    ['course_assignment_id' => $assignment->id, 'user_id' => $user->id],
                    [
                        'content_text' => 'Jawaban dummy peserta.',
                        'status' => 'graded',
                        'submitted_at' => now()->subDays(rand(1, 5)),
                        'graded_at' => now(),
                        'total_score' => rand(70, 95),
                        'graded_by' => $instructor?->id ?? $admin?->id,
                    ]
                );
            }
        }
    }

    private function resolveScheduleAndClass(?User $admin, ?User $instructor): array
    {
        $schedule = TrainingSchedule::with('program')->orderBy('mulai')->first();
        if (! $schedule) {
            $program = Program::firstOrCreate(
                ['judul' => 'Bangunan'],
                ['deskripsi' => 'Program dummy untuk nominatif.', 'gambar' => null]
            );

            $classId = (string) Str::uuid();
            $class = CourseClass::create([
                'id' => $classId,
                'title' => 'Membuat Gambar 2D dengan AutoCAD',
                'description' => 'Kelas dummy untuk nominatif peserta.',
                'format' => 'luring',
                'status' => 'published',
                'is_active' => true,
                'instructor_id' => $instructor?->id,
                'created_by' => $admin?->id,
                'approved_by' => $admin?->id,
                'approved_at' => now(),
                'published_at' => now(),
            ]);

            $schedule = TrainingSchedule::create([
                'id' => $classId,
                'external_id' => 'DEMO-SCHEDULE-001',
                'batch_id' => 'WEEK I',
                'program_id' => $program->id,
                'judul' => $class->title,
                'penyelenggara' => 'BPVP SURAKARTA',
                'lokasi' => 'BPVP SURAKARTA',
                'mulai' => now()->startOfMonth()->addDays(4),
                'selesai' => now()->startOfMonth()->addDays(9),
                'kuota' => '30',
                'bulan' => now()->format('m'),
                'tahun' => now()->format('Y'),
                'pendaftaran_link' => null,
                'catatan' => 'Data dummy nominatif.',
                'is_active' => true,
            ]);

            return [$schedule, $class];
        }

        $class = CourseClass::firstOrNew(['id' => $schedule->id]);
        if (! $class->exists) {
            $class->fill([
                'title' => $schedule->judul,
                'description' => $schedule->catatan,
                'format' => 'luring',
                'status' => 'published',
                'is_active' => true,
                'instructor_id' => $instructor?->id,
                'created_by' => $admin?->id,
                'approved_by' => $admin?->id,
                'approved_at' => now(),
                'published_at' => now(),
            ]);
            $class->save();
        } elseif (! $class->instructor_id && $instructor) {
            $class->update(['instructor_id' => $instructor->id]);
        }

        return [$schedule, $class];
    }

    private function seedAssignments(CourseClass $class, ?string $actorId)
    {
        $quiz = CourseAssignment::create([
            'course_class_id' => $class->id,
            'title' => 'Quiz Dummy',
            'description' => 'Kuis dummy untuk penilaian.',
            'type' => 'quiz',
            'due_at' => now()->addDays(3),
            'weight' => 30,
            'max_score' => 100,
            'status' => 'published',
            'is_active' => true,
            'created_by' => $actorId,
            'approved_by' => $actorId,
            'approved_at' => now(),
            'published_at' => now(),
        ]);

        $project = CourseAssignment::create([
            'course_class_id' => $class->id,
            'title' => 'Proyek Dummy',
            'description' => 'Tugas praktik dummy.',
            'type' => 'file',
            'due_at' => now()->addDays(5),
            'weight' => 70,
            'max_score' => 100,
            'status' => 'published',
            'is_active' => true,
            'created_by' => $actorId,
            'approved_by' => $actorId,
            'approved_at' => now(),
            'published_at' => now(),
        ]);

        return collect([$quiz, $project]);
    }
}
