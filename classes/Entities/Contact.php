<?php
// entities/Contact.php
class Contact {
    public $id;
    public $name;
    public $email;
    public $phone;
    public $company;
    public $tags;

    public function __construct($id, $name, $email, $phone, $company, $tags) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->phone = $phone;
        $this->company = $company;
        $this->tags = $tags;
    }
}
