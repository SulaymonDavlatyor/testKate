<?php

namespace App\Enum;

class GameType
{
    public const DIVISION_A = 'divisionA';
    public const DIVISION_B = 'divisionB';
    public const PLAYOFF = 'playoff';

    public static function getValues(): array
    {
        return [
            self::DIVISION_A,
            self::DIVISION_B,
            self::PLAYOFF,
        ];
    }
}
