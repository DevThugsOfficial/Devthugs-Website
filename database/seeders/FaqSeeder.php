<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqs = [
            [
                'question' => 'Greetings and hello',
                'keywords' => 'hi, hello, hey, greetings',
                'answer' => 'Hi! How can I help you today?',
                'is_active' => true,
            ],
            [
                'question' => 'What are your business hours?',
                'keywords' => 'business hours, operating hours, when open',
                'answer' => 'Our business hours are Monday to Friday, 9 AM - 6 PM EST.',
                'is_active' => true,
            ],
            [
                'question' => 'Where are you located?',
                'keywords' => 'location, address, office',
                'answer' => 'We are a remote-first company operating globally.',
                'is_active' => true,
            ],
            [
                'question' => 'How can I contact support?',
                'keywords' => 'contact, support, help, email, phone',
                'answer' => 'You can contact our support team at support@devthugs.com or call +1 (555) 123-4567.',
                'is_active' => true,
            ],
            [
                'question' => 'What services do you offer?',
                'keywords' => 'services, what do you do, offerings',
                'answer' => 'We offer web development, mobile app development, UI/UX design, and digital marketing services.',
                'is_active' => true,
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::updateOrCreate(
                ['question' => $faq['question']],
                $faq
            );
        }
    }
}
