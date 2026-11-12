<?php

namespace App\Enums;

enum NguoiDungVaiTro: string
{
    case USER = 'USER';   // Người dùng
    case ADMIN = 'ADMIN'; // Quản trị viên

    public function label(): string
    {
        return match ($this) {
            self::USER => 'Người dùng',
            self::ADMIN => 'Quản trị viên',
        };
    }

    public static function labels(): array
    {
        return [
            self::USER->value => 'Người dùng',
            self::ADMIN->value => 'Quản trị viên',
        ];
    }
}
