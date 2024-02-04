<?php
require_once 'DB.php';

class AuthenticatorRegister
{
    private $conn;
    private $table_name = "users";
    private $lastErrorInfo;

    public function __construct()
    {
        $db = new DB();
        $this->conn = $db->getConnection();
    }

    public function registerUser($email, $password)
    {
        // Validate password strength
        $passwordStrengthResult = $this->validatePasswordStrength($password);
        if ($passwordStrengthResult !== true) {
            return $passwordStrengthResult;
        }

        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Insert user into the database
        $query = "INSERT INTO " . $this->table_name . " (email, password) VALUES (:email, :password)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);

        try {
            if ($stmt->execute()) {
                return true;
            } else {
                $this->lastErrorInfo = $stmt->errorInfo();
                return false;
            }
        } catch (PDOException $e) {
            // Check for unique constraint violation (error code 1062)
            if ($e->getCode() == 23000 && $e->errorInfo[1] == 1062) {
                $this->lastErrorInfo = $e->errorInfo;
                return false;
            } else {
                throw $e; // Re-throw other PDOExceptions
            }
        }
    }

    public function getLastErrorInfo()
    {
        return $this->lastErrorInfo;
    }

    public function validatePasswordStrength($password)
    {
        // Minimum length check
        if (mb_strlen($password) < 8) {
            return "Password should be at least 8 characters long.";
        }
        // Check for at least one lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            return "Password should contain at least one lowercase letter.";
        }

        // Check for at least one uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            return "Password should contain at least one uppercase letter.";
        }

        // Check for at least one digit
        if (!preg_match('/[0-9]/', $password)) {
            return "Password should contain at least one digit.";
        }
        return true;
    }
}
