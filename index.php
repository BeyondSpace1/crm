<?php
/**
 * Main application entry point
 * Handles routing and bootstrapping
 */

session_start();

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load configuration
$config = require_once 'config.php';

// Autoloader for classes
spl_autoload_register(function ($class) {
    $class = str_replace('\\', '/', $class);
    
    // Define class paths
    $paths = [
        'classes/',
        'classes/Entities/',
        'classes/Repositories/',
        'classes/Services/',
        'classes/Controllers/'
    ];
    
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

try {
    // Initialize database
    $database = new Database($config['db']);
    $db = $database->getConnection();
    
    // Initialize services
    $authService = new AuthService($db);
    $auditLogger = new AuditLogger($db);
    
    // Initialize repositories
    $userRepository = new UserRepository($db);
    $contactRepository = new ContactRepository($db);
    
    // Initialize CSV service
    $csvService = new ContactsCsvService($db, $auditLogger);
    
    // Initialize controllers
    $authController = new AuthController($authService, $userRepository, $auditLogger);
    $contactController = new ContactController($contactRepository, $authService, $auditLogger, $csvService, $config['page_size']);
    $auditController = new AuditController($auditLogger, $authService, $config['page_size']);
    
    // Initialize router
    $router = new Router($authService);
    
    // Get current action
    $action = $_GET['action'] ?? 'contacts.list';
    
    // Handle logout
    if ($action === 'logout') {
        $authController->logout();
        header('Location: index.php?action=login');
        exit;
    }
    
    // Check if user is authenticated (except for login page)
    if ($action !== 'login' && !$authService->isLoggedIn()) {
        header('Location: index.php?action=login');
        exit;
    }
    
    // Route the request
    $router->route($action, $authController, $contactController, $auditController);
    
} catch (Exception $e) {
    // Error handling
    error_log($e->getMessage());
    http_response_code(500);
    
    if ($config['app']['environment'] === 'development') {
        echo "<h1>Error</h1>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        echo "<h1>System Error</h1>";
        echo "<p>We're experiencing technical difficulties. Please try again later.</p>";
    }
}