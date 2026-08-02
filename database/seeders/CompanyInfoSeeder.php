<?php

namespace Database\Seeders;

use App\Models\CompanyInfo;
use Illuminate\Database\Seeder;

class CompanyInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $infos = [
            [
                'key' => 'company_name',
                'value' => 'DevThugs',
                'description' => 'Company name',
            ],
            [
                'key' => 'location',
                'value' => 'Remote / Global',
                'description' => 'Company location',
            ],
            [
                'key' => 'business_hours',
                'value' => 'Monday to Friday, 9 AM - 6 PM EST',
                'description' => 'Business operating hours',
            ],
            [
                'key' => 'support_email',
                'value' => 'support@devthugs.com',
                'description' => 'Support contact email',
            ],
            [
                'key' => 'support_phone',
                'value' => '+1 (555) 123-4567',
                'description' => 'Support contact phone',
            ],
            [
                'key' => 'services',
                'value' => 'We offer web development, mobile app development, UI/UX design, and digital marketing services.',
                'description' => 'Company services overview',
            ],
        ];

        foreach ($infos as $info) {
            CompanyInfo::updateOrCreate(
                ['key' => $info['key']],
                $info
            );
        }
    }
}
