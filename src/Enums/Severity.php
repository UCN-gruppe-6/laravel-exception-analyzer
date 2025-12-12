<?php

namespace LaravelExceptionAnalyzer\Enums;

enum Severity: string
{
    case LOW = 'LOW';
    case MEDIUM = 'MEDIUM';
    case HIGH = 'HIGH';

    case CRITICAL = 'CRITICAL';

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}
