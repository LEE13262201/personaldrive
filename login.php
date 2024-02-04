<?php
require_once 'AuthenticatorLogin.php';
require_once 'AuthenticatorRegister.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        // Login form submitted
        $loginAuthenticator = new AuthenticatorLogin();
        $email = $_POST['loginEmail'];
        $password = $_POST['loginPassword'];

        $user = $loginAuthenticator->loginUser($email, $password);

        if ($user) {
            session_start();
            $_SESSION['user_id'] = $user['userID'];
            header('Location: dashboard.php');
            exit();
        } else {
            $loginError = "Invalid email or password";
        }
    } elseif (isset($_POST['register'])) {
        // Registration form submitted
        $registerAuthenticator = new AuthenticatorRegister();
        $email = $_POST['registerEmail'];
        $password = $_POST['registerPassword'];
        $confirmPassword = $_POST['confirmPassword'];

        if ($password !== $confirmPassword) {
            $registrationError = "Password and Confirm Password do not match.";
        } else {
            if ($registerAuthenticator->registerUser($email, $password)) {
                header('Location: index.php'); // Redirect to the login page after successful registration
                exit();
            } else {
                $errorInfo = $registerAuthenticator->getLastErrorInfo();
                if ($errorInfo[1] == 1062) {
                    // MySQL error code for duplicate entry
                    $registrationError = "Registration failed. Email already in use.";
                } else {
                    $registrationError = "Registration failed. Please try again later.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>Personal Drive</title>
    <link rel="icon" href="logo.png" type="image/png">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-form,
        .registration-form {
            width: 100%;
            max-width: 400px;
            display: block;
            margin: auto;
        }

        .toggle-password {
            cursor: pointer;
            position: absolute;
            right: 30px;
            top: 50%;
            transform: translateY(-50%);
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="login-form">
            <!-- Login Form -->
            <div class="card">
                <div class="card-body">
                    <img class="rounded mx-auto d-block mb-3" src="logo.png" alt="logo" height="100" width="100">
                    <h5 class="card-title text-center mb-3">Login to Personal Drive</h5>
                    <form method="post" action="">
                        <div class="form-group">
                            <input type="email" class="form-control" name="loginEmail" id="loginEmail" placeholder="Email" required>
                        </div>
                        <div class="form-group">
                            <input type="password" class="form-control" name="loginPassword" id="loginPassword" placeholder="Password" required>
                            <span class="toggle-password" onclick="togglePasswordVisibility('loginPassword')">
                                <img width="20" height="20" src="https://img.icons8.com/fluency-systems-regular/48/visible--v1.png" alt="visible--v1" />
                            </span>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" name="login">Login to Personal Drive</button>
                        <p class="mt-2 text-center">Don't have an account? <a href="register.php">Create Account</a></p>
                    </form>
                    <?php if (isset($loginError)) {
                        echo "<p class='text-danger'>$loginError</p>";
                    } ?>
                </div>
            </div>
        </div>
        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

        <script>
            function togglePasswordVisibility() {
                const passwordInput = document.getElementById('loginPassword');
                const eyeIcon = document.querySelector('.toggle-password img');

                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    // Adjust styles or add styles as needed
                    eyeIcon.style.filter = 'brightness(0.7)';
                } else {
                    passwordInput.type = 'password';
                    eyeIcon.style.filter = 'brightness(1)';
                }
            }
        </script>

</body>

</html>