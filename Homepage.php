<?php
include __DIR__ . '/db_connect.php';
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: SignIn.php");
    exit();
}

$stmt = $const->prepare("SELECT t1.book_id, t1.book_title, t1.book_author, t1.book_pubdate, t1.book_description, t1.book_price, t1.book_img_path, t2.category_id FROM books AS t1 JOIN book_category AS t2 ON t1.book_id = t2.book_id");
$stmt->execute();
$result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$cart = $const->prepare("SELECT * FROM save_books WHERE user_id = ?");
$cart->bind_param("i", $_SESSION['user_id']);
$cart->execute();
$cart_result = $cart->get_result()->fetch_all(MYSQLI_ASSOC);

$books = [
    'fiction' => [],
    'educational' => [],
    'classic' => []
];

if ($result) {
    foreach ($result as $resBook) {
        switch ((int) ($resBook['category_id'])) {
            case 1:
                $books['fiction'][] = $resBook;
                break;
            case 2:
                $books['educational'][] = $resBook;
                break;
            case 3:
                $books['classic'][] = $resBook;
                break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eLibrary - Homepage</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <header class="main-header">
        <h1>eLibrary</h1>
        <nav class="main-nav" aria-label="Main Navigation">
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
        <h2 class="section-heading">Discover Your Next Read</h2>

        <section id="categories" class="book-categories">

            <div class="content-section">
                <h3 class="category-title">Fiction Thrillers 🔪</h3>
                <div class="book-cards-container" id="fiction-section">

                    <?php foreach ($books['fiction'] as $book): ?>
                        <article class="book-card">
                            <img src="<?php echo htmlspecialchars($book['book_img_path']); ?>"
                                alt="<?php echo htmlspecialchars($book['book_title']); ?>" class="book-image">
                            <h3 class="book-name"><?php echo htmlspecialchars($book['book_title']); ?></h3>
                            <p class="book-description">
                                <?php echo htmlspecialchars($book['book_description']); ?>
                            </p>
                            <a href="book.php?book_id=<?php echo urlencode($book['book_id']); ?>" class="details-button">View Details</a>
                        </article>
                    <?php endforeach; ?>

                </div>
            </div>

            <div class="content-section">
                <h3 class="category-title">Educational Resources 📚</h3>
                <div class="book-cards-container" id="educational-section">
                    <?php foreach ($books['educational'] as $book): ?>
                        <article class="book-card">
                            <img src="<?php echo htmlspecialchars($book['book_img_path']); ?>"
                                alt="<?php echo htmlspecialchars($book['book_title']); ?>" class="book-image">
                            <h3 class="book-name"><?php echo htmlspecialchars($book['book_title']); ?></h3>
                            <p class="book-description">
                                <?php echo htmlspecialchars($book['book_description']); ?>
                            </p>
                            <a href="book.php?book_id=<?php echo urlencode($book['book_id']); ?>" class="details-button">View Details</a>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="content-section">
                <h3 class="category-title">Classic Literature 🏛️</h3>
                <div class="book-cards-container" id="classic-section">
                    <?php foreach ($books['educational'] as $book): ?>
                        <article class="book-card">
                            <img src="<?php echo htmlspecialchars($book['book_img_path']); ?>"
                                alt="<?php echo htmlspecialchars($book['book_title']); ?>" class="book-image">
                            <h3 class="book-name"><?php echo htmlspecialchars($book['book_title']); ?></h3>
                            <p class="book-description">
                                <?php echo htmlspecialchars($book['book_description']); ?>
                            </p>
                            <a href="book.php?book_id=<?php echo urlencode($book['book_id']); ?>" class="details-button">View Details</a>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 eLibrary. All rights reserved.</p>
    </footer>

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

        const params = new URLSearchParams(window.location.search);
        if (params.get("logged_out") === "1") {
            showNotif("You have been logged out successfully.");
        }
    </script>


</body>

</html>