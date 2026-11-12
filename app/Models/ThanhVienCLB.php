<?php

namespace App\Models;

use App\Enums\ThanhVienTrangThai;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThanhVienCLB extends Model
{
    protected $table = 'thanh_vien_c_l_b_s';

    protected $primaryKey = 'MaThanhVien';

    public $timestamps = true;

    protected $fillable = [
        'MaCLB',
        'MaNguoiDung',
        'ChucVu',
        'NgayThamGia',
        'TrangThai',
    ];

    protected function casts(): array
    {
        return [
            'MaCLB' => 'integer',
            'MaNguoiDung' => 'integer',
            'NgayThamGia' => 'datetime',
            'TrangThai' => ThanhVienTrangThai::class,
        ];
    }

    public function cauLacBo(): BelongsTo
    {
        return $this->belongsTo(CauLacBo::class, 'MaCLB', 'MaCLB');
    }

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'MaNguoiDung', 'MaNguoiDung');
    }
}
