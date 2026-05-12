<?php

namespace App\Exports;

use App\Models\CourseClass;
use App\Models\CourseEnrollment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CourseGradebookExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    protected $classId;
    protected $className;

    public function __construct(string $classId)
    {
        $this->classId = $classId;
        $courseClass = CourseClass::find($classId);
        $this->className = $courseClass ? $courseClass->title : 'Gradebook';
    }

    public function collection()
    {
        return CourseEnrollment::with('user')
            ->where('course_class_id', $this->classId)
            ->whereIn('status', ['active', 'approved', 'completed'])
            ->get();
    }

    public function headings(): array
    {
        return [
            'Nama Peserta',
            'Pre-Test',
            'Post-Test',
            'Praktik',
            'Sikap',
            'Kehadiran (%)',
            'Nilai Akhir',
            'Status Kelulusan',
        ];
    }

    public function map($enrollment): array
    {
        return [
            $enrollment->user ? $enrollment->user->name : '-',
            $enrollment->pre_test_score ?? '-',
            $enrollment->post_test_score ?? $enrollment->written_score ?? '-',
            $enrollment->practice_score ?? '-',
            $enrollment->attitude_score ?? '-',
            $enrollment->attendance_rate !== null ? $enrollment->attendance_rate . '%' : '-',
            $enrollment->final_grade ?? '-',
            $enrollment->competency_status === 'competent' ? 'Kompeten' : ($enrollment->competency_status === 'not_competent' ? 'Belum Kompeten' : 'Pending'),
        ];
    }

    public function title(): string
    {
        return substr('Nilai ' . $this->className, 0, 31);
    }
}
