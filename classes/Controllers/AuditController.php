<?php
require_once __DIR__.'/../services/AuditLogger.php';
require_once __DIR__.'/../repositories/UserRepository.php';

class AuditController {
    private UserRepository $userRepo;

    public function __construct(){
        $this->userRepo = new UserRepository();
    }

    public function list(): void {
        $this->userRepo->requireRole('admin');
        $logs = AuditLogger::getAll();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($logs, JSON_PRETTY_PRINT);
        exit;
    }
}
