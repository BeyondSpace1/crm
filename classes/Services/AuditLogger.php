<?php
require_once __DIR__.'/../database.php';

class AuditLogger {
    public static function log(int $actorId, string $actorEmail, string $action, string $entity, int $entityId, array $changes = null){
        $stmt = pdo()->prepare("INSERT INTO audit_logs (actor_id, actor_email, action, entity, entity_id, changes) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$actorId, $actorEmail, $action, $entity, $entityId, $changes ? json_encode($changes) : null]);
    }

    public static function getAll(): array {
        return pdo()->query("SELECT * FROM audit_logs ORDER BY ts DESC")->fetchAll(PDO::FETCH_ASSOC);
    }
}
