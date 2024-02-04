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
            header('Location: dashboard.php'); // Redirect to the dashboard after successful login
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
            $passwordStrengthResult = $registerAuthenticator->validatePasswordStrength($password);
            if ($passwordStrengthResult !== true) {
                $registrationError = $passwordStrengthResult;
            } else {
                // Valid password, proceed with registration
                if ($registerAuthenticator->registerUser($email, $password)) {
                    $registrationSuccess = true;
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
    <link rel="stylesheet" href="register.css">
</head>

<body>
    <div class="container">
        <div class="registration-form">
            <!-- Registration Form -->
            <div class="card">
                <div class="card-body">
                <img class="rounded mx-auto d-block mb-3" src="logo.png" alt="logo" height="100" width="100">
                    <h5 class="card-title text-center mb-3">Register to Personal Drive</h5>
                    <form method="post" action="">
                        <div class="form-group">
                            <input type="email" class="form-control" name="registerEmail" id="registerEmail" placeholder="Enter email" required>
                        </div>
                        <div class="form-group position-relative">
                            <div class="password-container">
                                <input type="password" class="form-control password-input" name="registerPassword" id="registerPassword" placeholder="Password" required>
                                <span class="toggle-password" onclick="togglePasswordVisibility('registerPassword')">
                                    <img width="20" height="20" src="https://img.icons8.com/fluency-systems-regular/48/visible--v1.png" alt="visible--v1" />
                                </span>
                            </div>
                        </div>
                        <div class="form-group position-relative">
                            <div class="password-container">
                                <input type="password" class="form-control password-input" name="confirmPassword" id="confirmPassword" placeholder="Confirm Password" required>
                                <span class="toggle-password" onclick="togglePasswordVisibility('confirmPassword')">
                                    <img width="20" height="20" src="https://img.icons8.com/fluency-systems-regular/48/visible--v1.png" alt="visible--v1" />
                                </span>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success w-100" name="register">Create Account</button>
                        <p class="mt-2 text-center">Already have an account? <a href="login.php">Login</a></p>
                    </form>
                    <?php if (isset($registrationError)) {
                        echo "<p class='text-danger'>$registrationError</p>";
                    } ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade " id="registrationSuccessModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <img src="http://goactionstations.co.uk/wp-content/uploads/2017/03/Green-Round-Tick.png" alt="">
                    <h1>Account Created</h1>
                    <p>You may now proceed to login.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary btn-block" onclick="redirectToLogin()">Okay</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function togglePasswordVisibility(passwordFieldId) {
            const passwordInput = document.getElementById(passwordFieldId);
            const eyeIcon = document.querySelector(`#${passwordFieldId} ~ .toggle-password img`);

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
    <script>
    function redirectToLogin() {
        window.location.href = 'login.php';
    }

    // Show the modal after successful registration
    <?php if (isset($registrationSuccess) && $registrationSuccess) : ?>
        document.addEventListener('DOMContentLoaded', function () {
            var registrationSuccessModal = new bootstrap.Modal(document.getElementById('registrationSuccessModal'));
            registrationSuccessModal.show();
        });
    <?php endif; ?>
</script>
</body>

</html>