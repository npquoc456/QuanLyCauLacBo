<?php

namespace App\Enums;

enum ThanhVienTrangThai: string
{
    case DANG_HOAT_DONG = 'DANG_HOAT_DONG'; // Đang hoạt động
    case TAM_NGHI = 'TAM_NGHI';             // Tạm nghỉ
    case DA_ROI_CLB = 'DA_ROI_CLB';         // Đã rời CLB
    case CHO_PHE_DUYET = 'CHO_PHE_DUYET';   // Chờ phê duyệt

    public function label(): string
    {
        return match ($this) {
            self::DANG_HOAT_DONG => 'Đang hoạt động',
            self::TAM_NGHI => 'Tạm nghỉ',
            self::DA_ROI_CLB => 'Đã rời CLB',
            self::CHO_PHE_DUYET => 'Chờ phê duyệt',
        };
    }

    public static function labels(): array
    {
        return [
            self::DANG_HOAT_DONG->value => 'Đang hoạt động',
            self::TAM_NGHI->value => 'Tạm nghỉ',
            self::DA_ROI_CLB->value => 'Đã rời CLB',
            self::CHO_PHE_DUYET->value => 'Chờ phê duyệt',
        ];
    }
}
