<?php
session_start();
require_once 'connection.php';
require_once 'File.php';

$userId = $_SESSION['user_id'];

if (!isset($userId)) {
    header("Location: login.php");
    exit();
}

$error = null;

// File upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    try {
        $fileHandler->upload($_FILES['file'], $userId);
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
    header("Location: {$_SERVER['PHP_SELF']}");
    exit();
}

// Display success message if it exists (for uploaded files)
$success = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : null;
unset($_SESSION['success_message']);

// Display error message if it exists (for uploaded files)
$error = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : null;
unset($_SESSION['error_message']);

// Check for error messages when viewing a file from File class
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Personal Drive</title>
    <link rel="icon" href="logo.png" type="image/png">
    <link rel="stylesheet" href="dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>

<body>

    <nav class="navbar bg-body-tertiary">
        <div class="container-fluid">
            <form method="post" action="" enctype="multipart/form-data">
                <input type="file" name="file" class="form-control" required>
                <button type="submit" class="btn btn-success my-2">Upload</button>
            </form>
            <a href="#" class="btn btn-danger" id="logoutButton">Logout</a>
        </div>
    </nav>

    <!-- For uploading and viewing error -->
    <?php if ($error) : ?>
        <div id="errorMessage" class="alert alert-danger text-center" role="alert">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <!-- For uploading success -->
    <?php if ($success) : ?>
        <div id="successMessage" class="alert alert-success text-center" role="alert">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <div class="container mt-3">
        <form method="get" action="">
            <div class="input-group mb-3">
                <input type="text" class="form-control" placeholder="Search files..." name="search">
                <button class="btn btn-outline-secondary" type="submit">Search</button>
            </div>
        </form>
    </div>

    <!-- List of Uploaded Files -->
    <div class="container mt-5">
        <h2 class="text-success">Uploaded Files:</h2>
        <table class="table text-center mt-3 table-bordered table-striped table-hover">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">Filename</th>
                    <th scope="col">Upload Date</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($files as $file) : ?>
                    <tr>
                        <td><?php echo htmlentities($file['filename'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo $file['upload_date']; ?></td>
                        <td>
                            <a href="?action=download&file_id=<?php echo $file['id']; ?>" class="btn btn-sm"><img width="22" height="22" src="https://img.icons8.com/windows/32/download--v1.png" alt="download--v1" /></a>
                            <a href="?action=view&file_id=<?php echo $file['id']; ?>" class="btn btn-sm"><img width="24" height="24" src="https://img.icons8.com/material-outlined/24/view-file.png" alt="view-file" /></a>
                            <a href="?action=delete&file_id=<?php echo $file['id']; ?>" class="btn btn-sm" onclick="return confirm('Are you sure?')"><svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="22" height="22" viewBox="0 0 50 50">
                                    <path d="M 7.71875 6.28125 L 6.28125 7.71875 L 23.5625 25 L 6.28125 42.28125 L 7.71875 43.71875 L 25 26.4375 L 42.28125 43.71875 L 43.71875 42.28125 L 26.4375 25 L 43.71875 7.71875 L 42.28125 6.28125 L 25 23.5625 Z"></path>
                                </svg></a>

                            <button class="btn btn-sm" data-bs-toggle="modal" data-bs-target="#renameModal<?php echo $file['id']; ?>">
                                <img width="22" height="22" src="https://img.icons8.com/windows/32/edit-file.png" alt="edit-file" />
                            </button>

                            <!-- Rename Modal -->
                            <div class="modal fade" id="renameModal<?php echo $file['id']; ?>" tabindex="-1" aria-labelledby="renameModalLabel<?php echo $file['id']; ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="renameModalLabel<?php echo $file['id']; ?>">Rename File</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <!-- Rename Form -->
                                            <form method="post" action="">
                                                <input type="hidden" name="action" value="rename">
                                                <input type="hidden" name="file_id" value="<?php echo $file['id']; ?>">
                                                <div class="mb-3">
                                                    <label for="new_filename" class="form-label"><b>New name</b></label>
                                                    <input type="text" class="form-control" id="new_filename" name="new_filename" required>
                                                </div>
                                                <button type="submit" class="btn btn-success"><svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="20" height="20" viewBox="0 0 26 26">
                                                        <path d="M 22.566406 4.730469 L 20.773438 3.511719 C 20.277344 3.175781 19.597656 3.304688 19.265625 3.796875 L 10.476563 16.757813 L 6.4375 12.71875 C 6.015625 12.296875 5.328125 12.296875 4.90625 12.71875 L 3.371094 14.253906 C 2.949219 14.675781 2.949219 15.363281 3.371094 15.789063 L 9.582031 22 C 9.929688 22.347656 10.476563 22.613281 10.96875 22.613281 C 11.460938 22.613281 11.957031 22.304688 12.277344 21.839844 L 22.855469 6.234375 C 23.191406 5.742188 23.0625 5.066406 22.566406 4.730469 Z"></path>
                                                    </svg>Done</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Rename Modal -->
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

    <div class="container mt-5 text-center">
        <h2>Lee's Personal Drive</h2>
    </div>

    <div class="container mt-5 text-center">
        <h5><i>This doesn't support large files such as videos.</i></h5>
    </div>

    <script>
        $(document).ready(function() {
            // Fade out success message after 5 seconds
            $("#successMessage").delay(5000).fadeOut(500);

            // Fade out error message after 5 seconds
            $("#errorMessage").delay(5000).fadeOut(500);
        });

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