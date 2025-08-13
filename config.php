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
];
