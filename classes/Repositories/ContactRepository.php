<?php
// repositories/ContactRepository.php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../entities/Contact.php';

class ContactRepository {

    public function getAll(bool $includeDeleted = false): array {
        if ($includeDeleted) {
            $stmt = pdo()->query("SELECT * FROM contacts");
        } else {
            $stmt = pdo()->query("SELECT * FROM contacts WHERE deleted_at = 0");
        }
        $contacts = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $contacts[] = new Contact($row['id'], $row['name'], $row['email'], $row['phone'], $row['company'], $row['tags']);
        }
        return $contacts;
    }

    public function getById(int $id): ?Contact {
        $stmt = pdo()->prepare("SELECT * FROM contacts WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return new Contact($row['id'], $row['name'], $row['email'], $row['phone'], $row['company'], $row['tags']);
        }
        return null;
    }

    // public function create(Contact $contact): bool {
    //     $stmt = pdo()->prepare("INSERT INTO contacts (name, email, phone, company, tag) VALUES (:name, :email, :phone, :company, :tag)");
    //     return $stmt->execute([
    //         'name'    => $contact->name,
    //         'email'   => $contact->email,
    //         'phone'   => $contact->phone,
    //         'company' => $contact->company,
    //         'tags'     => $contact->tags
    //     ]);
    // }
    public function create(Contact $contact): int|false {
        $stmt = pdo()->prepare(
            "INSERT INTO contacts (name, email, phone, company, tags) 
            VALUES (:name, :email, :phone, :company, :tags)"
        );
        $result = $stmt->execute([
            'name'    => $contact->name,
            'email'   => $contact->email,
            'phone'   => $contact->phone,
            'company' => $contact->company,
            'tags'     => $contact->tags
        ]);
        return $result ? (int)pdo()->lastInsertId() : false;
    }


    public function update(Contact $contact): bool {
        $stmt = pdo()->prepare("UPDATE contacts SET name = :name, email = :email, phone = :phone, company = :company, tags = :tags WHERE id = :id");
        return $stmt->execute([
            'id'      => $contact->id,
            'name'    => $contact->name,
            'email'   => $contact->email,
            'phone'   => $contact->phone,
            'company' => $contact->company,
            'tags'     => $contact->tags
        ]);
    }

    public function softDelete(int $id): bool {
        $stmt = pdo()->prepare("UPDATE contacts SET deleted_at = CURRENT_TIMESTAMP WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
