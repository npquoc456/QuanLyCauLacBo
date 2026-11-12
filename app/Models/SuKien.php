<?php

namespace App\Models;

use App\Enums\SuKienLoai;
use App\Enums\SuKienPhanLoaiNoiBo;
use App\Enums\SuKienTrangThai;
use App\Traits\NormalizesStorageUrls;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class SuKien extends Model
{
    use NormalizesStorageUrls;
    protected $table = 'su_kiens';

    protected $primaryKey = 'MaSuKien';

    protected $fillable = [
        'TenSuKien',
        'MoTa',
        'URLAnhNen',
        'DiaDiem',
        'SoLuongToiDa',
        'ThoiGianMoDangKy',
        'ThoiGianDongDangKy',
        'ThoiGianBatDau',
        'ThoiGianKetThuc',
        'LoaiSuKien',
        'PhanLoaiNoiBo',
        'MaCLB',
        'MaDanhMucSuKien',
        'DaDanhDau',
    ];

    protected function casts(): array
    {
        return [
            'SoLuongToiDa' => 'integer',
            'MaCLB' => 'integer',
            'MaDanhMucSuKien' => 'integer',
            'ThoiGianMoDangKy' => 'datetime',
            'ThoiGianDongDangKy' => 'datetime',
            'ThoiGianBatDau' => 'datetime',
            'ThoiGianKetThuc' => 'datetime',
            'DaDanhDau' => 'boolean',
            'LoaiSuKien' => SuKienLoai::class,
            'PhanLoaiNoiBo' => SuKienPhanLoaiNoiBo::class,
        ];
    }

    protected function trangThai(): Attribute
    {
        return Attribute::get(function (): SuKienTrangThai {
            $thoiGianMoDangKy = $this->ThoiGianMoDangKy;
            $thoiGianDongDangKy = $this->ThoiGianDongDangKy;
            $thoiGianBatDau = $this->ThoiGianBatDau;
            $thoiGianKetThuc = $this->ThoiGianKetThuc;

            if (! $thoiGianMoDangKy || ! $thoiGianDongDangKy || ! $thoiGianBatDau || ! $thoiGianKetThuc) {
                return SuKienTrangThai::CHUA_MO_DANG_KY;
            }

            $now = now();

            if ($now->lt($thoiGianMoDangKy)) {
                return SuKienTrangThai::CHUA_MO_DANG_KY;
            }

            if ($now->lt($thoiGianDongDangKy)) {
                return SuKienTrangThai::DANG_MO_DANG_KY;
            }

            if ($now->lt($thoiGianBatDau)) {
                return SuKienTrangThai::DA_DONG_DANG_KY;
            }

            if ($now->lt($thoiGianKetThuc)) {
                return SuKienTrangThai::SAP_DIEN_RA;
            }

            return SuKienTrangThai::DA_DONG_DANG_KY;
        })->withoutObjectCaching();
    }

    public function cauLacBo(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CauLacBo::class, 'MaCLB', 'MaCLB');
    }

    public function getURLAnhNenAttribute(?string $value): ?string
    {
        return self::normalizeStorageUrl($value);
    }

    public function setURLAnhNenAttribute(?string $value): void
    {
        $this->attributes['URLAnhNen'] = self::normalizeStorageUrl($value);
    }
}
