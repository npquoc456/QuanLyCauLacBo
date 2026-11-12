<?php

namespace App\Enums;

enum SuKienTrangThai: string
{
    case CHUA_MO_DANG_KY = 'CHUA_MO_DANG_KY'; // Chưa mở đăng ký
    case DANG_MO_DANG_KY = 'DANG_MO_DANG_KY'; // Đang mở đăng ký
    case DA_DONG_DANG_KY = 'DA_DONG_DANG_KY'; // Đã đóng đăng ký
    case SAP_DIEN_RA = 'SAP_DIEN_RA';         // Sắp diễn ra

    public function label(): string
    {
        return match ($this) {
            self::CHUA_MO_DANG_KY => 'Chưa mở đăng ký',
            self::DANG_MO_DANG_KY => 'Đang mở đăng ký',
            self::DA_DONG_DANG_KY => 'Đã đóng đăng ký',
            self::SAP_DIEN_RA => 'Sắp diễn ra',
        };
    }

    public static function labels(): array
    {
        return [
            self::CHUA_MO_DANG_KY->value => 'Chưa mở đăng ký',
            self::DANG_MO_DANG_KY->value => 'Đang mở đăng ký',
            self::DA_DONG_DANG_KY->value => 'Đã đóng đăng ký',
            self::SAP_DIEN_RA->value => 'Sắp diễn ra',
        ];
    }
}
