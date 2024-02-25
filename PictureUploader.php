<?php
class PictureUploader
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function uploadProfilePicture($file, $userId)
    {
        $uploadDir = 'profile_images/';
        $uploadFile = $uploadDir . basename($file['name']);

        $imageFileType = strtolower(pathinfo($uploadFile, PATHINFO_EXTENSION));
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            return ['status' => 'error', 'message' => 'Only JPG, JPEG, PNG & GIF files are allowed.'];
        }

        if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
            $stmt = $this->pdo->prepare("INSERT INTO student_profile (file_name, user_id) VALUES (?, ?)");
            $stmt->execute([$uploadFile, $userId]);

            return ['status' => 'success', 'message' => 'File uploaded successfully.', 'file_path' => $uploadFile];
        } else {
            return ['status' => 'error', 'message' => 'Failed to move uploaded file.'];
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_picture'])) {
    if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] === UPLOAD_ERR_OK) {
        require_once 'PictureUploader.php';
        $pictureUploader = new PictureUploader($pdo);
        $file = $_FILES['profilePicture'];
        $uploadResult = $pictureUploader->uploadProfilePicture($file, $_SESSION['user_id']);
        if ($uploadResult['status'] === 'success') {
            $update_success = true;
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $update_error = $uploadResult['message'];
        }
    } else {
        $update_error = "Failed to upload profile picture.";
    }
}
