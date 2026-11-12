<?php

namespace App\Enums;

enum NguoiDungTrangThai: string
{
    case HOAT_DONG = 'HOAT_DONG';             // Đang hoạt động
    case KHONG_HOAT_DONG = 'KHONG_HOAT_DONG'; // Không hoạt động

    public function label(): string
    {
        return match ($this) {
            self::HOAT_DONG => 'Đang hoạt động',
            self::KHONG_HOAT_DONG => 'Không hoạt động',
        };
    }

    public static function labels(): array
    {
        return [
            self::HOAT_DONG->value => 'Đang hoạt động',
            self::KHONG_HOAT_DONG->value => 'Không hoạt động',
        ];
    }
}
