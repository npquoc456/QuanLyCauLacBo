<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CauLacBoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'ma_clb' => $this->MaCLB,
            'ten_clb' => $this->TenCLB,
            'mo_ta' => $this->MoTa,
            'email' => $this->Email,
            'sdt' => $this->SDT,
            'trang_thai' => $this->TrangThai?->value,
            'url_logo' => $this->URLLogo,
            'url_anh_nen' => $this->URLAnhNen,
            'ngay_tao' => $this->NgayTao?->format('Y-m-d H:i:s'),
            'ma_danh_muc_clb' => $this->MaDanhMucCLB,
        ];
    }
}
