<?php

namespace App\Enums;

enum SuKienPhanLoaiNoiBo: string
{
    case SINH_HOAT_DINH_KY = 'SINH_HOAT_DINH_KY'; // Sinh hoạt định kỳ
    case SU_KIEN_NOI_BO = 'SU_KIEN_NOI_BO';       // Sự kiện nội bộ

    public function label(): string
    {
        return match ($this) {
            self::SINH_HOAT_DINH_KY => 'Sinh hoạt định kỳ',
            self::SU_KIEN_NOI_BO => 'Sự kiện nội bộ',
        };
    }

    public static function labels(): array
    {
        return [
            self::SINH_HOAT_DINH_KY->value => 'Sinh hoạt định kỳ',
            self::SU_KIEN_NOI_BO->value => 'Sự kiện nội bộ',
        ];
    }
}
