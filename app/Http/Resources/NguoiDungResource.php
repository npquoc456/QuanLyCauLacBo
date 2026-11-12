<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NguoiDungResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'MaNguoiDung' => $this->MaNguoiDung,
            'TenTaiKhoan' => $this->TenTaiKhoan,
            // Không nên trả về MatKhauHash qua API để bảo mật
            'MSSV' => $this->MSSV,
            'HoTen' => $this->HoTen,
            'SDT' => $this->SDT,
            'Email' => $this->Email,
            'NgaySinh' => $this->NgaySinh,
            'URLAnhDaiDien' => $this->URLAnhDaiDien,
            'Khoa' => $this->Khoa,
            'Nganh' => $this->Nganh,
            'KhoaHoc' => $this->KhoaHoc,
            'VaiTro' => $this->VaiTro,
            'TrangThai' => $this->TrangThai,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
