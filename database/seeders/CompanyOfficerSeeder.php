<?php

namespace Database\Seeders;

use App\Models\CompanyOfficer;
use Illuminate\Database\Seeder;

class CompanyOfficerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $officers = [
            [
                'name' => 'Virgilio Tuga Jr.',
                'role' => 'Technical Advisor',
                'short_description' => 'Technical Advisor providing strategic guidance',
                'email' => null,
            ],
            [
                'name' => 'Carl Cedrik T. Rebusora',
                'role' => 'Chief Executive Officer',
                'short_description' => 'Chief Executive Officer leading company vision and strategy',
                'email' => null,
            ],
            [
                'name' => 'Jhon Lloyd Y. Cape',
                'role' => 'Chief Technology Officer',
                'short_description' => 'Chief Technology Officer overseeing technical innovation',
                'email' => null,
            ],
            [
                'name' => 'Donard O. Osol',
                'role' => 'Chief Operating Officer',
                'short_description' => 'Chief Operating Officer managing operational excellence',
                'email' => null,
            ],
            [
                'name' => 'Shane D. Onsing',
                'role' => 'Administrative Officer',
                'short_description' => 'Administrative Officer handling administrative operations',
                'email' => null,
            ],
            [
                'name' => 'Justine Seraspe',
                'role' => 'Finance Officer',
                'short_description' => 'Finance Officer managing financial operations',
                'email' => null,
            ],
            [
                'name' => 'Ladyjane Bayang',
                'role' => 'Patent Draftsman',
                'short_description' => 'Patent Draftsman specializing in patent documentation',
                'email' => null,
            ],
            [
                'name' => 'Nerry Simacon',
                'role' => 'Patent Illustrator',
                'short_description' => 'Patent Illustrator creating patent illustrations',
                'email' => null,
            ],
            [
                'name' => 'Lovely Dela Vega',
                'role' => 'Marketing Analyst',
                'short_description' => 'Marketing Analyst analyzing market trends and strategies',
                'email' => null,
            ],
            [
                'name' => 'Vincent Napal',
                'role' => 'Graphic Designer',
                'short_description' => 'Graphic Designer creating visual content and designs',
                'email' => null,
            ],
            [
                'name' => 'Zyrone Campos',
                'role' => 'Post-production Specialist',
                'short_description' => 'Post-production Specialist handling multimedia post-production',
                'email' => null,
            ],
            [
                'name' => 'Laurence Brillantes',
                'role' => 'Product Manager',
                'short_description' => 'Product Manager overseeing product development lifecycle',
                'email' => null,
            ],
            [
                'name' => 'Delan Alvizo',
                'role' => '3D Artist',
                'short_description' => '3D Artist creating 3D models and animations',
                'email' => null,
            ],
            [
                'name' => 'Reyjhon R. Villarias',
                'role' => 'Developer',
                'short_description' => 'Developer specializing in software development',
                'email' => null,
            ],
            [
                'name' => 'Justine Vasquez',
                'role' => 'Developer',
                'short_description' => 'Developer specializing in software development',
                'email' => null,
            ],
            [
                'name' => 'Ryan Llanto',
                'role' => 'Developer',
                'short_description' => 'Developer specializing in software development',
                'email' => null,
            ],
            [
                'name' => 'Aljon De jesus',
                'role' => 'Marketing Analyst',
                'short_description' => 'Marketing Analyst analyzing market trends and strategies',
                'email' => null,
            ],
            [
                'name' => 'J Karl Meniano',
                'role' => 'Graphic Designer',
                'short_description' => 'Graphic Designer creating visual content and designs',
                'email' => null,
            ],
            [
                'name' => 'Benigno J. Jaranta',
                'role' => 'Blockchain Officer',
                'short_description' => 'Blockchain Officer specializing in blockchain technology',
                'email' => null,
            ],
        ];

        foreach ($officers as $officer) {
            CompanyOfficer::updateOrCreate(
                ['name' => $officer['name'], 'role' => $officer['role']],
                $officer
            );
        }
    }
}
