<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiChatService
{
    public function getAiResponse(string $message, string $context): ?array
    {
        if (! config('services.ai_chat.enabled', false)) {
            return null;
        }

        $provider = config('services.ai_chat.provider', 'ollama');
        $baseUrl = config('services.ai_chat.base_url');
        $model = config('services.ai_chat.model');
        $apiKey = config('services.ai_chat.api_key');
        $timeout = config('services.ai_chat.timeout', 60);

        if (! $baseUrl || ! $model) {
            Log::warning('AI Chat service not properly configured');

            return null;
        }

        $systemPrompt = $this->buildSystemPrompt($context);

        try {
            $httpClient = Http::timeout($timeout);

            // Add API key if provided (for providers that require it)
            if ($apiKey) {
                $httpClient = $httpClient->withHeaders([
                    'Authorization' => "Bearer {$apiKey}",
                ]);
            }

            $response = $httpClient->post("{$baseUrl}/chat/completions", [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $message],
                ],
                'temperature' => 0.1, // Low temperature for consistent responses
                'max_tokens' => 500,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $reply = $data['choices'][0]['message']['content'] ?? null;

                if ($reply) {
                    Log::info('AI response received successfully');

                    return [
                        'reply' => trim($reply),
                        'source_type' => 'ai',
                    ];
                } else {
                    Log::warning('AI response successful but no content in response', ['data' => $data]);
                }
            } else {
                Log::error('AI Chat API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'url' => "{$baseUrl}/chat/completions",
                    'headers' => $response->headers(),
                ]);
            }
        } catch (ConnectionException $e) {
            Log::error('AI Chat connection error', ['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            Log::error('AI Chat unexpected error', ['error' => $e->getMessage()]);
        }

        return null;
    }

    private function buildSystemPrompt(string $context): string
    {
        return "You are a helpful customer support chatbot for our company. Answer questions ONLY using the provided company information below. If the information is not available in the context, say 'Sorry, I do not have that information right now. Please contact support.'

Company Context:
{$context}

Instructions:
- Answer only from the provided context
- Do not guess or invent information
- Do not invent people, services, prices, or policies
- Keep responses short and helpful
- Be customer-friendly but stick to facts";
    }
}
