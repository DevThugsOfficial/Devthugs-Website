<?php

namespace App\Services;

use App\Models\ChatSession;
use Illuminate\Support\Facades\Log;

class ChatbotService
{
    public function __construct(
        private FaqService $faqService,
        private CompanyContextService $companyContextService,
        private AiChatService $aiChatService
    ) {}

    public function processMessage(string $message, ChatSession $session): array
    {
        try {
            Log::info('Chatbot processing message', ['message' => $message, 'session_id' => $session->id]);

            // Step 0: Check for simple greetings
            $greetingResult = $this->checkForGreeting($message);
            if ($greetingResult) {
                Log::info('Using greeting response', $greetingResult);

                return $greetingResult;
            }

            // Step 1: Check FAQ
            Log::info('Checking FAQ');
            $faqResult = $this->faqService->findMatchingFaq($message);
            if ($faqResult) {
                Log::info('Using FAQ response', $faqResult);

                return $faqResult;
            }

            // Step 2: Check company knowledge
            Log::info('Checking company knowledge');
            $companyResult = $this->companyContextService->findCompanyAnswer($message);
            if ($companyResult) {
                Log::info('Using company response', $companyResult);

                return $companyResult;
            } else {
                Log::info('No company knowledge match found', ['message' => $message]);
            }

            // Step 3: AI fallback
            $aiEnabled = config('services.ai_chat.enabled', false);
            Log::info('AI enabled status', ['enabled' => $aiEnabled]);

            if ($aiEnabled) {
                Log::info('Attempting AI fallback');
                $context = $this->companyContextService->buildContext();
                $aiResult = $this->aiChatService->getAiResponse($message, $context);
                if ($aiResult) {
                    Log::info('Using AI response', $aiResult);

                    return $aiResult;
                } else {
                    Log::info('AI request failed or returned no result');
                }
            } else {
                Log::info('AI disabled, skipping AI fallback');
            }

            // Final fallback response
            Log::info('Using final fallback - no information available from any source');

            return [
                'reply' => 'Sorry, I do not have that information right now. Please contact support.',
                'source_type' => 'fallback',
            ];
        } catch (\Exception $e) {
            Log::error('Chatbot service error', ['error' => $e->getMessage(), 'message' => $message]);

            return [
                'reply' => 'Sorry, I\'m having trouble processing your request right now. Please try again later.',
                'source_type' => 'error',
            ];
        }
    }

    private function checkForGreeting(string $message): ?array
    {
        $message = strtolower(trim($message));

        $greetings = ['hi', 'hello', 'hey', 'good morning', 'good afternoon', 'good evening', 'greetings'];

        foreach ($greetings as $greeting) {
            if (str_contains($message, $greeting)) {
                Log::info('Greeting detected', ['greeting' => $greeting]);

                return [
                    'reply' => 'Hi! How can I help you today?',
                    'source_type' => 'greeting',
                ];
            }
        }

        return null;
    }
}
