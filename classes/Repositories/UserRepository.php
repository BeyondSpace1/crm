<?php
// repositories/UserRepository.php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../entities/User.php';

class UserRepository {

    public function findByUsername(string $username): ?User {
        $stmt = pdo()->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            return new User($data['id'], $data['username'], $data['email'], $data['password'], $data['role']);
        }
        return null;
    }

    public function findById(int $id): ?User {
        $stmt = pdo()->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            return new User($data['id'], $data['username'], $data['email'], $data['password'], $data['role']);
        }
        return null;
    }

    public function startSession(User $user) {
        $_SESSION['user'] = [
            'id'       => $user->id,
            'username' => $user->username,
            'role'     => $user->role
        ];
    }

    public function hasRole(string $role): bool {
        return isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === $role;
    }

    public function hasAnyRole(array $roles): bool {
        return isset($_SESSION['user']['role']) && in_array($_SESSION['user']['role'], $roles);
    }

    public function requireRole(string $role) {
        if (!$this->hasRole($role)) {
            http_response_code(403);
            exit(json_encode(['error' => 'Access denied']));
        }
    }

    public function requireAnyRole(array $roles) {
        if (!$this->hasAnyRole($roles)) {
            http_response_code(403);
            exit(json_encode(['error' => 'Access denied']));
        }
    }

     public static function findByEmail(string $email): ?User {
        $stmt = pdo()->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return new User($row['id'], $row['username'], $row['email'], $row['password'], $row['role']);
        }
        return null;
    }

    public static function getById(int $id): ?User {
        $stmt = pdo()->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return new User($row['id'], $row['username'], $row['email'], $row['password'], $row['role']);
        }
        return null;
    }

    public static function checkAccess(string $requiredRole): bool {
        if (!isset($_SESSION['user'])) return false;

        $roleHierarchy = [
            'viewer' => 1,
            'editor' => 2,
            'admin' => 3
        ];

        $currentRole = $_SESSION['user']->role;

        return $roleHierarchy[$currentRole] >= $roleHierarchy[$requiredRole];
    }
}
