<?php
    /**
     * Slack Controller
     *
     * This controller is the Slack message builder + sender for our system.
     * This controller decides what the Slack message should look like
     * SlackClient is only responsible for actually sending it
     */

namespace LaravelExceptionAnalyzer\Controller;

use LaravelExceptionAnalyzer\Clients\SlackClient;
use LaravelExceptionAnalyzer\Models\RepetitiveExceptionModel;

class SlackController
{
    /**
     * Transform to payload
     *
     * Slack expects messages in a specific JSON structure (payload).
     * In particular, we use Slack "blocks" so the message can be nicely formatted.
     * This method converts our plain text into the Slack payload format.
     *
     * We keep this as a separate method because:
     * - formatting should be reusable
     * - it's easier to change the Slack layout in one place later
     * - it keeps sendMessageToSlack() very simple
     */
    private static function transformToPayload(string $exceptionTextForSlack, string $aiTextForSlack): array
    {
        // #TODO finde ud af hvad der skal sendes til slack
        // Right now we send two text blocks:
        // 1) the exception summary
        // 2) the AI analysis / explanation
            return [
                'blocks' => [
                    [
                        // First section: exception text
                        'type' => 'section',
                        'text' => [
                            'type' => 'mrkdwn',
                            'text' => $exceptionTextForSlack,
                        ],
                    ],
                    [
                        // Second section: AI text
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
                [
                    'type' => 'divider'
                ],
                [
                    'type' => 'header',
                    'text' => [
                        'type' => 'plain_text',
                        'text' => $exception->short_error_message,
                        'emoji' => true
                    ]
                ],
                [
                    'type' => 'section',
                    'fields' => [
                        [
                            'type' => 'mrkdwn',
                            'text' => '*Severity:*' . PHP_EOL . self::getSeverityEmoji($exception->severity) ." ".
                                $exception->severity,
                        ],
                        [
                            'type' => 'mrkdwn',
                            'text' => '*Internal Error:*' . PHP_EOL .
                                ($exception->is_internal ? 'Yes' : 'No')
                        ]
                    ]
                ],
                [
                    'type' => 'section',
                    'fields' => [
                        [
                            'type' => 'mrkdwn',
                            'text' => '*Carrier:*' . PHP_EOL .
                                $exception->carrier
                        ],
                        [
                            'type' => 'mrkdwn',
                            'text' => '*Error Count:*' . PHP_EOL .
                                $exception->occurrence_count
                        ]
                    ]
                ],
                [
                    'type' => 'section',
                    'text' => [
                        'type' => 'mrkdwn',
                        'text' => '*Detailed Error Message:*' . PHP_EOL .
                            $exception->detailed_error_message
                    ]
                ],
                [
                    'type' => 'context',
                    'elements' => [
                        [
                            'type' => 'plain_text',
                            'text' => $exception->cfl,
                            'emoji' => true
                        ]
                    ]
                ]
            ]
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

    /**
     * Send message to Slack
     *
     * This is the main method other code should call when it wants to send something to Slack.
     * It:
     * 1) builds the Slack payload (format Slack requires)
     * 2) resolves SlackClient
     * 3) sends the payload
     */
    public function sendMessageToSlack(string $laravelExceptionAnalyzed = "no text yet", string $aiText = "no ai text yet"): void
    {
        // Payload skal nok ændres ift. hvad der skal sendes med, dette er blot et udkast
        $payload = self::transformToPayload($laravelExceptionAnalyzed, $aiText);


        $client = app(SlackClient::class);

        $client->sendMessageToSlack($payload);
    }
    public function sendRepetitiveExceptionToSlack(RepetitiveExceptionModel $exception): void
    {
        // Build the Slack payload.
        $payload = self::transformRepetitiveToPayload($exception);
        // SlackClient does the actual HTTP request to Slack
        $client = app(SlackClient::class);
        // Send the message
        $client->sendMessageToSlack($payload);
    }

}
