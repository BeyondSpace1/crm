<?php
return [
    'db' => [
        'dsn' => 'mysql:host=127.0.0.1;port=3306;dbname=crm;charset=utf8mb4',
        'user' => 'crm-user',
        'pass' => '7QLGR5vAxq4*(_Hg',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    ],
    'page_size' => 10,
    'app' => [
        'name' => 'CRM RBAC System',
        'version' => '1.0.0',
        'environment' => 'development'
    ],
    'security' => [
        'session_lifetime' => 86400, // 24 hours
        'csrf_token_name' => '_token'
    ]
];
