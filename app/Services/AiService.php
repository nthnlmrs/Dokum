<?php

namespace App\Services;

use App\Models\AiConfiguration;
use Illuminate\Support\Facades\Http;
use Exception;

class AiService
{
    /**
     * Get the currently active AI Configuration
     */
    protected function getActiveConfig(): AiConfiguration
    {
        $config = AiConfiguration::where('is_active', true)->first();

        if (!$config) {
            throw new Exception('No active AI configuration found. Please set one up in Settings.');
        }

        return $config;
    }

    /**
     * Generate content based on the prompt and given text
     */
    public function generateContent(string $prompt, string $content): string
    {
        $config = $this->getActiveConfig();

        return match ($config->provider) {
            'openai' => $this->callOpenAI($config, $prompt, $content),
            'gemini' => $this->callGemini($config, $prompt, $content),
            'anthropic' => $this->callAnthropic($config, $prompt, $content),
            default => throw new Exception("AI Provider '{$config->provider}' is not supported."),
        };
    }

    protected function callOpenAI(AiConfiguration $config, string $prompt, string $content): string
    {
        $response = Http::withToken($config->api_key)
            ->timeout(120)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $config->model_name,
                'messages' => [
                    ['role' => 'system', 'content' => $prompt],
                    ['role' => 'user', 'content' => $content],
                ],
                'temperature' => 0.7,
            ]);

        if ($response->failed()) {
            throw new Exception('OpenAI API Error: ' . $response->body());
        }

        return $response->json('choices.0.message.content') ?? '';
    }

    protected function callGemini(AiConfiguration $config, string $prompt, string $content): string
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$config->model_name}:generateContent?key={$config->api_key}";

        $response = Http::timeout(120)->post($url, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt . "\n\n" . $content]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7
            ]
        ]);

        if ($response->failed()) {
            throw new Exception('Gemini API Error: ' . $response->body());
        }

        return $response->json('candidates.0.content.parts.0.text') ?? '';
    }

    protected function callAnthropic(AiConfiguration $config, string $prompt, string $content): string
    {
        $response = Http::withHeaders([
            'x-api-key' => $config->api_key,
            'anthropic-version' => '2023-06-01',
        ])
        ->timeout(120)
        ->post('https://api.anthropic.com/v1/messages', [
            'model' => $config->model_name,
            'system' => $prompt,
            'messages' => [
                ['role' => 'user', 'content' => $content],
            ],
            'max_tokens' => 4096,
            'temperature' => 0.7,
        ]);

        if ($response->failed()) {
            throw new Exception('Anthropic API Error: ' . $response->body());
        }

        return $response->json('content.0.text') ?? '';
    }
}
