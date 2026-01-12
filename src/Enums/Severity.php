<?php
/**
 * Severity Enum
 *
 * This enum represents how severe an exception or recurring problem is.
 *
 * By using an enum instead of free-text values,
 * we guarantee that severity is always one of a known set.
 */

namespace LaravelExceptionAnalyzer\Enums;

enum Severity: string
{
    case LOW = 'LOW';
    case MEDIUM = 'MEDIUM';
    case HIGH = 'HIGH';

    case CRITICAL = 'CRITICAL';

    /**
     * ToArray()
     *
     * Some parts of the system need severity values as a plain array instead of enum cases.
     *
     * This method converts the enum into:
     * ['LOW', 'MEDIUM', 'HIGH', 'CRITICAL']
     *
     * This keeps severity values consistent everywhere.
     */
    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}
