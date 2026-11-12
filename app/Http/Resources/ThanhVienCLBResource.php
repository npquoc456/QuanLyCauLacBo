<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThanhVienCLBResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'MaThanhVien' => $this->MaThanhVien,
            'MaCLB' => $this->MaCLB,
            'MaNguoiDung' => $this->MaNguoiDung,
            'ChucVu' => $this->ChucVu,
            'NgayThamGia' => $this->NgayThamGia,
            'TrangThai' => $this->TrangThai,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
