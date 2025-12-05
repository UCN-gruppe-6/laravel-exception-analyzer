<?php


namespace LaravelExceptionAnalyzer\AI;

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
    public function __construct(
        public readonly string $category,
        public readonly string $source,
        public readonly string $severity,
        public readonly string $statusMessage,
    ) {}


    /**
     * Method that builds an AiClassificationResult from an associative array.
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
