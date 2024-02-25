<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=personaldrive", "root", "");

    class UserInfoUpdater {
        private $pdo;
        
        public function __construct($pdo) {
            $this->pdo = $pdo;
        }
        
        public function updateProfile($user_id, $first_name, $last_name, $age, $course, $block) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM student_information WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $user_exists = $stmt->fetch();
    
                if ($user_exists) {
                    $stmt = $this->pdo->prepare("UPDATE student_information SET first_name = ?, last_name = ?, age = ?, course = ?, block = ? WHERE user_id = ?");
                    $stmt->execute([$first_name, $last_name, $age, $course, $block, $user_id]);
                } else {
                    $stmt = $this->pdo->prepare("INSERT INTO student_information (first_name, last_name, age, course, block, user_id) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$first_name, $last_name, $age, $course, $block, $user_id]);
                }
                return true;
            } catch (PDOException $e) {
                return "Database error: " . $e->getMessage();
            }
        }
    }

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
