<?php

namespace LaravelExceptionAnalyzer\Enums;

enum Carrier: string
{
    case GLS = 'GLS';
    case DFM = 'DFM';
    case PACKETA = 'PACKETA';
    case BRING = 'BRING';
    case POSTNORD = 'POSTNORD';
    case DAO = 'DAO';
    case NONE = 'NONE';

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');

    }
}
