<?php
/**
 * Carrier enum
 *
 * This enum represents the carriers that an exception can be related to.
 *
 * In our system, many exceptions are tied to external shipping providers.
 * Instead of using free-text strings everywhere (which leads to typos,
 * inconsistencies, and invalid values), we centralize all allowed carriers
 * in this enum.
 *
 * This ensures:
 * - only known carriers are used
 * - consistent values across database, AI, and frontend
 * - easier validation and filtering
 */
namespace LaravelExceptionAnalyzer\Enums;
/**
 * Known carriers in the system
 */
enum Carrier: string
{
    case GLS = 'GLS';
    case DFM = 'DFM';
    case PACKETA = 'PACKETA';
    case BRING = 'BRING';
    case POSTNORD = 'POSTNORD';
    case DAO = 'DAO';
    case NONE = 'NONE';

    /**
     * toArray()
     * Some parts of the system (especially the AI schema)
     * need a plain array of allowed values instead of enum cases.
     *
     * This method converts the enum into a simple array like:
     * ['GLS', 'DFM', 'PACKETA', ...]
     *
     * This keeps the enum as the single source of truth,
     * while still being compatible with places that expect arrays.
     */
    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');

    }
}
