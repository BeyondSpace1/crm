<?php
declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/authService.php';

session_start();
$pdo = pdo();
$authService = new AuthService($pdo);

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get-csrf':
        $csrf = $authService->generateCsrfToken();
        echo json_encode(['csrf_token' => $csrf]);
        break;

    case 'login':
        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $authService->login($data);
        break;

    case 'logout':
        $authService->logout();
        break;

    default:
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Not Found']);
        break;
}
