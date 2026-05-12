<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'manage-users', 'label' => 'Kelola Pengguna', 'module' => 'system'],
            ['name' => 'manage-audit', 'label' => 'Melihat Log Aktivitas', 'module' => 'system'],
            ['name' => 'manage-access', 'label' => 'Kelola Role & Permission', 'module' => 'system'],
            ['name' => 'impersonate-users', 'label' => 'Impersonasi Pengguna', 'module' => 'system'],
            ['name' => 'view-talent-pool', 'label' => 'Lihat Talent Pool & CV Book', 'module' => 'kemitraan'],
            ['name' => 'approve-content', 'label' => 'Menyetujui Konten', 'module' => 'konten'],
            ['name' => 'review-content', 'label' => 'Review Konten (draft/pending)', 'module' => 'konten'],
            ['name' => 'manage-seo', 'label' => 'Kelola SEO', 'module' => 'konten'],
            ['name' => 'manage-berita', 'label' => 'Kelola Berita & Artikel', 'module' => 'konten'],
            ['name' => 'manage-program', 'label' => 'Kelola Program Pelatihan', 'module' => 'konten'],
            ['name' => 'manage-publication', 'label' => 'Kelola Publikasi', 'module' => 'konten'],
            ['name' => 'manage-gallery', 'label' => 'Kelola Media & Galeri', 'module' => 'konten'],
            ['name' => 'manage-classes', 'label' => 'Kelola Kelas & Silabus', 'module' => 'kelas'],
            ['name' => 'manage-sessions', 'label' => 'Kelola Jadwal & Rekaman', 'module' => 'kelas'],
            ['name' => 'manage-assignments', 'label' => 'Kelola Tugas/Quiz', 'module' => 'kelas'],
            ['name' => 'grade-submissions', 'label' => 'Menilai Submission', 'module' => 'kelas'],
            ['name' => 'manage-announcements', 'label' => 'Kelola Pengumuman Kelas', 'module' => 'kelas'],
            ['name' => 'moderate-class-forum', 'label' => 'Moderasi Forum Kelas', 'module' => 'kelas'],
            ['name' => 'manage-enrollment', 'label' => 'Kelola Enrollment Peserta', 'module' => 'kelas'],
            ['name' => 'view-class-surveys', 'label' => 'Lihat Survei Kelas/Instruktur', 'module' => 'survey'],
            ['name' => 'manage-surveys', 'label' => 'Kelola Survey & Formulir', 'module' => 'survey'],
            ['name' => 'view-survey-analytics', 'label' => 'Lihat Analitik Survey', 'module' => 'survey'],
            ['name' => 'manage-faq', 'label' => 'Kelola FAQ', 'module' => 'layanan'],
            ['name' => 'manage-public-service', 'label' => 'Kelola Pelayanan Publik', 'module' => 'layanan'],
            ['name' => 'manage-ppid', 'label' => 'Kelola PPID', 'module' => 'layanan'],
            ['name' => 'manage-settings', 'label' => 'Kelola Pengaturan Situs', 'module' => 'system'],
            ['name' => 'access-admin', 'label' => 'Akses Panel Admin', 'module' => 'system'],
            ['name' => 'access-alumni-forum', 'label' => 'Akses Forum Alumni', 'module' => 'alumni'],
            ['name' => 'moderate-alumni-forum', 'label' => 'Moderasi Forum Alumni', 'module' => 'alumni'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['name' => $permission['name']], $permission);
        }

        $rolePermissions = [
            'superadmin' => Permission::pluck('id')->all(),
            'admin' => Permission::whereNotIn('name', ['manage-users', 'manage-audit', 'manage-access', 'impersonate-users'])->pluck('id')->all(),
            'editor' => Permission::whereIn('name', [
                'manage-berita',
                'manage-program',
                'manage-publication',
                'manage-gallery',
                'manage-faq',
                'manage-surveys',
            ])->pluck('id')->all(),
            'viewer' => Permission::where('name', 'access-admin')->pluck('id')->all(),
            'alumni' => Permission::where('name', 'access-alumni-forum')->pluck('id')->all(),
            'moderator' => Permission::whereIn('name', [
                'moderate-alumni-forum',
                'access-alumni-forum',
                'moderate-class-forum',
            ])->pluck('id')->all(),
            'reviewer' => Permission::whereIn('name', [
                'approve-content',
                'review-content',
                'manage-classes',
                'manage-sessions',
                'manage-assignments',
                'grade-submissions',
                'manage-announcements',
                'moderate-class-forum',
                'view-class-surveys',
                'manage-enrollment',
                'manage-surveys',
                'view-survey-analytics',
                'access-admin',
            ])->pluck('id')->all(),
            'instructor' => Permission::whereIn('name', [
                'manage-classes',
                'manage-sessions',
                'manage-assignments',
                'grade-submissions',
                'manage-announcements',
                'moderate-class-forum',
                'view-class-surveys',
            ])->pluck('id')->all(),
            'instruktur' => Permission::whereIn('name', [
                'manage-classes',
                'manage-sessions',
                'manage-assignments',
                'grade-submissions',
                'manage-announcements',
                'moderate-class-forum',
                'view-class-surveys',
            ])->pluck('id')->all(),
            'participant' => Permission::whereIn('name', [
                'access-alumni-forum',
            ])->pluck('id')->all(),
        ];

        foreach ($rolePermissions as $roleName => $permissionIds) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->permissions()->sync($permissionIds);
            }
        }
    }
}
