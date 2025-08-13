<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../Repositories/ContactRepository.php';
require_once __DIR__ . '/../Repositories/UserRepository.php';
require_once __DIR__ . '/../Services/AuditLogger.php';
require_once __DIR__ . '/../Entities/Contact.php';

class ContactController {
    private ContactRepository $contactRepo;
    private UserRepository $userRepo;

    public function __construct() {
        $this->contactRepo = new ContactRepository();
        $this->userRepo = new UserRepository();
    }

    private function getActor(): array {
        return $_SESSION['user'] ?? ['id'=>0,'email'=>'guest@example.com'];
    }

    private function json(array $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }

    private function normalizeTags(?string $tags): string {
        $tags = trim((string)$tags);
        if ($tags === '') return json_encode([]); // empty array
        // convert comma-separated to JSON array
        $arr = array_map('trim', explode(',', $tags));
        return json_encode($arr);
    }

    // --- AJAX endpoint: List contacts ---
    public function list(): void {
        try {
            $this->userRepo->requireAnyRole(['admin','editor','viewer']);
            $includeDeleted = isset($_GET['deleted']) && (bool)$_GET['deleted'];
            $contacts = $this->contactRepo->getAll($includeDeleted);

            $data = array_map(fn($c) => [
                'id'      => $c->id,
                'name'    => $c->name,
                'email'   => $c->email,
                'phone'   => $c->phone,
                'company' => $c->company,
                'tags'    => $c->tags
            ], $contacts);

            $this->json($data);

        } catch (\Throwable $e) {
            $this->json(['success'=>false,'message'=>$e->getMessage()]);
        }
    }

    // --- AJAX endpoint: Get contact form ---
    public function getForm(): void {
        try {
            $this->userRepo->requireAnyRole(['admin','editor']);
            $id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;
            $contact = $id ? $this->contactRepo->getById($id) : null;

            ob_start();
            require __DIR__ . '/../../views/contacts/form.php'; // form.php uses $contact
            $html = ob_get_clean();

            echo $html;
            exit;

        } catch (\Throwable $e) {
            $this->json(['success'=>false,'message'=>$e->getMessage()]);
        }
    }

    // --- AJAX endpoint: Create ---
    public function create(): void {
        try {
            $this->userRepo->requireAnyRole(['admin','editor']);
            $data = $_POST;

            $contact = new Contact(
                null,
                $data['name'] ?? '',
                $data['email'] ?? '',
                $data['phone'] ?? '',
                $data['company'] ?? '',
                $this->normalizeTags($data['tags'] ?? '')
            );

            $contactId = $this->contactRepo->create($contact);

            if ($contactId) {
                $actor = $this->getActor();
                AuditLogger::log($actor['id'], $actor['email'], 'create', 'contact', (int)$contactId, $data);
                $this->json(['success'=>true]);
            } else {
                $this->json(['success'=>false,'message'=>'Failed to create contact']);
            }

        } catch (\Throwable $e) {
            $this->json(['success'=>false,'message'=>$e->getMessage()]);
        }
    }

    // --- AJAX endpoint: Update ---
    public function update(): void {
        try {
            $this->userRepo->requireAnyRole(['admin','editor']);
            $id = (int)($_POST['id'] ?? 0);
            $existing = $this->contactRepo->getById($id);
            if (!$existing) $this->json(['success'=>false,'message'=>'Contact not found']);

            $contact = new Contact(
                $id,
                $_POST['name'] ?? $existing->name,
                $_POST['email'] ?? $existing->email,
                $_POST['phone'] ?? $existing->phone,
                $_POST['company'] ?? $existing->company,
                $this->normalizeTags($_POST['tags'] ?? $existing->tags)
            );

            if ($this->contactRepo->update($contact)) {
                $actor = $this->getActor();
                $changes = [];
                foreach(['name','email','phone','company','tags'] as $f){
                    if ($existing->$f !== $contact->$f) {
                        $changes[$f] = ['old'=>$existing->$f,'new'=>$contact->$f];
                    }
                }
                AuditLogger::log($actor['id'], $actor['email'], 'update', 'contact', $id, $changes);
                $this->json(['success'=>true]);
            } else {
                $this->json(['success'=>false,'message'=>'Failed to update contact']);
            }

        } catch (\Throwable $e) {
            $this->json(['success'=>false,'message'=>$e->getMessage()]);
        }
    }

    // --- AJAX endpoint: Delete ---
    public function delete(): void {
        try {
            $this->userRepo->requireRole('admin');
            $id = (int)($_POST['id'] ?? 0);
            $existing = $this->contactRepo->getById($id);
            if (!$existing) $this->json(['success'=>false,'message'=>'Contact not found']);

            if ($this->contactRepo->softDelete($id)) {
                $actor = $this->getActor();
                AuditLogger::log($actor['id'], $actor['email'], 'delete', 'contact', $id, [
                    'name'=>$existing->name,
                    'email'=>$existing->email,
                    'phone'=>$existing->phone,
                    'company'=>$existing->company,
                    'tags'=>$existing->tags
                ]);
                $this->json(['success'=>true]);
            } else {
                $this->json(['success'=>false,'message'=>'Failed to delete contact']);
            }

        } catch (\Throwable $e) {
            $this->json(['success'=>false,'message'=>$e->getMessage()]);
        }
    }
}
