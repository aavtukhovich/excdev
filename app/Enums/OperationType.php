<?php

namespace App\Enums;

enum OperationType: string
{
    case CREDIT = 'credit';
    case DEBIT = 'debit';

    public function label(): string
    {
        return match ($this) {
            self::CREDIT => 'Начисление',
            self::DEBIT => 'Списание',
        };
    }

    public function multiplier(): int
    {
        return match ($this) {
            self::CREDIT => 1,
            self::DEBIT => -1,
        };
    }
}
