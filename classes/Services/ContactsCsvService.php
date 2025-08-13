<?php
// services/ContactsCsvService.php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../entities/Contact.php';

class ContactsCsvService {

    public static function importCSV(array $file): array {
        $results = ['success'=>[], 'failed'=>[]];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $results['failed'][] = 'File upload error';
            return $results;
        }

        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            $results['failed'][] = 'Cannot open CSV file';
            return $results;
        }

        $header = fgetcsv($handle);
        while ($row = fgetcsv($handle)) {
            $data = array_combine($header, $row);

            // Create contact object
            $contact = new Contact(
                null,
                $data['name'] ?? '',
                $data['email'] ?? '',
                $data['phone'] ?? '',
                $data['company'] ?? '',
                $data['tag'] ?? ''
            );

            try {
                $stmt = pdo()->prepare(
                    "INSERT INTO contacts (name, email, phone, company, tag) 
                     VALUES (:name,:email,:phone,:company,:tag)"
                );
                $stmt->execute([
                    'name'=>$contact->name,
                    'email'=>$contact->email,
                    'phone'=>$contact->phone,
                    'company'=>$contact->company,
                    'tag'=>$contact->tag
                ]);
                $results['success'][] = $contact->email;
            } catch (PDOException $e) {
                $results['failed'][] = $contact->email;
            }
        }
        fclose($handle);
        return $results;
    }

    public static function exportCSV(): string {
        $stmt = pdo()->query("SELECT name,email,phone,company,tag FROM contacts WHERE deleted_at IS NULL");
        $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $filename = 'contacts_export_'.date('Ymd_His').'.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="'.$filename.'"');

        $output = fopen('php://output', 'w');
        fputcsv($output, array_keys($contacts[0] ?? []));
        foreach ($contacts as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }
}
