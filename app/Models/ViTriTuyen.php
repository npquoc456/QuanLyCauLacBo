<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ViTriTuyen extends Model
{
    protected $table = 'vi_tri_tuyens';

    protected $primaryKey = 'MaViTri';

    public $timestamps = true;

    protected $fillable = [
        'TenViTri',
        'Mota',
        'SoLuong',
        'MaDotTuyen',
        'MaBan',
    ];

    protected function casts(): array
    {
        return [
            'MaDotTuyen' => 'integer',
            'MaBan' => 'integer',
            'SoLuong' => 'integer',
        ];
    }

    public function dotTuyen(): BelongsTo
    {
        return $this->belongsTo(DotTuyenThanhVien::class, 'MaDotTuyen', 'MaDotTuyen');
    }

    public function donUngTuyens(): HasMany
    {
        return $this->hasMany(DonUngTuyen::class, 'MaViTri', 'MaViTri');
    }

    public function ban(): BelongsTo
    {
        return $this->belongsTo(Ban::class, 'MaBan', 'MaBan');
    }
}
