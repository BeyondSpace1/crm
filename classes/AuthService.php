<?php
declare(strict_types=1);

final class AuthService
{
    public function __construct(private PDO $pdo) {}

    private function json(array $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    // CSRF Token Generator
    public function generateCsrfToken(): string {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    // CSRF Token Validator
    private function validateCsrfToken(?string $token): bool {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
    }

    public function login(array $data): void {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();

        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $csrfToken = $data['csrf_token'] ?? '';

        if (!$this->validateCsrfToken($csrfToken)) {
            $this->json(['success' => false, 'message' => 'Invalid CSRF token'], 403);
        }

        if ($email === '' || $password === '') {
            $this->json(['success' => false, 'message' => 'Email and password are required'], 422);
        }

        $stmt = $this->pdo->prepare(
            "SELECT id, name, email, password, role FROM users WHERE email = ? LIMIT 1"
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password'])) {
            $this->json(['success' => false, 'message' => 'Invalid credentials'], 401);
        }

        $_SESSION['user'] = [
            'id'    => (int)$user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role'],
        ];

        session_regenerate_id(true);

        $this->json(['success' => true]);
    }

    public function logout(): void {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();
        $this->json(['success' => true]);
    }
}
