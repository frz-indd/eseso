<?php
declare(strict_types=1);

return [
    'users' => [
        // username => password
        'demo' => 'demo123',
    ],
    'clients' => [
        ...require __DIR__ . '/../shared/sso_clients.php',
    ],
    'ttl_seconds' => [
        'code' => 120,
        'token' => 3600,
    ],
];
