<?php

/**
 * AiClassificationResult
 *
 * When we send an exception to an AI service, the AI does not return
 * a single text response. It returns structured data such as:
 * - what category the error belongs to
 * - where it seems to originate from
 * - how severe it is
 * - and a short status or explanation
 *
 * This class exists to hold that AI response in a clear, predictable structure.
 *
 * Instead of passing around loose arrays with unknown keys,
 * we wrap the AI result in this object so the rest of the system
 * knows exactly what data is available.
 *
 * Think of this class as:
 * “the agreed format for AI results inside our system”.
 */

namespace LaravelExceptionAnalyzer\AI;

class AiClassificationResult
{
    /**
     * Create a new AI classification result.
     *
     * All properties are readonly because:
     * - the AI result should not be changed after it is received
     * - this prevents accidental modification later in the pipeline
     */
    public function __construct(
        public readonly string $category,
        public readonly string $source,
        public readonly string $severity,
        public readonly string $statusMessage,
    ) {}


    /**
     * Build an AiClassificationResult from raw array data.
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
     * Convert the AI result back into an array.
     *
     * This is useful when storing the result in the database
     * It ensures the same structure is always used.
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
