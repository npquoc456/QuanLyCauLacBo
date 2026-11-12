<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DonUngTuyenResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'MaDon' => $this->MaDon,
            'MaViTri' => $this->MaViTri,
            'MaNguoiUngTuyen' => $this->MaNguoiUngTuyen,
            'NoiDung' => $this->NoiDung,
            'URLHoSo' => $this->URLHoSo,
            'TrangThai' => $this->TrangThai,
            'MaNguoiXetDuyet' => $this->MaNguoiXetDuyet,
            'GhiChu' => $this->GhiChu,
            'NgayNopDon' => $this->NgayNopDon,
            'NgayCapNhat' => $this->NgayCapNhat,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
