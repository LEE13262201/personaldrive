<?php
session_start();
require_once 'connection.php';
require_once 'File.php';
$pdo = new PDO("mysql:host=localhost;dbname=personaldrive", "root", "");

// Retrieve deleted files from the recovered_files table
$stmt = $pdo->query("SELECT * FROM recovered_files");
$deletedFiles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle actions (recover or permanently delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['file_id'])) {
        $fileId = $_POST['file_id'];

        if ($_POST['action'] === 'recover') {
            // Recover file: Move from recovered_files table to files table
            $stmt = $pdo->prepare("SELECT * FROM recovered_files WHERE id = ?");
            $stmt->execute([$fileId]);
            $fileInfo = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($fileInfo) {
                // Insert into files table
                $stmt = $pdo->prepare("INSERT INTO files (filename, filepath, upload_date, userID) VALUES (?, ?, ?, ?)");
                $stmt->execute([$fileInfo['filename'], $fileInfo['filepath'], $fileInfo['deleted_at'], $fileInfo['userID']]);

                // Delete from recovered_files table
                $stmt = $pdo->prepare("DELETE FROM recovered_files WHERE id = ?");
                $stmt->execute([$fileId]);

                // Redirect to recover_file.php after successful recovery
                header("Location: recover_file.php");
                exit();
            }
        } elseif ($_POST['action'] === 'delete_permanently') {
            // Permanently delete file: Remove from recovered_files table
            $stmt = $pdo->prepare("DELETE FROM recovered_files WHERE id = ?");
            $stmt->execute([$fileId]);

            // Redirect to recover_file.php after deletion
            header("Location: recover_file.php");
            exit();
        }
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recovered Files</title>
    <link rel="icon" href="logo.png" type="image/png">
    <link rel="stylesheet" href="dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>

<body>
    <nav class="navbar bg-body-tertiary">
        <div class="container-fluid">
            <a href="dashboard.php" class="btn btn-sm"><svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="30" height="30" viewBox="0 0 24 24">
                    <path d="M 12 2.0996094 L 1 12 L 4 12 L 4 21 L 11 21 L 11 15 L 13 15 L 13 21 L 20 21 L 20 12 L 23 12 L 12 2.0996094 z M 12 4.7910156 L 18 10.191406 L 18 11 L 18 19 L 15 19 L 15 13 L 9 13 L 9 19 L 6 19 L 6 10.191406 L 12 4.7910156 z"></path>
                </svg></a>
            <a href="#" class="btn" id="logoutButton"><img width="50" height="50" src="https://img.icons8.com/cotton/64/logout-rounded--v2.png" alt="logout-rounded--v2" /></a>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="text-danger">Recovered Files:</h2>
        <table class="table text-center mt-3 table-bordered table-striped table-hover">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">Filename</th>
                    <th scope="col">Deleted At</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($deletedFiles as $file) : ?>
                    <tr>
                        <td><?php echo htmlentities($file['filename'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo $file['deleted_at']; ?></td>
                        <td>
                            <form method="post" action="">
                                <input type="hidden" name="file_id" value="<?php echo $file['id']; ?>">
                                <button type="submit" class="btn btn-success" name="action" value="recover">Recover</button>
                                <button type="submit" class="btn btn-danger" name="action" value="delete_permanently">Delete Permanently</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <!-- Logout Confirmation Modal -->
    <div class="modal fade" tabindex="-1" role="dialog" id="logoutConfirmationModal">
        <div class="modal-dialog modal-dialog-centered text-center" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Logout Confirmation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="h4">Do you wish to logout?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="post" action="">
                        <a href="logout.php" class="btn btn-danger" id="logoutButton">Logout</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Show the logout confirmation modal when the user clicks the logout button
        $(document).ready(function() {
            $('#logoutButton').on('click', function(e) {
                e.preventDefault(); // Prevent the default behavior of the link
                $('#logoutConfirmationModal').modal('show');
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>

</html>