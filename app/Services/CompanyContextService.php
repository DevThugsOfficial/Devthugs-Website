<?php

namespace App\Services;

use App\Models\CompanyInfo;
use App\Models\CompanyOfficer;
use Illuminate\Support\Facades\Log;

class CompanyContextService
{
    public function buildContext(): string
    {
        try {
            $companyInfos = CompanyInfo::all();
            $officers = CompanyOfficer::all();

            $context = "Company Information:\n";

            foreach ($companyInfos as $info) {
                $context .= "- {$info->key}: {$info->value}\n";
            }

            $context .= "\nCompany Officers:\n";

            foreach ($officers as $officer) {
                $context .= "- {$officer->role}: {$officer->name}";
                if ($officer->short_description) {
                    $context .= " - {$officer->short_description}";
                }
                if ($officer->email) {
                    $context .= " (Contact: {$officer->email})";
                }
                $context .= "\n";
            }

            return $context;
        } catch (\Exception $e) {
            Log::error('Company context build error', ['error' => $e->getMessage()]);

            return 'Company information is currently unavailable.';
        }
    }

    public function findCompanyAnswer(string $message): ?array
    {
        try {
            $message = strtolower($message);
            Log::info('Company lookup', ['message' => $message]);

            // Check for officer queries
            $officerAnswer = $this->findOfficerAnswer($message);
            if ($officerAnswer) {
                Log::info('Company officer answer found', $officerAnswer);

                return $officerAnswer;
            }

            // Check for company info queries
            $infoAnswer = $this->findCompanyInfoAnswer($message);
            if ($infoAnswer) {
                Log::info('Company info answer found', $infoAnswer);

                return $infoAnswer;
            }

            Log::info('No company answer found');

            return null;
        } catch (\Exception $e) {
            Log::error('Company lookup error', ['error' => $e->getMessage(), 'message' => $message]);

            return null;
        }
    }

