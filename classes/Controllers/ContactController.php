<?php
/**
 * Contact controller for managing contacts
 */
class ContactController 
{
    private $contactRepository;
    private $authService;
    private $auditLogger;
    private $csvService;
    private $pageSize;
    
    public function __construct(ContactRepository $contactRepository, AuthService $authService, AuditLogger $auditLogger, ContactsCsvService $csvService, int $pageSize = 10) 
    {
        $this->contactRepository = $contactRepository;
        $this->authService = $authService;
        $this->auditLogger = $auditLogger;
        $this->csvService = $csvService;
        $this->pageSize = $pageSize;
    }
    
    public function list(): void 
    {
        // Get filters and pagination
        $search = trim($_GET['search'] ?? '');
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $showDeleted = isset($_GET['show_deleted']) && $_GET['show_deleted'] === '1';
        
        $filters = [];
        if (!empty($search)) {
            $filters['search'] = $search;
        }
        
        $offset = ($page - 1) * $this->pageSize;
        
        // Get contacts and count
        $contacts = $this->contactRepository->findAll($filters, $this->pageSize, $offset, $showDeleted);
        $totalContacts = $this->contactRepository->count($filters, $showDeleted);
        $totalPages = ceil($totalContacts / $this->pageSize);
        
        // Prepare pagination info
        $pagination = [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_items' => $totalContacts,
            'per_page' => $this->pageSize,
            'has_prev' => $page > 1,
            'has_next' => $page < $totalPages,
            'prev_page' => max(1, $page - 1),
            'next_page' => min($totalPages, $page + 1)
        ];
        
        $title = 'Contacts';
        $content = 'views/contacts/list.php';
        $csrfToken = $this->authService->generateCsrfToken();
        
        include 'views/layout.php';
    }
    
    public function view(): void 
    {
        $id = (int) ($_GET['id'] ?? 0);
        
        if (!$id) {
            header('Location: index.php?action=contacts.list');
            exit;
        }
        
        $contact = $this->contactRepository->findById($id, true);
        
        if (!$contact) {
            $_SESSION['error'] = 'Contact not found';
            header('Location: index.php?action=contacts.list');
            exit;
        }
        
        $title = 'View Contact - ' . htmlspecialchars($contact['name']);
        $content = 'views/contacts/view.php';
        
        include 'views/layout.php';
    }
    
    public function create(): void 
    {
        $this->authService->requireAnyRole(['admin', 'editor']);
        
        $contact = new Contact();
        $errors = [];
        
        $title = 'Create Contact';
        $content = 'views/contacts/form.php';
        $csrfToken = $this->authService->generateCsrfToken();
        $isEdit = false;
        
        include 'views/layout.php';
    }
    
    public function store(): void 
    {
        $this->authService->requireAnyRole(['admin', 'editor']);
        
        // Validate CSRF token
        if (!isset($_POST['_token']) || !$this->authService->validateCsrfToken($_POST['_token'])) {
            $_SESSION['error'] = 'Invalid security token';
            header('Location: index.php?action=contacts.create');
            exit;
        }
        
        $contact = new Contact($_POST);
        $errors = $contact->validate();
        
        // Check email uniqueness
        if (empty($errors['email']) && $this->contactRepository->emailExists($contact->getEmail())) {
            $errors['email'] = 'Email already exists';
        }
        
        if (empty($errors)) {
            try {
                $id = $this->contactRepository->create($contact);
                $this->auditLogger->logCreate('contact', $id, $contact->toArray());
                
                $_SESSION['success'] = 'Contact created successfully';
                header('Location: index.php?action=contacts.view&id=' . $id);
                exit;
            } catch (Exception $e) {
                $errors['general'] = 'Failed to create contact: ' . $e->getMessage();
            }
        }
        
        $title = 'Create Contact';
        $content = 'views/contacts/form.php';
        $csrfToken = $this->authService->generateCsrfToken();
        $isEdit = false;
        
        include 'views/layout.php';
    }
    
