<?php

namespace App\Services;

use App\Models\Faq;
use Illuminate\Support\Facades\Log;

class FaqService
{
    public function findMatchingFaq(string $message): ?array
    {
        try {
            $faqs = Faq::where('is_active', true)->get();

            $message = strtolower($message);
            Log::info('FAQ lookup', ['message' => $message, 'faq_count' => $faqs->count()]);

            foreach ($faqs as $faq) {
                if ($this->matchesFaq($message, $faq)) {
                    Log::info('FAQ match found', ['question' => $faq->question, 'answer' => $faq->answer]);

                    return [
                        'reply' => $faq->answer,
                        'source_type' => 'faq',
                    ];
                }
            }

            Log::info('No FAQ match found');

            return null;
        } catch (\Exception $e) {
            Log::error('FAQ service error', ['error' => $e->getMessage(), 'message' => $message]);

            return null;
        }
    }

    private function matchesFaq(string $message, Faq $faq): bool
    {
        // Check if message contains explicit keywords (highest priority)
        if ($faq->keywords) {
            $keywords = $this->extractKeywords($faq->keywords);
            if ($this->containsKeywords($message, $keywords)) {
                return true;
            }
        }

        // Check if message contains question keywords (lower priority)
        $questionWords = $this->extractKeywords($faq->question);
        if ($this->containsKeywords($message, $questionWords)) {
            return true;
        }

        return false;
    }

    private function extractKeywords(string $text): array
    {
        // Simple keyword extraction - split by spaces and remove common words
        $words = explode(' ', strtolower($text));
        $stopWords = ['what', 'is', 'the', 'a', 'an', 'how', 'do', 'i', 'can', 'you', 'tell', 'me', 'about'];

        return array_filter($words, function ($word) use ($stopWords) {
            return strlen($word) > 2 && ! in_array($word, $stopWords);
        });
    }

    private function containsKeywords(string $message, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (str_contains($message, $keyword)) {
                return true;
            }
        }

        return false;
    }
}
