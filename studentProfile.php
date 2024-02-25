<?php
session_start();
require_once 'connection.php';
require_once 'UserInfoUpdater.php';
require_once 'PictureUploader.php';

if (!isset($userId)) {
    header("Location: login.php");
    exit();
}

$update_success = false;

$userInfoUpdater = new UserInfoUpdater($pdo);

$pictureUploader = new PictureUploader($pdo);

$update_success = isset($update_success) ? $update_success : false;


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_student_info'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $age = $_POST['age'];
    $course = $_POST['course'];
    $block = $_POST['block'];

    $result = $userInfoUpdater->updateProfile($_SESSION['user_id'], $first_name, $last_name, $age, $course, $block);

    if ($result === true) {
        $_SESSION['profile_success'] = true;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $update_error = $result;
    }
}

unset($_SESSION['profile_success']);

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT first_name, last_name, age, course, block FROM student_information WHERE user_id = ?");
$stmt->execute([$user_id]);
$user_info = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Personal Drive</title>
    <link rel="icon" href="logo.png" type="image/png">
    <link rel="stylesheet" href="profile.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
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

    <?php
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT file_name FROM student_profile WHERE user_id = ? ORDER BY upload_timestamp DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $profile_picture = ($row && isset($row['file_name'])) ? $row['file_name'] : '';
    ?>

    <div class="container-fluid mt-5 mx-auto">
        <div class="profile-picture" style="text-align: center;">
            <?php if (!empty($profile_picture)) : ?>
                <img src="<?php echo $profile_picture; ?>" alt="Profile Picture" width="300" height="300" style="border-radius:10px;">
            <?php else : ?>
                <img src="default.png" alt="Default Profile Picture" width="300" height="300" style="border-radius:10px;">
            <?php endif; ?>
        </div>
    </div>

    <div class="container-fluid mt-5" style="text-align: center;">
        <form id="uploadForm" enctype="multipart/form-data" method="POST" action="">
            <p>You may upload a profile picture using the buttons below</p>
            <label for="fileInput" class="custom-file-upload">
                <input type="file" id="fileInput" name="profilePicture" style="display: none;">
                Choose File
            </label>
            <button type="submit" name="upload_picture" class="btn btn-primary">Upload</button>
        </form>
    </div>

    <?php

    if (empty($user_info)) {
        $user_info = array(
            'first_name' => 'GC-Student',
            'last_name' => 'GC-Student',
            'age' => 'Not Applicable',
            'course' => 'Not Applicable',
            'block' => 'Not Applicable'
        );
    }
    ?>
    <div class="container mt-5">
        <h4>User Information</h4>
        <table class="table table-bordered">
            <tbody>
                <tr>
                    <th scope="row">First Name</th>
                    <td><?php echo $user_info['first_name']; ?></td>
                </tr>
                <tr>
                    <th scope="row">Last Name</th>
                    <td><?php echo $user_info['last_name']; ?></td>
                </tr>
                <tr>
                    <th scope="row">Age</th>
                    <td><?php echo $user_info['age']; ?></td>
                </tr>
                <tr>
                    <th scope="row">Course</th>
                    <td><?php echo $user_info['course']; ?></td>
                </tr>
                <tr>
                    <th scope="row">Block</th>
                    <td><?php echo $user_info['block']; ?></td>
                </tr>
            </tbody>
        </table>
        </table>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">
            Edit Profile
        </button>

        <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <input type="text" class="form-control" id="firstName" name="first_name" placeholder="Enter First Name">
                            </div>
                            <div class="mb-3">
                                <input type="text" class="form-control" id="lastName" name="last_name" placeholder="Enter Last Name">
                            </div>
                            <div class="mb-3">
                                <input type="number" class="form-control" id="age" name="age" placeholder="Enter Age">
                            </div>
                            <div class="mb-3">
                                <input type="text" class="form-control" id="course" name="course" placeholder="Enter Course">
                            </div>
                            <div class="mb-3">
                                <input type="text" class="form-control" id="block" name="block" placeholder="Enter Block">
                            </div>
                            <button type="submit" class="btn btn-primary w-100" name="update_student_info">Save Changes</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

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

        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

        <script>
            // Show the logout confirmation modal when the user clicks the logout button
            $(document).ready(function() {
                $('#logoutButton').on('click', function(e) {
                    e.preventDefault();
                    $('#logoutConfirmationModal').modal('show');
                });
            });
        </script>

</body>

</html>