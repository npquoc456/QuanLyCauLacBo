<?php

namespace App\Enums;

enum DonUngTrangThai: string
{
    case CHO_XY_LY = 'CHO_XY_LY';           // Chờ xử lý
    case DANG_XET_DUYET = 'DANG_XET_DUYET'; // Đang xét duyệt
    case DA_DUYET = 'DA_DUYET';             // Đã duyệt
    case TU_CHOI = 'TU_CHOI';               // Từ chối
    case DA_HUY = 'DA_HUY';                 // Đã hủy

    public function label(): string
    {
        return match ($this) {
            self::CHO_XY_LY => 'Chờ xử lý',
            self::DANG_XET_DUYET => 'Đang xét duyệt',
            self::DA_DUYET => 'Đã duyệt',
            self::TU_CHOI => 'Từ chối',
            self::DA_HUY => 'Đã hủy',
        };
    }

    public static function labels(): array
    {
        return [
            self::CHO_XY_LY->value => 'Chờ xử lý',
            self::DANG_XET_DUYET->value => 'Đang xét duyệt',
            self::DA_DUYET->value => 'Đã duyệt',
            self::TU_CHOI->value => 'Từ chối',
            self::DA_HUY->value => 'Đã hủy',
        ];
    }
}
