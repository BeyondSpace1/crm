<?php
declare(strict_types=1);

require_once __DIR__ . '/classes/database.php';
require_once __DIR__ . '/classes/AuthService.php';
require_once __DIR__ . '/classes/controllers/ContactController.php';
require_once __DIR__ . '/classes/controllers/AuditController.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$action = $_GET['action'] ?? 'landing';
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

function json(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

function requireLoginAjax(): void {
    if (empty($_SESSION['user'])) {
        json(['success' => false, 'message' => 'Session expired'], 401);
    }
}

try {
    switch ($action) {

        case 'login':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') json(['success' => false, 'message' => 'Method not allowed'], 405);
            (new AuthService(pdo()))->login($_POST);
            break;

        case 'logout':
            (new AuthService(pdo()))->logout();
            break;

        case 'get-csrf':
            $auth = new AuthService(pdo());
            json(['csrf_token' => $auth->generateCsrfToken()]);
            break;

        // Dashboard page
        case 'dashboard':
            if (empty($_SESSION['user'])) {
                if ($isAjax) json(['success' => false, 'message' => 'Session expired'], 401);
                header('Location: index.php'); exit;
            }
            require __DIR__ . '/views/dashboard.php';
            break;

        // Contacts
        case 'contacts.list':
        case 'contacts.getForm':
        case 'contacts.create':
        case 'contacts.update':
        case 'contacts.delete':
        case 'contacts.importCSV':
        case 'contacts.exportCSV':
            requireLoginAjax();
            $controller = new ContactController();
            $method = explode('.', $action)[1];
            if (method_exists($controller, $method)) {
                $controller->$method();
            } else {
                json(['success' => false, 'message' => 'Unknown contact action'], 404);
            }
            break;

        // Audit logs
        case 'audit.logs':
            requireLoginAjax();
            if ($_SESSION['user']['role'] !== 'admin') json(['success' => false, 'message' => 'Unauthorized'], 403);
            (new AuditController())->list();
            break;

        // Default landing page
        case 'landing':
        default:
            if ($isAjax) {
                json(['success' => false, 'message' => 'Not Found'], 404);
            } else {
                require __DIR__ . '/views/landing.html';
            }
            break;
    }

} catch (Throwable $e) {
    if ($isAjax || str_starts_with($action, 'contacts.') || $action === 'get-csrf' || $action === 'audit.logs') {
        json(['success' => false, 'error' => $e->getMessage()]);
    } else {
        echo "Unexpected error: " . $e->getMessage();
    }
}