    private function findOfficerAnswer(string $message): ?array
    {
        $roleMappings = [
            // Executive roles
            'ceo' => 'Chief Executive Officer',
            'chief executive officer' => 'Chief Executive Officer',
            'cto' => 'Chief Technology Officer',
            'chief technology officer' => 'Chief Technology Officer',
            'coo' => 'Chief Operating Officer',
            'chief operating officer' => 'Chief Operating Officer',

            // Other roles
            'technical advisor' => 'Technical Advisor',
            'finance' => 'Finance Officer',
            'finance officer' => 'Finance Officer',
            'marketing' => 'Marketing Analyst',
            'marketing analyst' => 'Marketing Analyst',
            'developer' => 'Developer',
            'developers' => 'Developer',
            'graphic designer' => 'Graphic Designer',
            'designers' => 'Graphic Designer',
            'blockchain' => 'Blockchain Officer',
            'blockchain officer' => 'Blockchain Officer',
            'product manager' => 'Product Manager',
            '3d artist' => '3D Artist',
            'patent draftsman' => 'Patent Draftsman',
            'patent illustrator' => 'Patent Illustrator',
            'administrative officer' => 'Administrative Officer',
            'post-production specialist' => 'Post-production Specialist',
        ];

        // Check for direct role matches
        foreach ($roleMappings as $keyword => $role) {
            if (str_contains($message, $keyword)) {
                $officers = CompanyOfficer::where('role', $role)->get();

                if ($officers->count() > 0) {
                    Log::info('Company role detected', [
                        'detected_role' => $role,
                        'keyword' => $keyword,
                        'officers_found' => $officers->count(),
                    ]);

                    if ($officers->count() === 1) {
                        $officer = $officers->first();
                        $answer = "{$officer->role} is {$officer->name}.";
                        if ($officer->short_description) {
                            $answer .= " {$officer->short_description}";
                        }
                    } else {
                        // Multiple officers with same role
                        $names = $officers->pluck('name')->toArray();
                        $lastName = array_pop($names);
                        $namesList = count($names) > 0 ? implode(', ', $names).', and '.$lastName : $lastName;
                        $answer = "{$role}s are {$namesList}.";
                    }

                    return [
                        'reply' => $answer,
                        'source_type' => 'company',
                    ];
                }
            }
        }

        // Handle "who is" / "who are" patterns
        if (str_contains($message, 'who is') || str_contains($message, 'who are')) {
            // Check for "who is your ceo" patterns
            foreach ($roleMappings as $keyword => $role) {
                if (str_contains($message, $keyword)) {
                    $officers = CompanyOfficer::where('role', $role)->get();

                    if ($officers->count() > 0) {
                        Log::info('Who query role detected', [
                            'detected_role' => $role,
                            'keyword' => $keyword,
                            'officers_found' => $officers->count(),
                        ]);

                        if ($officers->count() === 1) {
                            $officer = $officers->first();
                            $answer = "{$officer->role} is {$officer->name}.";
                            if ($officer->short_description) {
                                $answer .= " {$officer->short_description}";
                            }
                        } else {
                            $names = $officers->pluck('name')->toArray();
                            $lastName = array_pop($names);
                            $namesList = count($names) > 0 ? implode(', ', $names).', and '.$lastName : $lastName;
                            $answer = "{$role}s are {$namesList}.";
                        }

                        return [
                            'reply' => $answer,
                            'source_type' => 'company',
                        ];
                    }
                }
            }
        }

        // Handle "who handles" patterns
        if (str_contains($message, 'who handles')) {
            if (str_contains($message, 'operations')) {
                $officer = CompanyOfficer::where('role', 'Chief Operating Officer')->first();
                if ($officer) {
                    Log::info('Who handles operations detected', ['officer' => $officer->name]);

                    return [
                        'reply' => "Operations are handled by {$officer->name}, our {$officer->role}.",
                        'source_type' => 'company',
                    ];
                }
            }
            if (str_contains($message, 'finance')) {
                $officer = CompanyOfficer::where('role', 'Finance Officer')->first();
                if ($officer) {
                    Log::info('Who handles finance detected', ['officer' => $officer->name]);

                    return [
                        'reply' => "Finance is handled by {$officer->name}, our {$officer->role}.",
                        'source_type' => 'company',
                    ];
                }
            }
        }

        // Handle "who leads" patterns
        if (str_contains($message, 'who leads')) {
            if (str_contains($message, 'company')) {
                $officer = CompanyOfficer::where('role', 'Chief Executive Officer')->first();
                if ($officer) {
                    Log::info('Who leads company detected', ['officer' => $officer->name]);

                    return [
                        'reply' => "The company is led by {$officer->name}, our {$officer->role}.",
                        'source_type' => 'company',
                    ];
                }
            }
            if (str_contains($message, 'technology') || str_contains($message, 'technical')) {
                $officer = CompanyOfficer::where('role', 'Chief Technology Officer')->first();
                if ($officer) {
                    Log::info('Who leads technology detected', ['officer' => $officer->name]);

                    return [
                        'reply' => "Technology is led by {$officer->name}, our {$officer->role}.",
                        'source_type' => 'company',
                    ];
                }
            }
        }

        return null;
    }

    private function findCompanyInfoAnswer(string $message): ?array
    {
        $infoMappings = [
            'location' => 'location',
            'where are you' => 'location',
            'address' => 'location',
            'business hours' => 'business_hours',
            'hours' => 'business_hours',
            'when are you open' => 'business_hours',
            'contact' => 'support_email',
            'email' => 'support_email',
            'support' => 'support_email',
            'phone' => 'support_phone',
            'services' => 'services',
            'what do you offer' => 'services',
        ];

        foreach ($infoMappings as $keyword => $key) {
            if (str_contains($message, $keyword)) {
                $info = CompanyInfo::where('key', $key)->first();
                if ($info) {
                    return [
                        'reply' => $info->value,
                        'source_type' => 'company',
                    ];
                }
            }
        }

        return null;
    }
}
