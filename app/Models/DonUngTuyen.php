<?php

namespace App\Models;

use App\Enums\DonUngTrangThai;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DonUngTuyen extends Model
{
    protected $table = 'don_ung_tuyens';

    protected $primaryKey = 'MaDon';

    public $timestamps = false;

    protected $fillable = [
        'MaViTri',
        'MaNguoiUngTuyen',
        'NoiDung',
        'URLHoSo',
        'TrangThai',
        'MaNguoiXetDuyet',
        'GhiChu',
        'NgayNopDon',
        'NgayCapNhat',
    ];

    protected $casts = [
        'TrangThai' => DonUngTrangThai::class,
        'NgayNopDon' => 'datetime',
        'NgayCapNhat' => 'datetime',
        'MaNguoiUngTuyen' => 'integer',
        'MaNguoiXetDuyet' => 'integer',
        'MaViTri' => 'integer',
    ];

    public function viTri(): BelongsTo
    {
        return $this->belongsTo(ViTriTuyen::class, 'MaViTri', 'MaViTri');
    }

    public function nguoiUngTuyen(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'MaNguoiUngTuyen', 'MaNguoiDung');
    }

    public function nguoiXetDuyet(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'MaNguoiXetDuyet', 'MaNguoiDung');
    }
}
