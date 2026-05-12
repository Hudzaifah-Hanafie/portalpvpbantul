<?php

namespace App\Models;

use App\Traits\HasUuid;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Galeri extends Model
{
    use HasUuid;

    protected $fillable = [
        'judul',
        'gambar',
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

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }
}
