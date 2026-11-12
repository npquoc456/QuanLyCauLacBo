<?php

namespace App\Enums;

enum SuKienLoai: string
{
    case NOI_BO = 'NOI_BO';       // Nội bộ
    case CONG_KHAI = 'CONG_KHAI'; // Công khai

    public function label(): string
    {
        return match ($this) {
            self::NOI_BO => 'Nội bộ',
            self::CONG_KHAI => 'Công khai',
        };
    }

    public static function labels(): array
    {
        return [
            self::NOI_BO->value => 'Nội bộ',
            self::CONG_KHAI->value => 'Công khai',
        ];
    }
}
