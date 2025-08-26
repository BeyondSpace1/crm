<?php
/**
 * Contact entity class
 */
class Contact
{
    private $id;
    private $name;
    private $email;
    private $phone;
    private $company;
    private $tags;
    private $createdAt;
    private $updatedAt;
    private $deletedAt;

    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->fill($data);
        }
    }

    public function fill(array $data): void
    {
        $this->id        = $data['id'] ?? null;
        $this->name      = $data['name'] ?? '';
        $this->email     = $data['email'] ?? '';
        $this->phone     = $data['phone'] ?? '';
        $this->company   = $data['company'] ?? '';
        $this->tags      = $data['tags'] ?? [];
        $this->createdAt = $data['created_at'] ?? null;
        $this->updatedAt = $data['updated_at'] ?? null;
        $this->deletedAt = $data['deleted_at'] ?? null;

        // Handle JSON tags
        if (is_string($this->tags)) {
            $this->tags = json_decode($this->tags, true) ?? [];
        }
    }

    // Getters (always return string, never null)
    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name ?? ''; }
    public function getEmail(): string { return $this->email ?? ''; }
    public function getPhone(): string { return $this->phone ?? ''; }
    public function getCompany(): string { return $this->company ?? ''; }
    public function getTags(): array { return $this->tags ?? []; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function getUpdatedAt(): ?string { return $this->updatedAt; }
    public function getDeletedAt(): ?string { return $this->deletedAt; }

    // Setters
    public function setName(string $name): void { $this->name = $name; }
    public function setEmail(string $email): void { $this->email = $email; }
    public function setPhone(string $phone): void { $this->phone = $phone; }
    public function setCompany(string $company): void { $this->company = $company; }

    public function setTags($tags): void
    {
        if (is_string($tags)) {
            // Parse comma-separated tags
            $tags = array_map('trim', explode(',', $tags));
        }
        if (is_array($tags)) {
            $this->tags = array_values(array_unique(array_filter($tags)));
        } else {
            $this->tags = [];
        }
    }

    public function softDelete(): void
    {
        $this->deletedAt = date('Y-m-d H:i:s');
    }

    public function restore(): void
    {
        $this->deletedAt = null;
    }

    public function isDeleted(): bool
    {
        return !is_null($this->deletedAt);
    }

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name ?? '',
            'email'      => $this->email ?? '',
            'phone'      => $this->phone ?? '',
            'company'    => $this->company ?? '',
            'tags'       => $this->tags ?? [],
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'deleted_at' => $this->deletedAt
        ];
    }

    public function validate(): array
    {
        $errors = [];

        // Name validation
        if (empty($this->name) || strlen($this->name) < 2) {
            $errors['name'] = 'Name must be at least 2 characters long';
        }

        // Email validation
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }

        // Phone validation (7-20 characters, allow +, -, space, digits)
        if (!empty($this->phone)) {
            if (!preg_match('/^[\\d\\s\\+\\-]{7,20}$/', $this->phone)) {
                $errors['phone'] = 'Phone must be 7-20 characters and contain only digits, spaces, +, -';
            }
        }

        return $errors;
    }

    public function getTagsAsString(): string
    {
        return implode(', ', $this->tags ?? []);
    }

    public function getTagsAsJson(): string
    {
        return json_encode($this->tags ?? []);
    }
}
