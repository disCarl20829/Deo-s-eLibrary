<?php
include __DIR__ . '/db_connect.php';
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: SignIn.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $const->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_username = trim($_POST['username']);
    $new_email = trim($_POST['email']);
    $new_password = $_POST['password'];
    $new_description = $_POST['description'];

    $stmt = $const->prepare(
        "SELECT user_id FROM users WHERE (user_name = ? OR user_email = ?) AND user_id != ?"
    );
    $stmt->bind_param("ssi", $new_username, $new_email, $user_id);
    $stmt->execute();
    $check = $stmt->get_result();

    if ($check->num_rows > 0) {
        $error_message = "Username or email already taken.";
    } else {
        if (empty($new_password)) {
            $stmt = $const->prepare(
                "UPDATE users SET user_name = ?, user_email = ? WHERE user_id = ?"
            );
            $stmt->bind_param("ssi", $new_username, $new_email, $user_id);
        } else {
            $hashed_pass = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt = $const->prepare(
                "UPDATE users SET user_name = ?, user_email = ?, user_password = ?, user_description = ? WHERE user_id = ?"
            );
            $stmt->bind_param("ssssi", $new_username, $new_email, $hashed_pass, $user_description, $user_id);
        }

        if ($stmt->execute()) {
            $_SESSION['username'] = $new_username;
            $message = "Settings updated successfully!";
            $user['user_name'] = $new_username;
            $user['user_email'] = $new_email;
            $user['user_description'] = $new_description;
        } else {
            $error_message = "Failed to update settings.";
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
        <nav class="main-nav">
            <ul>
                <li><a href="Homepage.php" class="active">Homepage</a></li>
                <li><a href="Homepage.php#categories">Categories</a></li>
                <li><a href="saved_books.php">Saved Books</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="Settings.php">Settings</a></li>
                <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                    <li><a href="signout.php">Logout</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main class="auth-container">
        <div class="form-card">
            <h2>Settings</h2>
            <form method="post" class="auth-form">

                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($user['user_name']); ?>"required>
                </div>
                    
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['user_email']); ?>"
                        required>
                </div>

                <div class="form-group">
                    <label>New Password (optional)</label>
                    <input type="password" id="reg-password" name="password"
                        placeholder="Leave blank to keep current password">
                </div>

                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" id="reg-confirm-password" placeholder="Confirm new password">
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea id="reg-description" name="description" placeholder="About me"><?php echo htmlspecialchars($user['user_description']); ?></textarea>
                </div>

                <button type="submit" class="submit-button">Save Changes</button>
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

    <div id="notif"
        style=" display:none; position:fixed; top:20px; right:20px; background:#4CAF50; color:white; padding:12px 18px; border-radius:10px; z-index:1000; font-size:14px;">
    </div>

    <script>
        const form = document.querySelector(".auth-form");
        const password = document.getElementById("reg-password");
        const confirmPassword = document.getElementById("reg-confirm-password");

        function showNotif(msg, success = true) {
            const notif = document.getElementById("notif");
            notif.textContent = msg;
            notif.style.background = success ? "#4CAF50" : "#e74c3c";
            notif.style.display = "block";
            setTimeout(() => notif.style.display = "none", 2000);
        }

        form.addEventListener("submit", function (e) {
            if (password.value !== "" && password.value !== confirmPassword.value) {
                e.preventDefault();
                showNotif("Passwords do not match!", false);
            }
        });

        <?php if (!empty($message)): ?>
            showNotif("<?php echo addslashes($message); ?>");
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            showNotif("<?php echo addslashes($error_message); ?>", false);
        <?php endif; ?>
    </script>

</body>

</html>