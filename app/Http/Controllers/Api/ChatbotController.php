<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChatMessageRequest;
use App\Models\ChatSession;
use App\Services\ChatbotService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatbotController extends Controller
{
    public function __construct(
        private ChatbotService $chatbotService
    ) {}

    public function sendMessage(StoreChatMessageRequest $request): JsonResponse
    {
        try {
            // Test database connectivity
            try {
                DB::connection()->getPdo();
                Log::info('Database connection test passed');
            } catch (\Exception $e) {
                Log::error('Database connection test failed', ['error' => $e->getMessage()]);
                throw $e;
            }

            $validated = $request->validated();
            Log::info('Chatbot message received', [
                'message' => $validated['message'],
                'session_token' => $validated['session_token'] ?? null,
                'ip' => $request->ip(),
            ]);

            // Get or create session
            Log::info('Getting or creating session');
            try {
                $session = $this->getOrCreateSession($validated['session_token'] ?? null);
                Log::info('Session ready', ['session_id' => $session->id]);
            } catch (QueryException $e) {
                Log::error('Failed to get/create session', ['error' => $e->getMessage()]);
                // Create a temporary session object for processing
                $session = (object) [
                    'id' => 'temp_'.time(),
                    'session_token' => $validated['session_token'] ?? Str::uuid()->toString(),
                ];
                Log::info('Using temporary session for processing', ['session_id' => $session->id]);
            }

            // Save user message (don't fail if this fails)
            try {
                Log::info('Saving user message');
                $userMessage = $session->messages()->create([
                    'role' => 'user',
                    'message' => trim($validated['message']),
                ]);
                Log::info('User message saved', ['message_id' => $userMessage->id]);
            } catch (QueryException $e) {
                Log::error('Failed to save user message, continuing anyway', [
                    'error' => $e->getMessage(),
                    'session_id' => $session->id,
                ]);
            }

            // Get chatbot response
            Log::info('Getting chatbot response');
            $response = $this->chatbotService->processMessage($validated['message'], $session);
            Log::info('Chatbot response generated', ['response' => $response]);

            // Validate response structure - ensure reply and source_type exist
            if (! is_array($response) || ! isset($response['reply'])) {
                Log::error('Malformed chatbot response - missing reply key', ['response' => $response]);
                $reply = 'Sorry, I encountered an error processing your request.';
                $sourceType = 'fallback';
            } else {
                $reply = $response['reply'];
                $sourceType = $response['source_type'] ?? 'fallback';

                // Validate source_type against allowed values
                $allowedSourceTypes = ['greeting', 'faq', 'company', 'ai', 'fallback', 'error'];
                if (! in_array($sourceType, $allowedSourceTypes)) {
                    Log::warning('Invalid source_type in chatbot response', [
                        'source_type' => $sourceType,
                        'response' => $response,
                    ]);
                    $sourceType = 'fallback';
                }
            }

            // Save assistant response (don't fail if this fails)
            try {
                Log::info('Saving assistant response', [
                    'source_type' => $sourceType,
                    'has_reply' => ! empty($reply),
                ]);
                $assistantMessage = $session->messages()->create([
                    'role' => 'assistant',
                    'message' => $reply,
                    'source_type' => $sourceType,
                ]);
                Log::info('Assistant message saved', ['message_id' => $assistantMessage->id]);
            } catch (QueryException $e) {
                Log::error('Failed to save assistant message, continuing anyway', [
                    'error' => $e->getMessage(),
                    'session_id' => $session->id,
                    'source_type' => $sourceType,
                    'response' => $response,
                ]);
            }

            Log::info('Chatbot response sent successfully', [
                'session_id' => $session->id,
                'source_type' => $sourceType,
            ]);

            return response()->json([
                'success' => true,
                'reply' => $reply,
                'source_type' => $sourceType,
                'session_token' => $session->session_token,
            ]);
        } catch (QueryException $e) {
            Log::error('Database error in chatbot', [
                'error' => $e->getMessage(),
                'sql' => $e->getSql() ?? 'N/A',
                'bindings' => $e->getBindings() ?? 'N/A',
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'reply' => 'Sorry, I\'m having trouble connecting to the database right now. Please try again later.',
                'source_type' => 'error',
            ], 500);
        } catch (\Exception $e) {
            Log::error('Unexpected error in chatbot', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'reply' => 'Sorry, an unexpected error occurred. Please try again later.',
                'source_type' => 'error',
            ], 500);
        }
    }

    public function getMessages(string $token): JsonResponse
    {
        $session = ChatSession::where('session_token', $token)->firstOrFail();

        $messages = $session->messages()
            ->orderBy('created_at', 'asc')
            ->get(['role', 'message', 'source_type', 'created_at']);

        return response()->json([
            'success' => true,
            'messages' => $messages,
        ]);
    }

    private function getOrCreateSession(?string $token): ChatSession
    {
        if ($token) {
            $session = ChatSession::where('session_token', $token)->first();
            if ($session) {
                return $session;
            }
        }

        return ChatSession::create([
            'session_token' => Str::uuid()->toString(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
