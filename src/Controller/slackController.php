<?php

namespace NikolajVE\LaravelExceptionAnalyzer\Controller;

use Illuminate\Support\Facades\Http;
class slackController
{
    public function sendMessageToSlack(string $laravelExceptionAnalyzed = "no text yet", string $aiText = "no ai text yet"): void
    {
        $webhookUrl = 'YOUR_SLACK_WEBHOOK_URL';
        $exceptionTextForSlack = $laravelExceptionAnalyzed;
        $aiTextForSlack = $aiText;


        $payloadWithColor = [
            'attachments' => [
                [
                    'color' => '#f2c744', // Warning color (yellow/orange)
                    'fallback' => 'A user updated their profile.',
                    'text' => 'A user updated their profile details in the application.' . $aiTextForSlack . $exceptionTextForSlack,
                    'title' => 'Profile Updated',
                ]
            ]
        ];
        $response = Http::post($webhookUrl, $payloadWithColor);

        if ($response->successful()) {
            echo "Colored message sent successfully!";
        }
    }

}
