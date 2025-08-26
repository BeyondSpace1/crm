<?php
/**
 * Simple router for handling application routes
 */
class Router 
{
    private $authService;
    
    public function __construct(AuthService $authService) 
    {
        $this->authService = $authService;
    }
    
    public function route(string $action, AuthController $authController, ContactController $contactController, AuditController $auditController) 
    {
        // Handle login page
        if ($action === 'login') {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $authController->login();
            } else {
                $authController->showLogin();
            }
            return;
        }
        
        // All other routes require authentication
        if (!$this->authService->isLoggedIn()) {
            header('Location: index.php?action=login');
            exit;
        }
        
        // Route to appropriate controller
        switch ($action) {
            // Contact routes
            case 'contacts.list':
                $contactController->list();
                break;
                
            case 'contacts.view':
                $this->requireRole(['admin', 'editor', 'viewer']);
                $contactController->view();
                break;
                
            case 'contacts.create':
                $this->requireRole(['admin', 'editor']);
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $contactController->store();
                } else {
                    $contactController->create();
                }
                break;
                
            case 'contacts.update':
                $this->requireRole(['admin', 'editor']);
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $contactController->update();
                } else {
                    $contactController->edit();
                }
                break;
                
            case 'contacts.delete':
                $this->requireRole(['admin', 'editor']);
                $contactController->delete();
                break;
                
            case 'contacts.exportCsv':
                $this->requireRole(['admin', 'editor', 'viewer']);
                $contactController->exportCsv();
                break;
                
            case 'contacts.importCsvForm':
                $this->requireRole(['admin', 'editor']);
                $contactController->importForm();
                break;
                
            case 'contacts.importCsvDryRun':
                $this->requireRole(['admin', 'editor']);
                $contactController->importDryRun();
                break;
                
            case 'contacts.importCsvCommit':
                $this->requireRole(['admin', 'editor']);
                $contactController->importCommit();
                break;
                
            // Audit routes
            case 'audit.list':
                $this->requireRole(['admin']);
                $auditController->list();
                break;
                
            default:
                http_response_code(404);
                echo "<h1>404 - Page Not Found</h1>";
                break;
        }
    }
    
    private function requireRole(array $allowedRoles) 
    {
        $user = $this->authService->getCurrentUser();
        
        if (!$user || !in_array($user['role'], $allowedRoles)) {
            http_response_code(403);
            include 'views/layout.php';
            exit;
        }
    }
}