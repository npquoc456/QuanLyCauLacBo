<?php

namespace App\Models;

use App\Enums\CauLacBoTrangThai;
use App\Traits\NormalizesStorageUrls;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CauLacBo extends Model
{
    use NormalizesStorageUrls;
    protected $table = 'cau_lac_bos';

    protected $primaryKey = 'MaCLB';

    public $timestamps = false;

    protected $fillable = [
        'TenCLB',
        'MoTa',
        'Email',
        'SDT',
        'TrangThai',
        'URLLogo',
        'URLAnhNen',
        'MaDanhMucCLB',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $cauLacBo): void {
            $cauLacBo->NgayTao ??= now();
        });
    }

    protected function casts(): array
    {
        return [
            'MaDanhMucCLB' => 'integer',
            'NgayTao' => 'datetime',
            'TrangThai' => CauLacBoTrangThai::class,
        ];
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(ThanhVienCLB::class, 'MaCLB', 'MaCLB');
    }

    public function dotTuyenThanhViens(): HasMany
    {
        return $this->hasMany(DotTuyenThanhVien::class, 'MaCLB', 'MaCLB');
    }

    public function suKiens(): HasMany
    {
        return $this->hasMany(SuKien::class, 'MaCLB', 'MaCLB');
    }

    public function getURLLogoAttribute(?string $value): ?string
    {
        return self::normalizeStorageUrl($value);
    }

    public function setURLLogoAttribute(?string $value): void
    {
        $this->attributes['URLLogo'] = self::normalizeStorageUrl($value);
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
