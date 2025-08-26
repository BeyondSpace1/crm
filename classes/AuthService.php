<?php
/**
 * Authentication service for handling user login/logout and sessions
 */
class AuthService 
{
    private $db;
    private $userRepository;
    
    public function __construct(PDO $db) 
    {
        $this->db = $db;
        $this->userRepository = new UserRepository($db);
    }
    
    public function login(string $email, string $password): bool 
    {
        $user = $this->userRepository->findByEmail($email);
        
        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }
        
        // Start session and store user info
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['login_time'] = time();
        
        // Generate CSRF token
        $this->generateCsrfToken();
        
        return true;
    }
    
    public function logout(): void 
    {
        session_destroy();
        session_start();
    }
    
    public function isLoggedIn(): bool 
    {
        return isset($_SESSION['user_id']) && isset($_SESSION['user_email']);
    }
    
    public function getCurrentUser(): ?array 
    {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'email' => $_SESSION['user_email'],
            'name' => $_SESSION['user_name'],
            'role' => $_SESSION['user_role']
        ];
    }
    
    public function hasRole(string $role): bool 
    {
        $user = $this->getCurrentUser();
        return $user && $user['role'] === $role;
    }
    
    public function hasAnyRole(array $roles): bool 
    {
        $user = $this->getCurrentUser();
        return $user && in_array($user['role'], $roles);
    }
    
    public function generateCsrfToken(): string 
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    public function validateCsrfToken(string $token): bool 
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    public function requireRole(string $role): void 
    {
        if (!$this->hasRole($role)) {
            throw new Exception("Access denied. Required role: {$role}");
        }
    }
    
    public function requireAnyRole(array $roles): void 
    {
        if (!$this->hasAnyRole($roles)) {
            $rolesList = implode(', ', $roles);
            throw new Exception("Access denied. Required roles: {$rolesList}");
        }
    }
}