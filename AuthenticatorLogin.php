<?php

require_once 'DB.php';

class AuthenticatorLogin {
    private $conn;
    private $table_name = "users";

    public function __construct() {
        $db = new DB();
        $this->conn = $db->getConnection();
    }

    public function loginUser($email, $password) {
        $query = "SELECT userID, email, password FROM " . $this->table_name . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        } else {
            return false;
        }
    }
}
