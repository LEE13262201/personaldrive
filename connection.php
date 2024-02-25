<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "personaldrive";

try {
    // Create a PDO connection
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Include the File class
    require_once 'File.php';

    // Get the user ID from the session
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    // Create an instance of the File class with PDO and user ID
    $fileHandler = new File($pdo, $userId);

    // File upload
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
        $fileHandler->upload($_FILES['file'], $userId);
    }

    $files = $fileHandler->getAllFilesForUser($userId, isset($_GET['search']) ? $_GET['search'] : null);

    // File deletion
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['file_id'])) {
        $fileId = $_GET['file_id'];
        $fileHandler->deleteFile($fileId);
    }

    // File download
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'download' && isset($_GET['file_id'])) {
        $fileId = $_GET['file_id'];
        $fileHandler->downloadFile($fileId);
    }

    // File view
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'view' && isset($_GET['file_id'])) {
        $fileId = $_GET['file_id'];
        $fileHandler->viewFile($fileId);
    }

    // Handle file renaming
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'rename') {
        $fileId = $_POST['file_id'];
        $newFilename = $_POST['new_filename'];

        if ($fileHandler->renameFile($fileId, $newFilename)) {
            // Redirect to the same page after successful renaming
            header("Location: {$_SERVER['PHP_SELF']}");
            exit();
        } else {
            // Handle the case where the file was not found (or other errors)
            $_SESSION['error_message'] = "Error: File not found or renaming failed.";
            header("Location: {$_SERVER['PHP_SELF']}");
            exit();
        }
    }

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}

// Close the PDO connection
$pdo = null;
