<?php
/**
 * Audit controller for viewing audit logs (admin only)
 */
class AuditController 
{
    private $auditLogger;
    private $authService;
    private $pageSize;
    
    public function __construct(AuditLogger $auditLogger, AuthService $authService, int $pageSize = 10) 
    {
        $this->auditLogger = $auditLogger;
        $this->authService = $authService;
        $this->pageSize = $pageSize;
    }
    
    public function list(): void 
    {
        // Ensure user is admin
        $this->authService->requireRole('admin');
        
        // Get filters and pagination
        $action = trim($_GET['filter_action'] ?? '');
        $entity = trim($_GET['filter_entity'] ?? '');
        $actorEmail = trim($_GET['filter_actor'] ?? '');
        $dateFrom = trim($_GET['date_from'] ?? '');
        $dateTo = trim($_GET['date_to'] ?? '');
        $page = max(1, (int) ($_GET['page'] ?? 1));
        
        $filters = [];
        if (!empty($action)) $filters['action'] = $action;
        if (!empty($entity)) $filters['entity'] = $entity;
        if (!empty($actorEmail)) $filters['actor_email'] = $actorEmail;
        if (!empty($dateFrom)) $filters['date_from'] = $dateFrom;
        if (!empty($dateTo)) $filters['date_to'] = $dateTo;
        
        $offset = ($page - 1) * $this->pageSize;
        
        // Get audit logs and count
        $auditLogs = $this->auditLogger->getAuditLogs($filters, $this->pageSize, $offset);
        $totalLogs = $this->auditLogger->countAuditLogs($filters);
        $totalPages = ceil($totalLogs / $this->pageSize);
        
        // Prepare pagination info
        $pagination = [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_items' => $totalLogs,
            'per_page' => $this->pageSize,
            'has_prev' => $page > 1,
            'has_next' => $page < $totalPages,
            'prev_page' => max(1, $page - 1),
            'next_page' => min($totalPages, $page + 1)
        ];
        
        // Get audit summary for dashboard
        $auditSummary = $this->auditLogger->getAuditSummary();
        
        $title = 'Audit Logs';
        $content = 'views/audit/list.php';
        
        include 'views/layout.php';
    }
    
    public function exportCsv(): void 
    {
        $this->authService->requireRole('admin');
        
        // Get filters (same as list method)
        $action = trim($_GET['filter_action'] ?? '');
        $entity = trim($_GET['filter_entity'] ?? '');
        $actorEmail = trim($_GET['filter_actor'] ?? '');
        $dateFrom = trim($_GET['date_from'] ?? '');
        $dateTo = trim($_GET['date_to'] ?? '');
        
        $filters = [];
        if (!empty($action)) $filters['action'] = $action;
        if (!empty($entity)) $filters['entity'] = $entity;
        if (!empty($actorEmail)) $filters['actor_email'] = $actorEmail;
        if (!empty($dateFrom)) $filters['date_from'] = $dateFrom;
        if (!empty($dateTo)) $filters['date_to'] = $dateTo;
        
        // Get all matching logs
        $auditLogs = $this->auditLogger->getAuditLogs($filters, 10000, 0);
        
        // Generate CSV
        $output = fopen('php://temp', 'r+');
        
        // Write CSV header
        fputcsv($output, ['Timestamp', 'Actor', 'Action', 'Entity', 'Entity ID', 'Changes']);
        
        // Write audit log data
        foreach ($auditLogs as $log) {
            $changes = '';
            if (!empty($log['changes'])) {
                $changesData = json_decode($log['changes'], true);
                if (is_array($changesData)) {
                    $changesSummary = [];
                    foreach ($changesData as $field => $change) {
                        if (is_array($change) && isset($change['old'], $change['new'])) {
                            $changesSummary[] = "{$field}: {$change['old']} → {$change['new']}";
                        }
                    }
                    $changes = implode('; ', $changesSummary);
                }
            }
            
            fputcsv($output, [
                $log['ts'],
                $log['actor_email'],
                $log['action'],
                $log['entity'],
                $log['entity_id'],
                $changes
            ]);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        // Download CSV
        $filename = 'audit_logs_' . date('Y-m-d_H-i-s') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($csv));
        
        echo $csv;
        exit;
    }
}