<?php

namespace LaravelExceptionAnalyzer\Controller;

use LaravelExceptionAnalyzer\Clients\SlackClient;
use LaravelExceptionAnalyzer\Models\RepetitiveExceptionModel;

class SlackController
{
    private static function transformToPayload(string $exceptionTextForSlack, string $aiTextForSlack): array
    {
        // #TODO finde ud af hvad der skal sendes til slack
            return [
                'blocks' => [
                    [
                        'type' => 'section',
                        'text' => [
                            'type' => 'mrkdwn',
                            'text' => $exceptionTextForSlack,
                        ],
                    ],
                    [
                        'type' => 'section',
                        'text' => [
                            'type' => 'mrkdwn',
                            'text' => $aiTextForSlack,
                        ],
                    ],
                ],
            ];
    }
    private static function transformRepetitiveToPayload(RepetitiveExceptionModel $exception): array
    {
        return [
            'blocks' => [
                // Bold label above header
                [
                    'type' => 'section',
                    'text' => [
                        'type' => 'mrkdwn',
                        'text' => "*Short message:*",
                    ],
                ],
                // Header block (big text)
                [
                    'type' => 'header',
                    'text' => [
                        'type' => 'plain_text',
                        'text' => $exception->short_error_message,
                    ],
                ],
                // Details section
                [
                    'type' => 'section',
                    'text' => [
                        'type' => 'mrkdwn',
                        'text' => "*Details:*\n{$exception->detailed_error_message}\n\n" .
                            "*Severity:* " . self::getSeverityEmoji($exception->severity) . " {$exception->severity}\n" .
                            "*Internal:* " . ($exception->is_internal ? 'Yes' : 'No') . "\n" .
                            "*Carrier:* {$exception->carrier}\n" .
                            "*Occurrences:* {$exception->occurrence_count}\n" .
                            "*Solved:* " . ($exception->is_solved ? 'Yes' : 'No') . "\n" .
                            "*Last updated:* {$exception->updated_at}",
                    ],
                ],
            ],
        ];

    }


    private static function getSeverityEmoji(string $severity): string
    {
        return match(strtoupper($severity)) {
            'LOW' => '🟢',
            'MEDIUM' => '🟡',
            'HIGH' => '🟠',
            'CRITICAL' => '🔴',
            default => '⚪',
        };
    }

    public function sendMessageToSlack(string $laravelExceptionAnalyzed = "no text yet", string $aiText = "no ai text yet"): void
    {
        // Payload skal nok ændres ift. hvad der skal sendes med, dette er blot et udkast
        $payload = self::transformToPayload($laravelExceptionAnalyzed, $aiText);


        $client = app(SlackClient::class);

        $client->sendMessageToSlack($payload);
    }
    public function sendRepetitiveExceptionToSlack(RepetitiveExceptionModel $exception): void
    {
        $payload = self::transformRepetitiveToPayload($exception);

        $client = app(SlackClient::class);
        $client->sendMessageToSlack($payload);
    }

}
