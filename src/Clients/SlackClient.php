<?php

/**
 * Slack Client
 *
 * This class is responsible for sending messages to Slack.
 * Slack is used as a notification channel, so repetitive exceptions can be shown quickly.
 *
 * This class does not decide when to send a Slack message - it only knows how to send one.
 */

namespace LaravelExceptionAnalyzer\Clients;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
/**
 * We use Guzzle because it is a reliable and commonly used
 * HTTP client in the Laravel ecosystem.
 */
use GuzzleHttp\Exception\GuzzleException;

class SlackClient
{
    /**
     * HTTP client used to communicate with Slack.
     */
    private ClientInterface $httpClient;

    /**
     * Create the Slack client
     * We configure a basic HTTP client with JSON headers,
     * because Slack webhooks expect JSON payloads.
     */
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

    /**
     * Sends a message payload to Slack using an incoming webhook.
     *
     * Input: an array containing the Slack message payload
     * The webhook URL is read from configuration variables.
     */
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
            /**
             * If sending to Slack fails, we don't want to crash the application.
             * The exception is therefore reported/logged instead of rethrown.
             */
            report($e);
        }
    }
}
