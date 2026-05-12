<?php

namespace Database\Seeders;

use App\Models\CourseAssignment;
use App\Models\CourseAttendance;
use App\Models\CourseClass;
use App\Models\CourseCurriculum;
use App\Models\CourseCurriculumUnit;
use App\Models\CourseEnrollment;
use App\Models\CourseSession;
use App\Models\CourseSubmission;
use App\Models\Program;
use App\Models\Role;
use App\Models\Survey;
use App\Models\SurveyAnswer;
use App\Models\SurveyInstance;
use App\Models\SurveyResponse;
use App\Models\TaskLetter;
use App\Models\TrainingSchedule;
use App\Models\User;
use App\Models\UserEducation;
use App\Models\UserProfile;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ReportDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (CourseClass::where('title', 'Membuat Gambar 2D dengan AutoCAD')->exists()) {
            return;
        }

        $admin = User::whereHas('roles', fn ($q) => $q->whereIn('name', ['superadmin', 'admin']))->first();
        if (! $admin) {
            $admin = User::create([
                'name' => 'Admin Demo',
                'email' => 'admin.demo@pvp.test',
                'password' => bcrypt('password'),
            ]);
            if ($adminRole = Role::where('name', 'admin')->first()) {
                $admin->roles()->syncWithoutDetaching([$adminRole->id]);
            }
        }

        $instructor = User::firstOrCreate(
            ['email' => 'instruktur.demo@pvp.test'],
            ['name' => 'Instruktur Demo', 'password' => bcrypt('password')]
        );
        if ($instructorRole = Role::where('name', 'instructor')->first()) {
            $instructor->roles()->syncWithoutDetaching([$instructorRole->id]);
        }

        $participantRole = Role::where('name', 'participant')->first();

        $program = Program::firstOrCreate(
            ['judul' => 'Bangunan'],
            ['deskripsi' => 'Program demo untuk laporan pelatihan.', 'gambar' => null]
        );

        $class = CourseClass::create([
            'title' => 'Membuat Gambar 2D dengan AutoCAD',
            'description' => 'Kelas demo untuk laporan nominatif dan penilaian.',
            'format' => 'luring',
            'competencies' => ['Bangunan'],
            'tags' => ['demo', 'nominatif'],
            'status' => 'published',
            'is_active' => true,
            'instructor_id' => $instructor->id,
            'created_by' => $admin->id,
            'approved_by' => $admin->id,
            'approved_at' => now(),
            'published_at' => now(),
            'min_attendance' => 80,
            'min_score' => 70,
            'require_final_project' => true,
            'require_final_exam' => true,
            'weight_theory' => 30,
            'weight_practice' => 60,
            'weight_attitude' => 10,
        ]);

        $startDate = now()->startOfMonth()->addDays(4);
        $endDate = (clone $startDate)->addDays(4);

        TrainingSchedule::updateOrCreate(
            ['id' => $class->id],
            [
                'program_id' => $program->id,
                'external_id' => 'DEMO-SKILLHUB-001',
                'batch_id' => 'WEEK I',
                'judul' => $class->title,
                'penyelenggara' => 'BPVP SURAKARTA',
                'lokasi' => 'BPVP SURAKARTA',
                'mulai' => $startDate,
                'selesai' => $endDate,
                'kuota' => '20',
                'bulan' => $startDate->format('m'),
                'tahun' => $startDate->format('Y'),
                'pendaftaran_link' => 'https://skillhub.kemnaker.go.id/pelatihan/demo',
                'catatan' => 'Data dummy untuk laporan.',
                'is_active' => true,
            ]
        );

        TaskLetter::updateOrCreate(
            ['course_class_id' => $class->id],
            [
                'letter_number' => 'ST/001/PP/2026',
                'legal_basis' => 'SK Dirjen PPVPP No. 01/2026',
                'start_date' => $startDate,
                'end_date' => $endDate,
                'start_time' => '08:00',
                'end_time' => '16:00',
                'location_name' => 'BPVP SURAKARTA',
                'location_link' => null,
                'instructors' => [
                    ['name' => $instructor->name, 'nip' => '198706152021011002', 'position' => 'Instruktur'],
                ],
                'committees' => [
                    ['name' => 'Panitia A', 'role' => 'Ketua'],
                    ['name' => 'Panitia B', 'role' => 'Sekretaris'],
                    ['name' => 'Panitia C', 'role' => 'Anggota'],
                ],
                'participant_statuses' => ['approved', 'active'],
                'status' => 'published',
                'signed_city' => 'Surakarta',
                'signed_at' => $endDate,
                'signatory_name' => 'Verry Fahrudin, S.E., M.M.',
                'signatory_position' => 'KEPALA',
                'signatory_nip' => '198706152021011002',
                'created_by' => $admin->id,
            ]
        );

        $curriculum = CourseCurriculum::updateOrCreate(
            ['course_class_id' => $class->id, 'title' => 'Kurikulum AutoCAD 2D'],
            [
                'skkni_reference' => 'TIK.JK01.001.01',
                'matrix_reference' => 'Matriks Kompetensi AutoCAD',
                'notes' => 'Kurikulum demo untuk laporan.',
                'method' => 'luring',
                'total_jp_theory' => 40,
                'total_jp_practice' => 60,
                'is_active' => true,
                'created_by' => $admin->id,
            ]
        );

        $units = [
            ['unit_code' => 'TIK.JK01.001.01', 'unit_title' => 'Mengoperasikan AutoCAD Dasar', 'jp_theory' => 8, 'jp_practice' => 12],
            ['unit_code' => 'TIK.JK01.002.01', 'unit_title' => 'Membuat Gambar Teknik 2D', 'jp_theory' => 10, 'jp_practice' => 18],
            ['unit_code' => 'TIK.JK01.003.01', 'unit_title' => 'Menyusun Layout & Plotting', 'jp_theory' => 6, 'jp_practice' => 10],
        ];
        foreach ($units as $index => $unit) {
            CourseCurriculumUnit::updateOrCreate(
                [
                    'course_curriculum_id' => $curriculum->id,
                    'unit_code' => $unit['unit_code'],
                ],
                [
                    'unit_title' => $unit['unit_title'],
                    'elements' => 'Persiapan, Eksekusi, Evaluasi',
                    'kuk' => 'Output sesuai standar gambar teknik',
                    'materials' => 'Teori & Praktik sesuai modul',
                    'jp_theory' => $unit['jp_theory'],
                    'jp_practice' => $unit['jp_practice'],
                    'method' => 'luring',
                    'sort_order' => $index + 1,
                    'is_active' => true,
                    'created_by' => $admin->id,
                ]
            );
        }

        $sessions = collect();
        for ($i = 0; $i < 3; $i++) {
            $startAt = (clone $startDate)->addDays($i)->setTime(8, 0);
            $endAt = (clone $startAt)->addHours(4);
            $sessions->push(CourseSession::create([
                'course_class_id' => $class->id,
                'title' => 'Sesi ' . ($i + 1) . ' - AutoCAD',
                'description' => 'Materi dasar & praktik.',
                'start_at' => $startAt,
                'end_at' => $endAt,
                'meeting_link' => null,
                'status' => 'published',
                'is_active' => true,
                'created_by' => $admin->id,
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'published_at' => now(),
            ]));
        }

        $quiz = CourseAssignment::create([
            'course_class_id' => $class->id,
            'title' => 'Quiz AutoCAD Dasar',
            'description' => 'Kuis evaluasi teori.',
            'type' => 'quiz',
            'due_at' => (clone $endDate)->addDays(1),
            'weight' => 30,
            'max_score' => 100,
            'status' => 'published',
            'is_active' => true,
            'created_by' => $admin->id,
            'approved_by' => $admin->id,
            'approved_at' => now(),
            'published_at' => now(),
        ]);

        $project = CourseAssignment::create([
            'course_class_id' => $class->id,
            'title' => 'Proyek Akhir Gambar 2D',
            'description' => 'Tugas praktik akhir.',
            'type' => 'file',
            'due_at' => (clone $endDate)->addDays(2),
            'weight' => 70,
            'max_score' => 100,
            'status' => 'published',
            'is_active' => true,
            'created_by' => $admin->id,
            'approved_by' => $admin->id,
            'approved_at' => now(),
            'published_at' => now(),
        ]);

        $participants = collect();
        for ($i = 1; $i <= 10; $i++) {
            $email = sprintf('peserta%02d@demo.test', $i);
            $nik = '331111070292' . sprintf('%04d', $i);
            while (User::where('nik', $nik)->exists()) {
                $nik = '331111070292' . sprintf('%04d', rand(1000, 9999));
            }

            $user = User::where('email', $email)->first();
            if (! $user) {
                $user = User::create([
                    'name' => 'Peserta Demo ' . $i,
                    'email' => $email,
                    'password' => bcrypt('password'),
                    'nik' => $nik,
                ]);
            } elseif (! $user->nik) {
                $user->update(['nik' => $nik]);
            }
            if ($participantRole) {
                $user->roles()->syncWithoutDetaching([$participantRole->id]);
            }

            UserProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'identity_number' => $user->nik,
                    'phone' => '08' . rand(111111111, 999999999),
                    'address' => 'Jl. Demo No. ' . $i . ' Surakarta',
                    'domicile_address' => 'Surakarta',
                    'birth_place' => 'Surakarta',
                    'birth_date' => '200' . rand(1, 5) . '-0' . rand(1, 9) . '-0' . rand(1, 9),
                    'gender' => $i % 2 === 0 ? 'perempuan' : 'laki-laki',
                ]
            );

            UserEducation::updateOrCreate(
                ['user_id' => $user->id, 'sso_id' => null],
                [
                    'school_name' => 'SMK Demo ' . $i,
                    'graduate_name' => $i % 2 === 0 ? 'S1' : 'SMK',
                    'study_field_name' => 'Teknik Gambar',
                    'start_year' => 2018,
                    'finish_year' => 2021,
                ]
            );

            $participants->push($user);

            CourseEnrollment::updateOrCreate(
                ['course_class_id' => $class->id, 'user_id' => $user->id],
                [
                    'status' => 'approved',
                    'admin_status' => 'verified',
                    'created_by' => $admin->id,
                    'pre_test_score' => rand(60, 85),
                    'post_test_score' => rand(75, 95),
                    'practice_score' => rand(75, 95),
                    'attitude_score' => rand(80, 100),
                    'attendance_rate' => rand(85, 100),
                    'final_grade' => rand(80, 95),
                    'competency_status' => 'kompeten',
                    'written_score' => rand(70, 90),
                    'interview_score' => rand(70, 90),
                    'final_score' => rand(75, 95),
                ]
            );
        }

        foreach ($sessions as $session) {
            foreach ($participants as $participant) {
                CourseAttendance::updateOrCreate(
                    ['course_session_id' => $session->id, 'user_id' => $participant->id],
                    [
                        'status' => 'hadir',
                        'checked_at' => $session->start_at,
                        'created_by' => $admin->id,
                    ]
                );
            }
        }

        foreach ($participants as $participant) {
            foreach ([$quiz, $project] as $assignment) {
                CourseSubmission::updateOrCreate(
                    ['course_assignment_id' => $assignment->id, 'user_id' => $participant->id],
                    [
                        'content_text' => 'Jawaban demo',
                        'status' => 'graded',
                        'submitted_at' => now(),
                        'graded_at' => now(),
                        'total_score' => rand(75, 95),
                        'graded_by' => $instructor->id,
                    ]
                );
            }
        }

        $survey = Survey::where('slug', 'evaluasi-penyelenggaraan-pelatihan')->first();
        if ($survey) {
            $instance = SurveyInstance::firstOrCreate(
                ['survey_id' => $survey->id, 'course_class_id' => $class->id],
                [
                    'instructor_id' => $instructor->id,
                    'status' => 'open',
                    'opens_at' => now()->subDays(2),
                    'closes_at' => now()->addDays(7),
                    'created_by' => $admin->id,
                ]
            );

            $questions = $survey->questions()->get();
            $participants->take(5)->each(function ($participant) use ($survey, $instance, $questions) {
                $response = SurveyResponse::create([
                    'survey_id' => $survey->id,
                    'survey_instance_id' => $instance->id,
                    'user_id' => $participant->id,
                    'submitted_at' => now(),
                    'meta' => ['demo' => true],
                ]);

                foreach ($questions as $question) {
                    $answerData = [
                        'survey_response_id' => $response->id,
                        'survey_question_id' => $question->id,
                    ];
                    if ($question->type === 'linear_scale') {
                        $answerData['answer_numeric'] = rand(4, 5);
                    } else {
                        $answerData['answer_text'] = 'Saran demo untuk peningkatan.';
                    }
                    SurveyAnswer::create($answerData);
                }
            });
        }
    }
}
