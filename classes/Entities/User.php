<?php
/**
 * User entity class
 */
class User 
{
    private $id;
    private $name;
    private $email;
    private $password;
    private $role;
    private $createdAt;
    
    public function __construct(array $data = []) 
    {
        if (!empty($data)) {
            $this->fill($data);
        }
    }
    
    public function fill(array $data): void 
    {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->password = $data['password'] ?? '';
        $this->role = $data['role'] ?? 'viewer';
        $this->createdAt = $data['created_at'] ?? null;
    }
    
    public function getId(): ?int 
    {
        return $this->id;
    }
    
    public function getName(): string 
    {
        return $this->name;
    }
    
    public function setName(string $name): void 
    {
        $this->name = $name;
    }
    
    public function getEmail(): string 
    {
        return $this->email;
    }
    
    public function setEmail(string $email): void 
    {
        $this->email = $email;
    }
    
    public function getPassword(): string 
    {
        return $this->password;
    }
    
    public function setPassword(string $password): void 
    {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }
    
    public function getRole(): string 
    {
        return $this->role;
    }
    
    public function setRole(string $role): void 
    {
        $allowedRoles = ['admin', 'editor', 'viewer'];
        if (!in_array($role, $allowedRoles)) {
            throw new InvalidArgumentException("Invalid role: {$role}");
        }
        $this->role = $role;
    }
    
    public function getCreatedAt(): ?string 
    {
        return $this->createdAt;
    }
    
    public function toArray(): array 
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'created_at' => $this->createdAt
        ];
    }
    
    public function validate(): array 
    {
        $errors = [];
        
        if (empty($this->name) || strlen($this->name) < 2) {
            $errors['name'] = 'Name must be at least 2 characters long';
        }
        
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }
        
        if (!in_array($this->role, ['admin', 'editor', 'viewer'])) {
            $errors['role'] = 'Invalid role';
        }
        
        return $errors;
    }
}