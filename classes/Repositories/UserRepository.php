<?php
/**
 * User repository for database operations
 */
class UserRepository 
{
    private $db;
    
    public function __construct(PDO $db) 
    {
        $this->db = $db;
    }
    
    public function findByEmail(string $email): ?array 
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    public function findById(int $id): ?array 
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    public function create(User $user): int 
    {
        $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        
        $stmt->execute([
            $user->getName(),
            $user->getEmail(),
            $user->getPassword(),
            $user->getRole()
        ]);
        
        return (int) $this->db->lastInsertId();
    }
    
    public function update(User $user): bool 
    {
        $sql = "UPDATE users SET name = ?, email = ?, role = ?";
        $params = [$user->getName(), $user->getEmail(), $user->getRole()];
        
        // Only update password if it's set
        if (!empty($user->getPassword())) {
            $sql .= ", password = ?";
            $params[] = $user->getPassword();
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $user->getId();
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function delete(int $id): bool 
    {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function findAll(int $limit = 50, int $offset = 0): array 
    {
        $sql = "SELECT * FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit, $offset]);
        
        return $stmt->fetchAll();
    }
    
    public function countAll(): int 
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM users");
        return (int) $stmt->fetchColumn();
    }
    
    public function emailExists(string $email, ?int $excludeId = null): bool 
    {
        $sql = "SELECT COUNT(*) FROM users WHERE email = ?";
        $params = [$email];
        
        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchColumn() > 0;
    }
}