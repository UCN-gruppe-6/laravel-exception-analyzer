<?php

namespace LaravelExceptionAnalyzer\Clients;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

class SlackClient
{
    private ClientInterface $httpClient;

    public function __construct()
    {
        $this->httpClient = new Client(
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]
        );
    }
    public function sendMessageToSlack(array $payload): void
    {
        try {

           $this->httpClient->post(
                config('laravel-exception-analyzer.SLACK_WEBHOOK_URL',
                env('LEA_SLACK_WEBHOOK_URL')),
                    [
                        'headers' => [
                            'Content-Type' => 'application/json',
                        ],
                        'json' => $payload
                    ]
            );

        } catch (GuzzleException $e) {

            report($e);
        }
    }
}
