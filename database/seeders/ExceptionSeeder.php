<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use LaravelExceptionAnalyzer\Models\ExceptionModel;

class ExceptionSeeder extends Seeder
{
    // The maximum length allowed by $table->string() in your migration
    private const MAX_MESSAGE_LENGTH = 255;

    public function run()
    {
        $jsonPath = database_path('seeders/exceptions.json');

        if (!File::exists($jsonPath)) {
            Log::error("Exception data file not found at: {$jsonPath}");
            return;
        }

        $jsonContents = File::get($jsonPath);
        $exceptions = json_decode($jsonContents, true);

        if (!is_array($exceptions)) {
            Log::error("Failed to decode JSON data from: {$jsonPath}");
            return;
        }

        $count = 0;

        foreach ($exceptions as $exceptionData) {

            // --- 1. Use the new helper method to truncate the message ---
            if (isset($exceptionData['message'])) {
                $exceptionData['message'] = $this->truncateMessage($exceptionData['message']);
            }

            // --- 2. Rename 'stacktrace' (JSON key) to 'stack_trace' (DB column) ---
            if (isset($exceptionData['stacktrace']) && is_array($exceptionData['stacktrace'])) {
                // Encode the PHP stack trace array into a JSON string for the 'stack_trace' TEXT column
                $exceptionData['stack_trace'] = json_encode($exceptionData['stacktrace'], JSON_PRETTY_PRINT);

                // Remove the original 'stacktrace' key
                unset($exceptionData['stacktrace']);
            }

            // --- 3. Set default values for optional/dynamic fields ---
            $exceptionData['session_id'] = $exceptionData['session_id'] ?? Str::uuid()->toString();
            $exceptionData['created_at'] = $exceptionData['created_at'] ?? now();
            $exceptionData['user_id'] = $exceptionData['user_id'] ?? null;
            $exceptionData['user_email'] = $exceptionData['user_email'] ?? null;
            $exceptionData['level'] = $exceptionData['level'] ?? 'error';


            try {
                ExceptionModel::create($exceptionData);
                $count++;
            } catch (\Illuminate\Database\QueryException $e) {
                Log::error("Failed to insert exception: " . $exceptionData['message'], ['exception' => $e->getMessage()]);
                continue;
            }
        }

        Log::info("Exception Seeder finished. Successfully created {$count} exception records.");
    }

    // 🗜️ INNER METHOD TO TRUNCATE THE MESSAGE
    /**
     * Cuts the message string to a maximum length and appends "..." if truncated.
     *
     * @param string $message
     * @return string
     */
    private function truncateMessage(string $message): string
    {
        // Use the Laravel Str::limit helper for clean truncation
        return Str::limit($message, self::MAX_MESSAGE_LENGTH);

        /* // Alternative manual implementation using PHP standard function:
        $limit = self::MAX_MESSAGE_LENGTH;
        if (mb_strlen($message) > $limit) {
            return mb_substr($message, 0, $limit - 3) . '...';
        }
        return $message;
        */
    }
}
