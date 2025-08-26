<?php
/**
 * CSV service for contacts import/export
 */
class ContactsCsvService 
{
    private $db;
    private $auditLogger;
    private $contactRepository;
    
    public function __construct(PDO $db, AuditLogger $auditLogger) 
    {
        $this->db = $db;
        $this->auditLogger = $auditLogger;
        $this->contactRepository = new ContactRepository($db);
    }
    
    public function exportCsv(array $contacts): string 
    {
        $output = fopen('php://temp', 'r+');
        
        // Write CSV header
        fputcsv($output, ['name', 'email', 'phone', 'company', 'tags']);
        
        // Write contact data
        foreach ($contacts as $contact) {
            $tags = $contact['tags'];
            if (is_string($tags)) {
                $tagsArray = json_decode($tags, true);
                $tags = is_array($tagsArray) ? implode(',', $tagsArray) : '';
            } elseif (is_array($tags)) {
                $tags = implode(',', $tags);
            }
            
            fputcsv($output, [
                $contact['name'],
                $contact['email'],
                $contact['phone'] ?? '',
                $contact['company'] ?? '',
                $tags
            ]);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }
    
    public function parseCsv(string $csvContent): array 
    {
        $lines = str_getcsv($csvContent, "\n");
        $contacts = [];
        $errors = [];
        
        if (empty($lines)) {
            return ['contacts' => [], 'errors' => ['CSV file is empty']];
        }
        
        // Get header row
        $headers = str_getcsv(array_shift($lines));
        $expectedHeaders = ['name', 'email', 'phone', 'company', 'tags'];
        
        // Validate headers
        foreach ($expectedHeaders as $header) {
            if (!in_array($header, $headers)) {
                $errors[] = "Missing required header: {$header}";
            }
        }
        
        if (!empty($errors)) {
            return ['contacts' => [], 'errors' => $errors];
        }
        
        // Parse data rows
        foreach ($lines as $lineNumber => $line) {
            if (empty(trim($line))) {
                continue; // Skip empty lines
            }
            
            $data = str_getcsv($line);
            
            if (count($data) !== count($headers)) {
                $errors[] = "Line " . ($lineNumber + 2) . ": Column count mismatch";
                continue;
            }
            
            $contactData = array_combine($headers, $data);
            
            // Clean and validate data
            $contactData['name'] = trim($contactData['name']);
            $contactData['email'] = trim($contactData['email']);
            $contactData['phone'] = trim($contactData['phone'] ?? '');
            $contactData['company'] = trim($contactData['company'] ?? '');
            
            // Parse tags
            $tags = trim($contactData['tags'] ?? '');
            if (!empty($tags)) {
                $contactData['tags'] = array_map('trim', explode(',', $tags));
            } else {
                $contactData['tags'] = [];
            }
            
            $contacts[] = $contactData;
        }
        
        return ['contacts' => $contacts, 'errors' => $errors];
    }
    
    public function dryRunImport(array $contacts): array 
    {
        $results = [];
        $summary = [
            'total' => count($contacts),
            'valid' => 0,
            'invalid' => 0,
            'new' => 0,
            'updates' => 0,
            'errors' => []
        ];
        
        foreach ($contacts as $index => $contactData) {
            $contact = new Contact($contactData);
            $validationErrors = $contact->validate();
            
            $result = [
                'index' => $index + 1,
                'data' => $contactData,
                'valid' => empty($validationErrors),
                'errors' => $validationErrors,
                'action' => 'unknown'
            ];
            
            if (empty($validationErrors)) {
                // Check if contact exists
                $existing = $this->contactRepository->findByEmail($contact->getEmail());
                
                if ($existing) {
                    $result['action'] = 'update';
                    $result['existing_id'] = $existing['id'];
                    $summary['updates']++;
                } else {
                    $result['action'] = 'create';
                    $summary['new']++;
                }
                
                $summary['valid']++;
            } else {
                $result['action'] = 'error';
                $summary['invalid']++;
                $summary['errors'] = array_merge($summary['errors'], $validationErrors);
            }
            
            $results[] = $result;
        }
        
        return [
            'results' => $results,
            'summary' => $summary
        ];
    }
    
    public function commitImport(array $contacts): array 
    {
        $results = [];
        $summary = [
            'total' => count($contacts),
            'success' => 0,
            'errors' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0
        ];
        
        try {
            $this->db->beginTransaction();
            
            foreach ($contacts as $index => $contactData) {
                try {
                    $contact = new Contact($contactData);
                    $validationErrors = $contact->validate();
                    
                    if (!empty($validationErrors)) {
                        $results[] = [
                            'index' => $index + 1,
                            'success' => false,
                            'action' => 'validation_error',
                            'errors' => $validationErrors,
                            'data' => $contactData
                        ];
                        $summary['errors']++;
                        continue;
                    }
                    
                    // Check if contact exists
                    $existing = $this->contactRepository->findByEmail($contact->getEmail());
                    
                    if ($existing) {
                        // Update existing contact
                        $oldData = $existing;
                        $contact->fill(array_merge($existing, $contactData));
                        
                        if ($this->contactRepository->update($contact)) {
                            $this->auditLogger->logUpdate('contact', $existing['id'], $oldData, $contact->toArray());
                            
                            $results[] = [
                                'index' => $index + 1,
                                'success' => true,
                                'action' => 'updated',
                                'id' => $existing['id'],
                                'data' => $contact->toArray()
                            ];
                            $summary['updated']++;
                            $summary['success']++;
                        } else {
                            throw new Exception("Failed to update contact");
                        }
                    } else {
                        // Create new contact
                        $id = $this->contactRepository->create($contact);
                        $this->auditLogger->logCreate('contact', $id, $contact->toArray());
                        
                        $results[] = [
                            'index' => $index + 1,
                            'success' => true,
                            'action' => 'created',
                            'id' => $id,
                            'data' => array_merge($contact->toArray(), ['id' => $id])
                        ];
                        $summary['created']++;
                        $summary['success']++;
                    }
                    
                } catch (Exception $e) {
                    $results[] = [
                        'index' => $index + 1,
                        'success' => false,
                        'action' => 'error',
                        'errors' => ['general' => $e->getMessage()],
                        'data' => $contactData
                    ];
                    $summary['errors']++;
                }
            }
            
            $this->db->commit();
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw new Exception("Import failed: " . $e->getMessage());
        }
        
        return [
            'results' => $results,
            'summary' => $summary
        ];
    }
    
    public function downloadCsv(string $filename, string $csvContent): void 
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($csvContent));
        
        echo $csvContent;
        exit;
    }
}