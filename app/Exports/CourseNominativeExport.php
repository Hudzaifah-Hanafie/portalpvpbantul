<?php

namespace App\Exports;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CourseNominativeExport implements FromCollection, WithHeadings, Responsable
{
    public string $fileName;

    private Collection $rows;

    public function __construct(Collection $rows, string $fileName)
    {
        $this->rows = $rows;
        $this->fileName = $fileName;
    }

    public function collection()
    {
        return $this->rows->map(function ($row) {
            return [
                $row['no'] ?? '',
                $row['no_induk'] ?? '',
                $row['nama'] ?? '',
                $row['ttl'] ?? '',
                $row['gender'] ?? '',
                $row['education'] ?? '',
                $row['address'] ?? '',
                $row['phone'] ?? '',
                $row['note'] ?? '',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'NO',
            'NO. INDUK SISWA',
            'NAMA',
            'TEMPAT/TGL LAHIR',
            'JENIS KELAMIN',
            'PENDIDIKAN TERAKHIR',
            'ALAMAT',
            'NO. TELP/HP',
            'KETERANGAN',
        ];
    }
}
