<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DangKyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'MaDangKy' => $this->MaDangKy,
            'MaNguoiDung' => $this->MaNguoiDung,
            'MaSuKien' => $this->MaSuKien,
            'TrangThai' => $this->TrangThai,
            'ThoiGianCheckIn' => $this->ThoiGianCheckIn,
            'NgayDangKy' => $this->NgayDangKy,
            'MaQRToken' => $this->MaQRToken,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
