<?php
include __DIR__ . '/db_connect.php';
session_start();

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: Homepage.php");
    exit();
}

$login_error = "";
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_name = $_POST['user_name'] ?? '';
    $user_pass = $_POST['user_pass'] ?? '';

    $stmt = $const->prepare("SELECT * FROM users WHERE user_name = ?");
    $stmt->bind_param("s", $user_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($user_pass, $user['user_password'])) {
        $_SESSION['user_name'] = $user['user_name'];
        $_SESSION['user_id']   = $user['user_id'];
        $_SESSION['logged_in'] = true;
        $message = "Logging in. Please wait";
    } else {
        $login_error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eLibrary - Sign In</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <header class="main-header">
        <h1>eLibrary</h1>
    </header>

    <main class="auth-container">
        <div class="form-card">
            <h2>User Sign In</h2>

            <form action="SignIn.php" method="post" class="auth-form" id="signinForm">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="user_name" required>
                </div>

                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="user_pass" required>
                </div>

                <button type="submit" class="submit-button" id="submit">Submit</button>
            </form>

            <!-- NOTIFICATION -->
            <div id="notif" style="
            display:none;
            background:#d9534f;
            color:white;
            padding:12px;
            border-radius:8px;
            margin-top:12px;
            font-size:14px;
        "></div>

            <p class="form-footer">
                Don't have an account?
                <a href="create_account.php">Create Account</a>
            </p>
        </div>
    </main>

    <footer>
        <p>&copy; 2025 eLibrary. All rights reserved.</p>
    </footer>

    <script>
        function showNotif(message, type = "info") {
            const notif = document.getElementById("notif");
            notif.textContent = message;

            // Set color depending on type
            switch (type) {
                case "success":
                    notif.style.background = "#4CAF50"; // green
                    notif.style.color = "white";
                    break;
                case "error":
                    notif.style.background = "#d9534f"; // red
                    notif.style.color = "white";
                    break;
                case "warning":
                    notif.style.background = "#f0ad4e"; // orange
                    notif.style.color = "white";
                    break;
                default:
                    notif.style.background = "#5bc0de"; // blue
                    notif.style.color = "white";
            }

            notif.style.display = "block";

            setTimeout(() => {
                notif.style.display = "none";
            }, 2000);
        }

        <?php if (!empty($login_error)): ?>
            window.addEventListener("DOMContentLoaded", () => {
                showNotif("<?php echo addslashes($login_error); ?>", "error");
            });
        <?php endif; ?>

        <?php if (!empty($message)): ?>
            window.addEventListener("DOMContentLoaded", () => {
                showNotif("<?php echo addslashes($message); ?>", "success");
                setTimeout(() => {
                    window.location.href = "Homepage.php";
                }, 1500);
            });
        <?php endif; ?>
    </script>

</body>

</html>