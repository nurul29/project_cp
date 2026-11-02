<?php

namespace App\Enums;

enum AccountRequestStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Resolved = 'resolved';
    case Rejected = 'rejected';

    public static function labels(): array
    {
        return array_combine(
            array_map(fn (self $status) => $status->value, self::cases()),
            array_map(fn (self $status) => $status->label(), self::cases()),
        );
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu',
            self::InProgress => 'Ditindaklanjuti',
            self::Resolved => 'Selesai',
            self::Rejected => 'Ditolak',
        };
    }
}
