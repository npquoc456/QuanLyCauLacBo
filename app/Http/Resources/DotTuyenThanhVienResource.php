<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DotTuyenThanhVienResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'MaDotTuyen' => $this->MaDotTuyen,
            'TenDotTuyen' => $this->TenDotTuyen,
            'MoTa' => $this->MoTa,
            'NgayBatDau' => $this->NgayBatDau,
            'NgayKetThuc' => $this->NgayKetThuc,
            'TrangThai' => $this->TrangThai,
            'MaCLB' => $this->MaCLB,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
