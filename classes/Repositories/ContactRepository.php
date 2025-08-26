<?php
/**
 * Contact repository for database operations
 */
class ContactRepository 
{
    private $db;
    
    public function __construct(PDO $db) 
    {
        $this->db = $db;
    }
    
    public function create(Contact $contact): int 
    {
        $sql = "INSERT INTO contacts (name, email, phone, company, tags) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        
        $stmt->execute([
            $contact->getName(),
            $contact->getEmail(),
            $contact->getPhone(),
            $contact->getCompany(),
            $contact->getTagsAsJson()
        ]);
        
        return (int) $this->db->lastInsertId();
    }
    
    public function update(Contact $contact): bool 
    {
        $sql = "UPDATE contacts SET name = ?, email = ?, phone = ?, company = ?, tags = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            $contact->getName(),
            $contact->getEmail(),
            $contact->getPhone(),
            $contact->getCompany(),
            $contact->getTagsAsJson(),
            $contact->getId()
        ]);
    }
    
    public function findById(int $id, bool $includeDeleted = false): ?array 
    {
        $sql = "SELECT * FROM contacts WHERE id = ?";
        
        if (!$includeDeleted) {
            $sql .= " AND deleted_at IS NULL";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    public function findAll(array $filters = [], int $limit = 10, int $offset = 0, bool $includeDeleted = false): array 
    {
        $sql = "SELECT * FROM contacts";
        $params = [];
        $conditions = [];
        
        // Add deleted filter
        if (!$includeDeleted) {
            $conditions[] = "deleted_at IS NULL";
        }
        
        // Add search filter
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $conditions[] = "(name LIKE ? OR email LIKE ? OR company LIKE ?)";
            $params = array_merge($params, [$search, $search, $search]);
        }
        
        // Build WHERE clause
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        // Add ordering and pagination
        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    public function count(array $filters = [], bool $includeDeleted = false): int 
    {
        $sql = "SELECT COUNT(*) FROM contacts";
        $params = [];
        $conditions = [];
        
        // Add deleted filter
        if (!$includeDeleted) {
            $conditions[] = "deleted_at IS NULL";
        }
        
        // Add search filter
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $conditions[] = "(name LIKE ? OR email LIKE ? OR company LIKE ?)";
            $params = array_merge($params, [$search, $search, $search]);
        }
        
        // Build WHERE clause
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return (int) $stmt->fetchColumn();
    }
    
    public function softDelete(int $id): bool 
    {
        $sql = "UPDATE contacts SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    public function restore(int $id): bool 
    {
        $sql = "UPDATE contacts SET deleted_at = NULL WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    public function hardDelete(int $id): bool 
    {
        $stmt = $this->db->prepare("DELETE FROM contacts WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function emailExists(string $email, ?int $excludeId = null, bool $includeDeleted = false): bool 
    {
        $sql = "SELECT COUNT(*) FROM contacts WHERE email = ?";
        $params = [$email];
        
        if (!$includeDeleted) {
            $sql .= " AND deleted_at IS NULL";
        }
        
        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchColumn() > 0;
    }
    
    public function findByEmail(string $email, bool $includeDeleted = false): ?array 
    {
        $sql = "SELECT * FROM contacts WHERE email = ?";
        
        if (!$includeDeleted) {
            $sql .= " AND deleted_at IS NULL";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    public function bulkCreate(array $contacts): array 
    {
        $results = [];
        
        foreach ($contacts as $contactData) {
            try {
                $contact = new Contact($contactData);
                $errors = $contact->validate();
                
                if (!empty($errors)) {
                    $results[] = [
                        'success' => false,
                        'data' => $contactData,
                        'errors' => $errors,
                        'action' => 'validation_failed'
                    ];
                    continue;
                }
                
                // Check if email exists
                $existing = $this->findByEmail($contact->getEmail());
                
                if ($existing) {
                    // Update existing contact
                    $contact->fill(array_merge($existing, $contactData));
                    $this->update($contact);
                    
                    $results[] = [
                        'success' => true,
                        'data' => $contact->toArray(),
                        'errors' => [],
                        'action' => 'updated'
                    ];
                } else {
                    // Create new contact
                    $id = $this->create($contact);
                    
                    $results[] = [
                        'success' => true,
                        'data' => array_merge($contact->toArray(), ['id' => $id]),
                        'errors' => [],
                        'action' => 'created'
                    ];
                }
                
            } catch (Exception $e) {
                $results[] = [
                    'success' => false,
                    'data' => $contactData,
                    'errors' => ['general' => $e->getMessage()],
                    'action' => 'error'
                ];
            }
        }
        
        return $results;
    }
}