    public function edit(): void 
    {
        $this->authService->requireAnyRole(['admin', 'editor']);
        
        $id = (int) ($_GET['id'] ?? 0);
        
        if (!$id) {
            header('Location: index.php?action=contacts.list');
            exit;
        }
        
        $contactData = $this->contactRepository->findById($id);
        
        if (!$contactData) {
            $_SESSION['error'] = 'Contact not found';
            header('Location: index.php?action=contacts.list');
            exit;
        }
        
        $contact = new Contact($contactData);
        $errors = [];
        
        $title = 'Edit Contact - ' . htmlspecialchars($contact->getName());
        $content = 'views/contacts/form.php';
        $csrfToken = $this->authService->generateCsrfToken();
        $isEdit = true;
        
        include 'views/layout.php';
    }
    
    public function update(): void 
    {
        $this->authService->requireAnyRole(['admin', 'editor']);
        
        $id = (int) ($_POST['id'] ?? 0);
        
        if (!$id) {
            header('Location: index.php?action=contacts.list');
            exit;
        }
        
        // Validate CSRF token
        if (!isset($_POST['_token']) || !$this->authService->validateCsrfToken($_POST['_token'])) {
            $_SESSION['error'] = 'Invalid security token';
            header('Location: index.php?action=contacts.edit&id=' . $id);
            exit;
        }
        
        $oldData = $this->contactRepository->findById($id);
        
        if (!$oldData) {
            $_SESSION['error'] = 'Contact not found';
            header('Location: index.php?action=contacts.list');
            exit;
        }
        
        $contact = new Contact(array_merge($oldData, $_POST));
        $errors = $contact->validate();
        
        // Check email uniqueness (excluding current contact)
        if (empty($errors['email']) && $this->contactRepository->emailExists($contact->getEmail(), $id)) {
            $errors['email'] = 'Email already exists';
        }
        
        if (empty($errors)) {
            try {
                if ($this->contactRepository->update($contact)) {
                    $this->auditLogger->logUpdate('contact', $id, $oldData, $contact->toArray());
                    
                    $_SESSION['success'] = 'Contact updated successfully';
                    header('Location: index.php?action=contacts.view&id=' . $id);
                    exit;
                } else {
                    $errors['general'] = 'Failed to update contact';
                }
            } catch (Exception $e) {
                $errors['general'] = 'Failed to update contact: ' . $e->getMessage();
            }
        }
        
        $title = 'Edit Contact - ' . htmlspecialchars($contact->getName());
        $content = 'views/contacts/form.php';
        $csrfToken = $this->authService->generateCsrfToken();
        $isEdit = true;
        
        include 'views/layout.php';
    }
    
    public function delete(): void 
    {
        $this->authService->requireAnyRole(['admin', 'editor']);
        
        $id = (int) ($_POST['id'] ?? 0);
        
        if (!$id) {
            header('Location: index.php?action=contacts.list');
            exit;
        }
        
        // Validate CSRF token
        if (!isset($_POST['_token']) || !$this->authService->validateCsrfToken($_POST['_token'])) {
            $_SESSION['error'] = 'Invalid security token';
            header('Location: index.php?action=contacts.list');
            exit;
        }
        
        $contact = $this->contactRepository->findById($id);
        
        if ($contact) {
            if ($this->contactRepository->softDelete($id)) {
                $this->auditLogger->logDelete('contact', $id, $contact);
                $_SESSION['success'] = 'Contact deleted successfully';
            } else {
                $_SESSION['error'] = 'Failed to delete contact';
            }
        } else {
            $_SESSION['error'] = 'Contact not found';
        }
        
        header('Location: index.php?action=contacts.list');
        exit;
    }
    
