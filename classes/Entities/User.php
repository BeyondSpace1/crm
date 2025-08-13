<?php
// entities/User.php
class User {
    public $id;
    public $username;
    public $email;
    public $password;
    public $role;

    public function __construct($id, $username, $email, $password, $role) {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
    }
}
