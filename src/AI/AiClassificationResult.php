<?php


namespace NikolajVE\LaravelExceptionAnalyzer\AI;

/**
 * This class represent the AI classification result.
 *
 * When the external AI service analyzes an exception, it returns structured
 * information such as: category, source, severity, and status_message.
 *
 * This class provides a strongly typed and immutable structure for that data,
 * ensuring consistency across the system whenever AI results are processed.
 */
class AiClassificationResult
{
    /**
     * Creates an immutable (readonly) classification result object.
     */
    public function __construct(
        public readonly string $category,
        public readonly string $source,
        public readonly string $severity,
        public readonly string $statusMessage,
    ) {}


    /**
     * Factory method that builds an AiClassificationResult from an associative array.
     *
     * Typically used when converting the JSON response returned by the AI service.
     * If expected fields are missing, reasonable fallback defaults are applied.
     *
     * @param array $data  An associative array parsed from the AI JSON response.
     * @return self        A fully constructed AiClassificationResult instance.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            category: $data['category'] ?? 'unknown',
            source: $data['source'] ?? 'unknown',
            severity: $data['severity'] ?? 'unknown',
            statusMessage: $data['status_message'] ?? 'No status message',
        );
    }


    /**
     * Converts data back into an array.
     *
     * Useful for logging, storing the data in a database, or forwarding it
     * to other internal components that expect an array format.
     *
     * @return array  An associative array representation of the classification result.
     */
    public function toArray(): array
    {
        return [
            'category' => $this->category,
            'source'   => $this->source,
            'severity' => $this->severity,
            'status_message' => $this->statusMessage,
        ];
    }
}
