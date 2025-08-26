<?php
/**
 * Audit logging service for tracking all changes
 */
class AuditLogger 
{
    private $db;
    
    public function __construct(PDO $db) 
    {
        $this->db = $db;
    }
    
    public function log(string $action, string $entity, int $entityId, array $changes = [], ?array $user = null): void 
    {
        // Get current user if not provided
        if ($user === null && isset($_SESSION['user_id'])) {
            $user = [
                'id' => $_SESSION['user_id'],
                'email' => $_SESSION['user_email']
            ];
        }
        
        if ($user === null) {
            return; // Can't log without user info
        }
        
        $sql = "INSERT INTO audit_logs (actor_id, actor_email, action, entity, entity_id, changes) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        
        $stmt->execute([
            $user['id'],
            $user['email'],
            $action,
            $entity,
            $entityId,
            !empty($changes) ? json_encode($changes) : null
        ]);
    }
    
    public function logCreate(string $entity, int $entityId, array $data, ?array $user = null): void 
    {
        $this->log('create', $entity, $entityId, ['created' => $data], $user);
    }
    
    public function logUpdate(string $entity, int $entityId, array $oldData, array $newData, ?array $user = null): void 
    {
        $changes = $this->calculateDiff($oldData, $newData);
        $this->log('update', $entity, $entityId, $changes, $user);
    }
    
    public function logDelete(string $entity, int $entityId, array $data, ?array $user = null): void 
    {
        $this->log('delete', $entity, $entityId, ['deleted' => $data], $user);
    }
    
    private function calculateDiff(array $oldData, array $newData): array 
    {
        $changes = [];
        
        // Find changed fields
        foreach ($newData as $key => $newValue) {
            $oldValue = $oldData[$key] ?? null;
            
            // Skip unchanged values and timestamps
            if ($oldValue !== $newValue && !in_array($key, ['updated_at', 'created_at'])) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue
                ];
            }
        }
        
        return $changes;
    }
    
    public function getAuditLogs(array $filters = [], int $limit = 10, int $offset = 0): array 
    {
        $sql = "SELECT * FROM audit_logs";
        $params = [];
        $conditions = [];
        
        // Add filters
        if (!empty($filters['action'])) {
            $conditions[] = "action = ?";
            $params[] = $filters['action'];
        }
        
        if (!empty($filters['entity'])) {
            $conditions[] = "entity = ?";
            $params[] = $filters['entity'];
        }
        
        if (!empty($filters['actor_email'])) {
            $conditions[] = "actor_email LIKE ?";
            $params[] = '%' . $filters['actor_email'] . '%';
        }
        
        if (!empty($filters['date_from'])) {
            $conditions[] = "ts >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $conditions[] = "ts <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        
        // Build WHERE clause
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        // Add ordering and pagination
        $sql .= " ORDER BY ts DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    public function countAuditLogs(array $filters = []): int 
    {
        $sql = "SELECT COUNT(*) FROM audit_logs";
        $params = [];
        $conditions = [];
        
        // Add filters (same as getAuditLogs)
        if (!empty($filters['action'])) {
            $conditions[] = "action = ?";
            $params[] = $filters['action'];
        }
        
        if (!empty($filters['entity'])) {
            $conditions[] = "entity = ?";
            $params[] = $filters['entity'];
        }
        
        if (!empty($filters['actor_email'])) {
            $conditions[] = "actor_email LIKE ?";
            $params[] = '%' . $filters['actor_email'] . '%';
        }
        
        if (!empty($filters['date_from'])) {
            $conditions[] = "ts >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $conditions[] = "ts <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        
        // Build WHERE clause
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return (int) $stmt->fetchColumn();
    }
    
    public function getAuditSummary(): array 
    {
        $sql = "SELECT 
                    action,
                    entity,
                    COUNT(*) as count,
                    MAX(ts) as last_activity
                FROM audit_logs 
                GROUP BY action, entity 
                ORDER BY last_activity DESC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}