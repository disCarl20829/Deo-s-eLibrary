<?php
include __DIR__ . '/db_connect.php';
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: SignIn.php");
    exit();
}

$book_id = isset($_GET['book_id']) ? (int) $_GET['book_id'] : 0;

if ($book_id <= 0) {
    header("Location: Homepage.php");
    exit();
}

$stmt = $const->prepare("
    SELECT 
        b.book_id,
        b.book_title,
        b.book_author,
        b.book_description,
        b.book_price,
        b.book_img_path,
        c.category_id
    FROM books b
    JOIN book_category c ON b.book_id = c.book_id
    WHERE b.book_id = ?
");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$book = $stmt->get_result()->fetch_assoc();

if (!$book) {
    die("Book not found.");
}

/* 🏷 Category mapping */
$categories = [
    1 => "Fiction",
    2 => "Educational",
    3 => "Classic"
];

$categoryName = $categories[$book['category_id']] ?? "Unknown";

// 🔍 Check if book is already saved by user
$is_saved = false;

$check = $const->prepare(
    "SELECT 1 FROM save_books WHERE user_id = ? AND book_id = ? LIMIT 1"
);
$check->bind_param("ii", $_SESSION['user_id'], $book_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    $is_saved = true;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($book['book_title']); ?> - Book Details</title>
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
                <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                    <li><a href="signout.php">Logout</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>

        <section class="product-detail-section">

            <div class="product-image-box">
                <img src="<?php echo htmlspecialchars($book['book_img_path']); ?>"
                    alt="<?php echo htmlspecialchars($book['book_title']); ?>"
                    class="item-image">
            </div>

            <div class="product-info-box">
                <h2 class="item-name">
                    <?php echo htmlspecialchars($book['book_title']); ?>
                </h2>

                <p class="item-category">
                    Category: <?php echo htmlspecialchars($categoryName); ?>
                </p>

                <h3>Description</h3>
                <p class="item-description">
                    <?php echo htmlspecialchars($book['book_description']); ?>
                </p>

                <p class="item-price">
                    Price: ₱<?php echo number_format($book['book_price'], 2); ?>
                </p>

                <div class="interaction-area">
                    <button class="save-button <?php echo $is_saved ? 'unsave' : 'save'; ?>"
                        data-book="<?php echo $book['book_id']; ?>"
                        data-saved="<?php echo $is_saved ? '1' : '0'; ?>">
                        <?php echo $is_saved ? 'Unsave Book' : 'Save Book'; ?>
                    </button>
                </div>
            </div>

        </section>

        <!-- ⭐ REVIEW SECTION (ready for future DB use) -->

    </main>

    <footer>
        <p>&copy; 2025 eLibrary</p>
    </footer>

    <!-- 🔔 Notification -->
    <div id="notif" style="
    display:none;
    position:fixed;
    top:20px;
    right:20px;
    background:#4CAF50;
    color:white;
    padding:12px 18px;
    border-radius:10px;
    z-index:1000;
    font-size:14px;">
    </div>

    <script>
        function showNotif(message) {
            const notif = document.getElementById("notif");
            notif.textContent = message;
            notif.style.display = "block";

            setTimeout(() => {
                notif.style.display = "none";
            }, 2000);
        }

        // logout notification
        const params = new URLSearchParams(window.location.search);
        if (params.get("logged_out") === "1") {
            showNotif("You have been logged out successfully.");
        }

        const saveBtn = document.querySelector(".save-button");

        saveBtn.addEventListener("click", async () => {
            const book_id = saveBtn.dataset.book;
            const isSaved = saveBtn.dataset.saved === "1";

            const url = isSaved ?
                'bookHandler/remove_book.php' :
                'bookHandler/save_book.php';

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        book_id
                    })
                });

                const data = await response.json();

                if (data.success) {
                    if (isSaved) {
                        saveBtn.textContent = "Save Book";
                        saveBtn.dataset.saved = "0";
                        saveBtn.classList.remove("unsave");
                        saveBtn.classList.add("save");
                        showNotif("Book removed from saved.");
                    } else {
                        saveBtn.textContent = "Unsave Book";
                        saveBtn.dataset.saved = "1";
                        saveBtn.classList.remove("save");
                        saveBtn.classList.add("unsave");
                        showNotif("Book saved!");
                    }
                    setTimeout(() => {
                        window.location.href = "saved_books.php";
                    }, 3000);
                } else {
                    showNotif("Action failed.");
                }
            } catch {
                showNotif("Server error.");
            }
        });
    </script>

</body>

</html>