<?php

namespace App\Models;

use App\Enums\NguoiDungTrangThai;
use App\Enums\NguoiDungVaiTro;
use App\Traits\NormalizesStorageUrls;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class NguoiDung extends Authenticatable
{
    use Notifiable, NormalizesStorageUrls;

    protected $table = 'nguoi_dungs';

    protected $primaryKey = 'MaNguoiDung';

    public $timestamps = false;

    protected $fillable = [
        'TenTaiKhoan',
        'MatKhauHash',
        'MSSV',
        'HoTen',
        'SDT',
        'Email',
        'NgaySinh',
        'URLAnhDaiDien',
        'Khoa',
        'Nganh',
        'KhoaHoc',
        'VaiTro',
        'TrangThai',
    ];

    protected $hidden = ['MatKhauHash'];

    protected function casts(): array
    {
        return [
            'NgaySinh' => 'date',
            'VaiTro' => NguoiDungVaiTro::class,
            'TrangThai' => NguoiDungTrangThai::class,
        ];
    }

    public function getAuthPasswordName(): string
    {
        return 'MatKhauHash';
    }

    public function getAuthPassword(): string
    {
        return $this->MatKhauHash;
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(ThanhVienCLB::class, 'MaNguoiDung', 'MaNguoiDung');
    }

    public function clubs(): BelongsToMany
    {
        return $this->belongsToMany(
            CauLacBo::class,
            'thanh_vien_c_l_b_s',
            'MaNguoiDung',
            'MaCLB',
            'MaNguoiDung',
            'MaCLB',
        )->withPivot(['MaThanhVien', 'ChucVu', 'NgayThamGia', 'TrangThai'])->withTimestamps();
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(DangKy::class, 'MaNguoiDung', 'MaNguoiDung');
    }

    public function getURLAnhDaiDienAttribute(?string $value): ?string
    {
        return self::normalizeStorageUrl($value);
    }

    public function setURLAnhDaiDienAttribute(?string $value): void
    {
        $this->attributes['URLAnhDaiDien'] = self::normalizeStorageUrl($value);
    }
}
