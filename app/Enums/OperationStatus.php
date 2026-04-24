<?php

namespace App\Enums;

enum OperationStatus: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'В очереди',
            self::COMPLETED => 'Проведена',
            self::REJECTED => 'Отклонена',
        };
    }
}
