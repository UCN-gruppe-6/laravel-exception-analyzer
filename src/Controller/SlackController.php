<?php

namespace LaravelExceptionAnalyzer\Controller;

use Illuminate\Support\Facades\Http;
use LaravelExceptionAnalyzer\Clients\SlackClient;

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

    public function sendMessageToSlack(string $laravelExceptionAnalyzed = "no text yet", string $aiText = "no ai text yet"): void
    {
        // Payload skal nok ændres ift. hvad der skal sendes med, dette er blot et udkast
        $payload = self::transformToPayload($laravelExceptionAnalyzed, $aiText);

        $client = app(SlackClient::class);

        $client->sendMessageToSlack($payload);
    }

}
