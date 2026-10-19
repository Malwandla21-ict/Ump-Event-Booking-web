<?php

use App\Core\Env;

return [
    'name' => Env::get('APP_NAME', 'UMP Events'),
    'env' => Env::get('APP_ENV', 'production'),
    'debug' => (bool) Env::get('APP_DEBUG', false),
    // Leave empty to auto-detect (works for both `php -S` and XAMPP sub-folders).
    'url' => rtrim((string) Env::get('APP_URL', ''), '/'),
    'timezone' => Env::get('APP_TIMEZONE', 'Africa/Johannesburg'),

    'session' => [
        'name' => 'ump_session',
        'idle_timeout' => 60 * 60 * 2, // seconds
    ],

    'auth' => [
        'max_login_attempts' => 5,
        'lockout_minutes' => 15,
        'password_min_length' => 8,
    ],

    'booking' => [
        'max_tickets_per_booking' => 4,
    ],

    'uploads' => [
        'max_bytes' => 2 * 1024 * 1024,
        'dir' => 'uploads/events', // relative to /public
        'mimes' => [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ],
    ],

    'events_per_page' => 9,

    // Category key => [label, icon]
    'categories' => [
        'academic' => ['Academic', 'graduation-cap'],
        'career' => ['Career', 'briefcase'],
        'culture' => ['Culture', 'palette'],
        'music' => ['Music', 'music'],
        'sports' => ['Sports', 'trophy'],
        'tech' => ['Tech', 'cpu'],
        'social' => ['Social', 'party-popper'],
    ],
];
