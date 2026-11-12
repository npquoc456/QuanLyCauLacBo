<?php

namespace App\Enums;

enum CauLacBoTrangThai: string
{
    case DANG_HOAT_DONG = 'DANG_HOAT_DONG'; // Đang hoạt động
    case HOAT_DONG = 'HOAT_DONG';           // Đang hoạt động
    case SAP_DIEN_RA = 'SAP_DIEN_RA';       // Sắp diễn ra
    case TAM_DUNG = 'TAM_DUNG';             // Tạm dừng

    public function label(): string
    {
        return match ($this) {
            self::DANG_HOAT_DONG, self::HOAT_DONG => 'Đang hoạt động',
            self::SAP_DIEN_RA => 'Sắp diễn ra',
            self::TAM_DUNG => 'Tạm dừng',
        };
    }

    public static function labels(): array
    {
        return [
            self::DANG_HOAT_DONG->value => 'Đang hoạt động',
            self::HOAT_DONG->value => 'Đang hoạt động',
            self::SAP_DIEN_RA->value => 'Sắp diễn ra',
            self::TAM_DUNG->value => 'Tạm dừng',
        ];
    }
}
