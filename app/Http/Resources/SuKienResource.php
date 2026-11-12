<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SuKienResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'ma_su_kien' => $this->MaSuKien,
            'ten_su_kien' => $this->TenSuKien,
            'mo_ta' => $this->MoTa,
            'url_anh_nen' => $this->URLAnhNen,
            'dia_diem' => $this->DiaDiem,
            'so_luong_toi_da' => $this->SoLuongToiDa,
            'thoi_gian_mo_dang_ky' => $this->ThoiGianMoDangKy?->format('Y-m-d H:i:s'),
            'thoi_gian_dong_dang_ky' => $this->ThoiGianDongDangKy?->format('Y-m-d H:i:s'),
            'thoi_gian_bat_dau' => $this->ThoiGianBatDau?->format('Y-m-d H:i:s'),
            'thoi_gian_ket_thuc' => $this->ThoiGianKetThuc?->format('Y-m-d H:i:s'),
            'trang_thai' => $this->TrangThai?->value,
            'loai_su_kien' => $this->LoaiSuKien?->value,
            'phan_loai_noi_bo' => $this->PhanLoaiNoiBo?->value,
            'ma_clb' => $this->MaCLB,
            'ma_danh_muc_su_kien' => $this->MaDanhMucSuKien,
        ];
    }
}