    public function exportCsv(): void 
    {
        // Get current filters
        $search = trim($_GET['search'] ?? '');
        $showDeleted = isset($_GET['show_deleted']) && $_GET['show_deleted'] === '1';
        
        $filters = [];
        if (!empty($search)) {
            $filters['search'] = $search;
        }
        
        // Get all contacts matching filters
        $contacts = $this->contactRepository->findAll($filters, 10000, 0, $showDeleted);
        
        // Generate CSV
        $csv = $this->csvService->exportCsv($contacts);
        
        // Download CSV
        $filename = 'contacts_' . date('Y-m-d_H-i-s') . '.csv';
        $this->csvService->downloadCsv($filename, $csv);
    }
    
    public function importForm(): void 
    {
        $this->authService->requireAnyRole(['admin', 'editor']);
        
        $title = 'Import Contacts';
        $content = 'views/contacts/import_form.php';
        $csrfToken = $this->authService->generateCsrfToken();
        
        include 'views/layout.php';
    }
    
    public function importDryRun(): void 
    {
        $this->authService->requireAnyRole(['admin', 'editor']);
        
        // Validate CSRF token
        if (!isset($_POST['_token']) || !$this->authService->validateCsrfToken($_POST['_token'])) {
            $_SESSION['error'] = 'Invalid security token';
            header('Location: index.php?action=contacts.importCsvForm');
            exit;
        }
        
        $errors = [];
        
        // Check file upload
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $errors['file'] = 'Please select a valid CSV file';
        }
        
        if (!empty($errors)) {
            $title = 'Import Contacts';
            $content = 'views/contacts/import_form.php';
            $csrfToken = $this->authService->generateCsrfToken();
            
            include 'views/layout.php';
            return;
        }
        
        try {
            // Read and parse CSV
            $csvContent = file_get_contents($_FILES['csv_file']['tmp_name']);
            $parseResult = $this->csvService->parseCsv($csvContent);
            
            if (!empty($parseResult['errors'])) {
                $errors['file'] = implode('<br>', $parseResult['errors']);
                
                $title = 'Import Contacts';
                $content = 'views/contacts/import_form.php';
                $csrfToken = $this->authService->generateCsrfToken();
                
                include 'views/layout.php';
                return;
            }
            
            // Perform dry run
            $dryRunResult = $this->csvService->dryRunImport($parseResult['contacts']);
            
            // Store results in session for commit
            $_SESSION['import_data'] = $parseResult['contacts'];
            $_SESSION['dry_run_results'] = $dryRunResult;
            
            $title = 'Import Preview';
            $content = 'views/contacts/import_results.php';
            $csrfToken = $this->authService->generateCsrfToken();
            $isDryRun = true;
            
            include 'views/layout.php';
            
        } catch (Exception $e) {
            $errors['general'] = 'Import failed: ' . $e->getMessage();
            
            $title = 'Import Contacts';
            $content = 'views/contacts/import_form.php';
            $csrfToken = $this->authService->generateCsrfToken();
            
            include 'views/layout.php';
        }
    }
    
    public function importCommit(): void 
    {
        $this->authService->requireAnyRole(['admin', 'editor']);
        
        // Validate CSRF token
        if (!isset($_POST['_token']) || !$this->authService->validateCsrfToken($_POST['_token'])) {
            $_SESSION['error'] = 'Invalid security token';
            header('Location: index.php?action=contacts.importCsvForm');
            exit;
        }
        
        // Check if we have import data in session
        if (!isset($_SESSION['import_data'])) {
            $_SESSION['error'] = 'No import data found. Please start over.';
            header('Location: index.php?action=contacts.importCsvForm');
            exit;
        }
        
        try {
            // Perform actual import
            $commitResult = $this->csvService->commitImport($_SESSION['import_data']);
            
            // Clear session data
            unset($_SESSION['import_data']);
            unset($_SESSION['dry_run_results']);
            
            $title = 'Import Results';
            $content = 'views/contacts/import_results.php';
            $csrfToken = $this->authService->generateCsrfToken();
            $isDryRun = false;
            $importResults = $commitResult;
            
            include 'views/layout.php';
            
        } catch (Exception $e) {
            $_SESSION['error'] = 'Import failed: ' . $e->getMessage();
            header('Location: index.php?action=contacts.importCsvForm');
            exit;
        }
    }
}