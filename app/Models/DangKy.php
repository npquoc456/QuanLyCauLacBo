<?php

namespace App\Models;

use App\Enums\TrangThaiDangKy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DangKy extends Model
{
    protected $table = 'dang_kies';

    protected $primaryKey = 'MaDangKy';

    public $timestamps = true;

    protected $fillable = [
        'MaNguoiDung',
        'MaSuKien',
        'TrangThai',
        'ThoiGianCheckIn',
        'NgayDangKy',
        'MaQRToken',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $dangKy): void {
            $dangKy->NgayDangKy ??= now();
        });
    }

    protected function casts(): array
    {
        return [
            'TrangThai' => TrangThaiDangKy::class,
            'ThoiGianCheckIn' => 'datetime',
            'NgayDangKy' => 'datetime',
            'MaNguoiDung' => 'integer',
            'MaSuKien' => 'integer',
        ];
    }

    public function suKien(): BelongsTo
    {
        return $this->belongsTo(SuKien::class, 'MaSuKien', 'MaSuKien');
    }
}
