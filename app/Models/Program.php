<?php

namespace App\Models;

use App\Traits\HasUuid;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    use HasUuid;

    protected $fillable = [
        'external_id',
        'judul',
        'deskripsi',
        'gambar',
        'pendaftaran_link',
        'biaya_label',
        'sertifikat_label',
        'bahasa_label',
        'kode_unit_kompetensi',
        'fasilitas_keunggulan',
        'info_tambahan',
        'status',
        'approved_by',
        'approved_at',
        'published_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public static function statuses(): array
    {
        return [
            'draft' => 'Draft',
            'pending' => 'Menunggu Review',
            'published' => 'Terpublikasi',
        ];
    }

    public function trainingSchedules(): HasMany
    {
        return $this->hasMany(TrainingSchedule::class);
    }
}
