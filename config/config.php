<?php
return [
    'app' => [
        'name' => 'ABRM Management',
        'base_url' => getenv('APP_BASE_URL') ?: 'http://localhost',
        'session_name' => 'abrm_session',
        'csrf_token_name' => '_abrm_csrf',
        'lockout_threshold' => 5,
        'lockout_minutes' => 15,
    ],
    'database' => [
        'dsn' => getenv('DB_DSN') ?: 'mysql:host=localhost;dbname=abrm;charset=utf8mb4',
        'user' => getenv('DB_USER') ?: 'abrm',
        'password' => getenv('DB_PASS') ?: 'secret',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ],
    ],
    'minio' => [
        'endpoint' => getenv('MINIO_ENDPOINT') ?: 'http://127.0.0.1:9000',
        'key' => getenv('MINIO_KEY') ?: 'minioadmin',
        'secret' => getenv('MINIO_SECRET') ?: 'minioadmin',
        'region' => getenv('MINIO_REGION') ?: 'us-east-1',
        'bucket' => getenv('MINIO_BUCKET') ?: 'abrm-files',
    ],
];
