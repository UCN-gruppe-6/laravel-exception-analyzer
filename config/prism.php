<?php

/**
 * Prism
 *
 * This file defines how our application talks to different AI providers.
 *
 * Instead of hardcoding API keys, URLs, or provider-specific settings,
 * everything is collected here and controlled through environment variables.
 *
 * This makes it easy to:
 * - switch AI providers without changing code
 * - enable or disable features per environment
 * - keep secrets out of the repository
 *
 * In our system, this configuration supports the part of the
 * exception pipeline where exceptions can be analyzed or classified by AI.
 */

return [
    /**
     * Prism server settings
     */
    'prism_server' => [
        // The middleware that will be applied to the Prism Server routes.
        'middleware' => [],
        'enabled' => env('PRISM_SERVER_ENABLED', false),
    ],
    /**
     * AI provider
     *
     * Each provider listed here represents a possible AI backend that the system can use.
     * Only the providers that have valid API keys configured will actually be usable.
     *
     * This setup allows the system to remain provider-agnostic:
     * switching provider does not require code changes, only configuration changes.
     */
    'providers' => [
        'openai' => [
            'url' => env('OPENAI_URL', 'https://api.openai.com/v1'),
            'api_key' => env('OPENAI_API_KEY', ''),
            'organization' => env('OPENAI_ORGANIZATION', null),
            'project' => env('OPENAI_PROJECT', null),
        ],
        /** Claude */
        'anthropic' => [
            'api_key' => env('ANTHROPIC_API_KEY', ''),
            'version' => env('ANTHROPIC_API_VERSION', '2023-06-01'),
            'url' => env('ANTHROPIC_URL', 'https://api.anthropic.com/v1'),
            'default_thinking_budget' => env('ANTHROPIC_DEFAULT_THINKING_BUDGET', 1024),
            // Include beta strings as a comma separated list.
            'anthropic_beta' => env('ANTHROPIC_BETA', null),
        ],
        'ollama' => [
            'url' => env('OLLAMA_URL', 'http://localhost:11434'),
        ],
        'mistral' => [
            'api_key' => env('MISTRAL_API_KEY', ''),
            'url' => env('MISTRAL_URL', 'https://api.mistral.ai/v1'),
        ],
        'groq' => [
            'api_key' => env('GROQ_API_KEY', ''),
            'url' => env('GROQ_URL', 'https://api.groq.com/openai/v1'),
        ],
        'xai' => [
            'api_key' => env('XAI_API_KEY', ''),
            'url' => env('XAI_URL', 'https://api.x.ai/v1'),
        ],
        'gemini' => [
            'api_key' => env('GEMINI_API_KEY', ''),
            'url' => env('GEMINI_URL', 'https://generativelanguage.googleapis.com/v1beta/models'),
        ],
        'deepseek' => [
            'api_key' => env('DEEPSEEK_API_KEY', ''),
            'url' => env('DEEPSEEK_URL', 'https://api.deepseek.com/v1'),
        ],
        'elevenlabs' => [
            'api_key' => env('ELEVENLABS_API_KEY', ''),
            'url' => env('ELEVENLABS_URL', 'https://api.elevenlabs.io/v1/'),
        ],
        'voyageai' => [
            'api_key' => env('VOYAGEAI_API_KEY', ''),
            'url' => env('VOYAGEAI_URL', 'https://api.voyageai.com/v1'),
        ],
        'openrouter' => [
            'api_key' => env('OPENROUTER_API_KEY', ''),
            'url' => env('OPENROUTER_URL', 'https://openrouter.ai/api/v1'),
            'site' => [
                'http_referer' => env('OPENROUTER_SITE_HTTP_REFERER', null),
                'x_title' => env('OPENROUTER_SITE_X_TITLE', null),
            ],
        ],
    ],
];
