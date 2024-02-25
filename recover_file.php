<?php
session_start();
require_once 'connection.php';
require_once 'File.php';
$pdo = new PDO("mysql:host=localhost;dbname=personaldrive", "root", "");

// Retrieve deleted files from the recovered_files table
$stmt = $pdo->query("SELECT * FROM recovered_files");
$deletedFiles = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!isset($userId)) {
    header("Location: login.php");
    exit();
}

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

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT first_name, last_name, age, course, block FROM student_information WHERE user_id = ?");
$stmt->execute([$user_id]);
$user_info = $stmt->fetch(PDO::FETCH_ASSOC);

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
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <div>
                <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
                    <div class="offcanvas-header">
                        <?php
                        if (empty($user_info)) {
                            $user_name = "GC-Student";
                        } else {
                            $user_name = $user_info['first_name'];
                        }
                        ?>
                        <h5 class="offcanvas-title mx-auto" id="offcanvasNavbarLabel"><b><?php echo $user_name; ?></b></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body">
                        <ul class="navbar-nav justify-content-start flex-grow-1 ps-3">
                            <li class="nav-item">
                                <a class="nav-link" aria-current="page" href="dashboard.php">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="studentProfile.php">Profile</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="recover_file.php">Recently Deleted</a>
                            </li>
                            <li class="nav-item">
                                <button id="logoutButton" class="btn mt-3 mx-auto" data-bs-toggle="modal" data-bs-target="#logoutConfirmationModal"><img width="30" height="30" src="https://img.icons8.com/ios/50/exit--v1.png" alt="exit--v1" /></button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <p class="fs-1 mb-0 font-bold personalDrive" style="font-weight: 500;">Student Personal Drive</p>
            <p class="fs-1 mb-0"></p>
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
                e.preventDefault();
                $('#logoutConfirmationModal').modal('show');
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>

</html>