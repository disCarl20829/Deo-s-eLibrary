<?php
include __DIR__ . '/db_connect.php';
session_start();

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: Homepage.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $user_name = $_POST['username'];
    $user_pass = $_POST['password'];

    $stmt = $const->prepare("SELECT * FROM users WHERE user_name = ? OR user_email = ?");
    $stmt->bind_param("ss", $user_name, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $error_message = "Username or email already taken.";
    } else {
        $hashed_pass = password_hash($user_pass, PASSWORD_BCRYPT);

        $stmt = $const->prepare("INSERT INTO users (user_email, user_name, user_password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $user_name, $hashed_pass);

        $stmt2 = $const->prepare("INSERT INTO shop (shop_owner) VALUES (?)");

        if ($stmt->execute()) {
            $stmt2->bind_param("s", $user_name);
            $stmt2->execute();
            $message = "Account created. Please wait.";
            header("Location: Signin.php");
        } else {
            $error_message = "Error creating account. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eLibrary - Create Account</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <header class="main-header">
        <h1>eLibrary</h1>
    </header>

    <main class="auth-container">
        <div class="form-card">
            <h2>Create New Account</h2>
            <form action="create_account.php" method="post" class="auth-form">

                <div class="form-group">
                    <label for="reg-name">Username:</label>
                    <input type="text" id="reg-name" name="username" placeholder="Enter your username" required>
                </div>

                <div class="form-group">
                    <label for="reg-email">Email:</label>
                    <input type="email" id="reg-email" name="email" placeholder="Enter a valid email address" required>
                </div>

                <div class="form-group">
                    <label for="reg-password">Password:</label>
                    <input type="password" id="reg-password" name="password" placeholder="Choose a secure password" required>
                </div>

                <div class="form-group">
                    <label for="reg-confirm-password">Confirm Password:</label>
                    <input type="password" id="reg-confirm-password" name="confirm_password" placeholder="Re-enter your password" required>
                </div>

                <button type="submit" class="submit-button" id="submit">Create Account</button>
            </form>

            <p class="form-footer">
                Already have an account?
                <a href="SignIn.php">Sign In here</a>
            </p>
        </div>
    </main>

    <footer>
        <p>&copy; 2025 eLibrary. All rights reserved.</p>
    </footer>

    <div id="notif" style=" display:none; position:fixed; top:20px; right:20px; background:#4CAF50; color:white; padding:12px 18px; border-radius:10px; z-index:1000; font-size:14px;"> </div>

    <script>
        const form = document.querySelector(".auth-form");
        const password = document.getElementById("reg-password");
        const confirmPassword = document.getElementById("reg-confirm-password");

        function showNotif(message) {
            const notif = document.getElementById("notif");
            notif.textContent = message;
            notif.style.display = "block";

            setTimeout(() => {
                notif.style.display = "none";
            }, 2000);
        }

        form.addEventListener("submit", function(e) {
            if (password.value !== confirmPassword.value) {
                e.preventDefault();
                showNotif("Passwords do not match!");
                confirmPassword.focus();
                return false;
            }
        })

        confirmPassword.addEventListener("input", function() {
            if (password.value !== confirmPassword.value) {
                confirmPassword.style.borderColor = "red";
            } else {
                confirmPassword.style.borderColor = "green";
            }
        });

        <?php if (!empty($message)): ?>
            window.addEventListener("DOMContentLoaded", () => {
                showNotif("<?php echo addslashes($message); ?>");
                setTimeout(() => {
                    window.location.href = "SignIn.php";
                }, 1500);
            });
        <?php endif; ?>
    </script>

</body>

</html>