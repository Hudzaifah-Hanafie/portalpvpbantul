<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            UserSeeder::class,
            ForumBadgeSeeder::class,
            WeeklyChallengeSeeder::class,
            ProgramSeeder::class,
            PartnerSeeder::class,
            TestimonialSeeder::class,
            BeritaSeeder::class,
            PengumumanSeeder::class,
            ProfileSeeder::class,
            CertificationSeeder::class,
            JobVacancySeeder::class,
            InfographicSeeder::class,
            PublicationSeeder::class,
            PublicServiceSeeder::class,
            FaqSeeder::class,
            ContactSeeder::class,
            PpidSeeder::class,
            SurveySeeder::class,
        ]);

        if (app()->environment('local')) {
            $this->call([
                ReportDemoSeeder::class,
                KejuruanModuleSeeder::class,
                ParticipantSeeder::class,
                ParticipantEnrollmentSeeder::class,
            ]);
        }
    }
}
