<?php

$member = static fn (string $name, string $file, string $role = 'Team Member') => [
    'name' => $name,
    'role' => $role,
    'tagline' => 'Building the future with Devthugs.',
    'photo' => "images/member_profile/{$file}",
    'skills' => ['Development', 'Collaboration', 'Innovation'],
    'projects' => [
        ['title' => 'Devthugs Platform', 'description' => 'Contributing to core product and web experiences.'],
    ],
];

return [
    [
        'name' => 'Tuga',
        'role' => 'Technical Advisor',
        'photo' => 'images/member_profile/default.png',
        'tagline' => 'Guiding Devthugs with experience, clarity, and vision.',
        'skills' => ['Mentorship', 'Strategy', 'Leadership'],
        'projects' => [
            ['title' => 'Team growth', 'description' => 'Supporting the team through product direction and professional development.'],
        ],
    ],
    $member('Carl Cedrik', 'cael.png', 'Chief Executive Officer'),
    $member('Jhon Lloyd', 'cape.png', 'Chief Technology Officer'),
    $member('Donard', 'donard.png', 'Chief Operating Officer'),
    $member('Shane', 'shane.png', 'Administrative Officer'),
    $member('Justine', 'justine.png', 'Finance Officer'),
    $member('Ladyjane', 'bayang.png', 'Patent Draftsman'),
    $member('Nerry', 'nerry.png', 'Patent Illustrator'),
    $member('Lovely', 'lovely.png', 'Marketing Analyst'),
    $member('Vincent', 'vincent.png', 'Graphic Designer'),
    $member('Zyrone', 'zyrone.png', 'Post-production Specialist'),

    $member('Laurence', 'laur.png', 'Product Manager'),
    $member('Delan', 'delan.png', '3D Artist'),
    $member('Reyjhon', 'reyjhon.png', 'Developer'),
    $member('Justine', 'Vasquez.png', 'Developer'),
    $member('Ryan', 'ryan.png', 'Developer'),
    $member('Aljon', 'aljon.png', 'Marketing Analyst'),
    $member('J Karl', 'jkarl.png', 'Graphic Designer'),
    $member('Benigno', 'benigno.png', 'Blockchain Officer'),
    
    
    
];
