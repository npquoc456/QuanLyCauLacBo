<?php

namespace App\Models;

use App\Enums\DotTuyenTrangThai;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DotTuyenThanhVien extends Model
{
    protected $table = 'dot_tuyen_thanh_viens';

    protected $primaryKey = 'MaDotTuyen';

    public $timestamps = true;

    protected $fillable = [
        'TenDotTuyen',
        'MoTa',
        'URLPoster',
        'DoiTuong',
        'DiaDiemPhongVan',
        'NgayBatDau',
        'NgayKetThuc',
        'NgayPhongVan',
        'NgayCongBoKetQua',
        'LaBanNhap',
        'TrangThai',
        'MaCLB',
    ];

    protected function casts(): array
    {
        return [
            'NgayBatDau' => 'datetime',
            'NgayKetThuc' => 'datetime',
            'NgayPhongVan' => 'datetime',
            'NgayCongBoKetQua' => 'datetime',
            'LaBanNhap' => 'boolean',
            'TrangThai' => DotTuyenTrangThai::class,
            'MaCLB' => 'integer',
        ];
    }

    protected function trangThai(): Attribute
    {
        return Attribute::get(function (): DotTuyenTrangThai {
            $ngayBatDau = $this->NgayBatDau;
            $ngayKetThuc = $this->NgayKetThuc;

            if (! $ngayBatDau || ! $ngayKetThuc) {
                return DotTuyenTrangThai::CHUA_TUYEN;
            }

            $now = now();

            if ($now->lt($ngayBatDau)) {
                return DotTuyenTrangThai::CHUA_TUYEN;
            }

            if ($now->lt($ngayKetThuc) && $now->greaterThanOrEqualTo($ngayBatDau)) {
                return DotTuyenTrangThai::DANG_TUYEN;
            }

            return DotTuyenTrangThai::CHUA_TUYEN;
        })->withoutObjectCaching();
    }

    public function cauLacBo(): BelongsTo
    {
        return $this->belongsTo(CauLacBo::class, 'MaCLB', 'MaCLB');
    }

    public function viTriTuyens(): HasMany
    {
        return $this->hasMany(ViTriTuyen::class, 'MaDotTuyen', 'MaDotTuyen');
    }
}
