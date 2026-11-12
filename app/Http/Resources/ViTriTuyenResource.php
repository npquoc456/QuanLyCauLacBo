<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ViTriTuyenResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'MaViTri' => $this->MaViTri,
            'TenViTri' => $this->TenViTri,
            'Mota' => $this->Mota,
            'SoLuong' => $this->SoLuong,
            'MaDotTuyen' => $this->MaDotTuyen,
            'MaBan' => $this->MaBan,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
