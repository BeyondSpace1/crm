<?php
declare(strict_types=1);

session_start();

// Simple router
$action = $_GET['action'] ?? null;

if ($action) {
    require __DIR__ . '/Router.php';
    exit;
}

// Default landing page
require __DIR__ . '/views/landing.html';


