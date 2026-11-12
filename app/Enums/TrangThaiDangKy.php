<?php

namespace App\Enums;

enum TrangThaiDangKy: string
{
    case CHO_XAC_NHAN = 'CHO_XAC_NHAN'; // Chờ xác nhận
    case DA_XAC_NHAN = 'DA_XAC_NHAN';   // Đã xác nhận
    case DA_CHECK_IN = 'DA_CHECK_IN';   // Đã check-in (Đã điểm danh)
    case VANG_MAT = 'VANG_MAT';         // Vắng mặt
    case HUY_DANG_KY = 'HUY_DANG_KY';   // Hủy đăng ký

    public function label(): string
    {
        return match ($this) {
            self::CHO_XAC_NHAN => 'Chờ xác nhận',
            self::DA_XAC_NHAN => 'Đã xác nhận',
            self::DA_CHECK_IN => 'Đã điểm danh',
            self::VANG_MAT => 'Vắng mặt',
            self::HUY_DANG_KY => 'Hủy đăng ký',
        };
    }

    public static function labels(): array
    {
        return [
            self::CHO_XAC_NHAN->value => 'Chờ xác nhận',
            self::DA_XAC_NHAN->value => 'Đã xác nhận',
            self::DA_CHECK_IN->value => 'Đã điểm danh',
            self::VANG_MAT->value => 'Vắng mặt',
            self::HUY_DANG_KY->value => 'Hủy đăng ký',
        ];
    }
}
