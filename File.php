<?php

class File
{
    private $pdo;
    private $userId;

    public function __construct($pdo, $userId)
    {
        $this->pdo = $pdo;
        $this->userId = $userId;
    }

    public function upload($file, $userId)
    {
        $filename = $file['name'];
        $filetmp = $file['tmp_name'];
        $filepath = 'uploads/' . $filename;

        // Check if file size exceeds the maximum limit (5 MB)
        $maxFileSize = 10 * 1024 * 1024;
        if ($file['size'] > $maxFileSize) {
            $_SESSION['error_message'] = "File exceeded the maximum file size ($maxFileSize)";
            return;
        }

        move_uploaded_file($filetmp, $filepath);

        $stmt = $this->pdo->prepare("INSERT INTO files (filename, filepath, upload_date, userID) VALUES (?, ?, NOW(), ?)");
        $stmt->execute([$filename, $filepath, $userId]);

        $_SESSION['success_message'] = "File uploaded successfully.";
        // Redirect to the same page after successful upload
        header("Location: {$_SERVER['PHP_SELF']}");
        exit();
    }

    public function getAllFilesForUser($userId, $search = null)
    {
        $sql = "SELECT * FROM files WHERE userID = ?";
        $params = [$userId];

        if ($search !== null) {
            $sql .= " AND filename LIKE ?";
            $params[] = "%$search%";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function deleteFile($fileId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM files WHERE id = ?");
        $stmt->execute([$fileId]);
        $fileInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($fileInfo) {
            // Insert the file info into recovered_files table before deleting
            $stmt = $this->pdo->prepare("INSERT INTO recovered_files (filename, filepath, userID) VALUES (?, ?, ?)");
            $stmt->execute([$fileInfo['filename'], $fileInfo['filepath'], $this->userId]);

            // Delete the file from the files table
            $stmt = $this->pdo->prepare("DELETE FROM files WHERE id = ?");
            $stmt->execute([$fileId]);

            // Redirect to the same page after successful deletion
            header("Location: {$_SERVER['PHP_SELF']}");
            exit();
        }
    }

    public function downloadFile($fileId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM files WHERE id = ?");
        $stmt->execute([$fileId]);
        $fileInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($fileInfo) {
            // Set appropriate headers for download
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $fileInfo['filename'] . '"');

            // Output the file content
            readfile($fileInfo['filepath']);
            exit;
        }
    }

    public function viewFile($fileId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM files WHERE id = ? AND userID = ?");
        $stmt->execute([$fileId, $this->userId]);
        $fileInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($fileInfo) {
            // Set appropriate headers for viewing
            header('Content-Type: ' . mime_content_type($fileInfo['filepath']));
            header('Content-Disposition: inline; filename="' . $fileInfo['filename'] . '"');
            readfile($fileInfo['filepath']);
            exit;
        } else {
            // File not found or unauthorized access
            $_SESSION['error'] = "File not found or unauthorized access";
            header("Location: dashboard.php");
            exit;
        }
    }

    public function renameFile($fileId, $newFilename)
    {
        // Retrieve file information
        $stmt = $this->pdo->prepare("SELECT * FROM files WHERE id = ?");
        $stmt->execute([$fileId]);
        $fileInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($fileInfo) {
            $oldFilepath = $fileInfo['filepath'];

            // Get the file extension from the original filename
            $extension = pathinfo($oldFilepath, PATHINFO_EXTENSION);

            // Append the extension to the new filename if it doesn't have one
            $newFilenameWithExtension = $newFilename . ($extension ? '.' . $extension : '');

            // Generate the new filepath
            $newFilepath = 'uploads/' . $newFilenameWithExtension;

            // Rename the file on the server
            if (rename($oldFilepath, $newFilepath)) {
                // Update file record in the database
                $stmt = $this->pdo->prepare("UPDATE files SET filename = ?, filepath = ? WHERE id = ?");
                $stmt->execute([$newFilenameWithExtension, $newFilepath, $fileId]);

                return true; // Success
            } else {
                // Handle renaming failure
                echo "Error: Unable to rename the file on the server.";
            }
        }

        return false; // File not found
    }
}
