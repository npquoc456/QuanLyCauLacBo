<?php

namespace App\Enums;

enum DotTuyenTrangThai: string
{
    case DANG_TUYEN = 'DANG_TUYEN'; // Đang mở tuyển
    case CHUA_TUYEN = 'CHUA_TUYEN'; // Chưa mở tuyển

    public function label(): string
    {
        return match ($this) {
            self::DANG_TUYEN => 'Đang mở tuyển',
            self::CHUA_TUYEN => 'Chưa mở tuyển',
        };
    }

    public static function labels(): array
    {
        return [
            self::DANG_TUYEN->value => 'Đang mở tuyển',
            self::CHUA_TUYEN->value => 'Chưa mở tuyển',
        ];
    }
}
