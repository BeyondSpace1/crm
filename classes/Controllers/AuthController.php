<?php
/**
 * Authentication controller
 */
class AuthController 
{
    private $authService;
    private $userRepository;
    private $auditLogger;
    
    public function __construct(AuthService $authService, UserRepository $userRepository, AuditLogger $auditLogger) 
    {
        $this->authService = $authService;
        $this->userRepository = $userRepository;
        $this->auditLogger = $auditLogger;
    }
    
    public function showLogin(): void 
    {
        // If already logged in, redirect to contacts
        if ($this->authService->isLoggedIn()) {
            header('Location: index.php?action=contacts.list');
            exit;
        }
        
        $title = 'Login';
        $content = 'views/login.php';
        $hideNavbar = true;
        $csrfToken = $this->authService->generateCsrfToken();
        
        include 'views/layout.php';
    }
    
    public function login(): void 
    {
        $errors = [];
        
        // Validate CSRF token
        if (!isset($_POST['_token']) || !$this->authService->validateCsrfToken($_POST['_token'])) {
            $errors['general'] = 'Invalid security token. Please try again.';
        }
        
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Basic validation
        if (empty($email)) {
            $errors['email'] = 'Email is required';
        }
        
        if (empty($password)) {
            $errors['password'] = 'Password is required';
        }
        
        // Attempt login if no validation errors
        if (empty($errors)) {
            if ($this->authService->login($email, $password)) {
                // Log successful login
                $user = $this->authService->getCurrentUser();
                $this->auditLogger->log('login', 'user', $user['id'], ['ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'], $user);
                
                // Redirect to contacts list
                header('Location: index.php?action=contacts.list');
                exit;
            } else {
                $errors['general'] = 'Invalid email or password';
                
                // Log failed login attempt
                $user = $this->userRepository->findByEmail($email);
                if ($user) {
                    $this->auditLogger->log('login_failed', 'user', $user['id'], ['ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'], $user);
                }
            }
        }
        
        // Show login form with errors
        $title = 'Login';
        $content = 'views/login.php';
        $hideNavbar = true;
        $csrfToken = $this->authService->generateCsrfToken();
        
        include 'views/layout.php';
    }
    
    public function logout(): void 
    {
        // Log logout
        $user = $this->authService->getCurrentUser();
        if ($user) {
            $this->auditLogger->log('logout', 'user', $user['id'], ['ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'], $user);
        }
        
        $this->authService->logout();
    }
}