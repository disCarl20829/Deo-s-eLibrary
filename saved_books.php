<?php
include __DIR__ . '/db_connect.php';
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: SignIn.php");
    exit();
}

$stmt = $const->prepare("SELECT t1.book_id, t1.book_title, t1.book_author, t1.book_price, t1.book_img_path, t2.save_quantity FROM books AS t1 JOIN save_books AS t2 ON t1.book_id = t2.book_id WHERE t2.user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$books = [];
if ($result) {
    foreach ($result as $resBook) {
        $books[] = [
            'id' => $resBook['book_id'],
            'title' => $resBook['book_title'],
            'author' => $resBook['book_author'],
            'price' => $resBook['book_price'],
            'img' => $resBook['book_img_path'],
            'qty' => $resBook['save_quantity'] ?? 1,
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eLibrary - Saved Books</title>
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

    <main class="cart-page-container">

        <h2>Your Saved Books</h2>

        <section class="cart-table-section">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Book Name</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($books as $book) : ?>
                        <tr data-id="<?php echo $book['id']; ?>"
                            data-title="<?php echo htmlspecialchars($book['title']); ?>">
                            <td><img src="<?php echo htmlspecialchars($book['img']); ?>" width="80"></td>
                            <td>
                                <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                            </td>
                            <td>
                                <p class="author">by <?php echo htmlspecialchars($book['author']); ?></p>
                            </td>
                            <td class="price" data-price="<?php echo (int) $book['price']; ?>">
                                ₱<?php echo number_format($book['price'] * $book['qty'], 2); ?></td>
                            <td>
                                <div class="qty-box">
                                    <button class="minus">-</button>
                                    <span class="qty"><?php echo $book['qty']; ?></span>
                                    <button class="plus">+</button>
                                </div>
                            </td>
                            <td><button class="remove-btn" id="remove-btn">X</button></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <section class="cart-summary">
            <div class="calculation-box">
                <h3 class="total-price">Total Price: <span id="total-price">₱0.00</span></h3>
            </div>

            <!-- CHECKOUT BUTTON ADDED -->
            <button id="checkout-btn" class="checkout-button">Proceed to Checkout</button>
        </section>

    </main>

    <footer>
        <p>&copy; 2025 eLibrary. All rights reserved.</p>
    </footer>

    <div id="notif" style="display:none; position:fixed; top:20px; right:20px;
                background:#4B3B2A; color:white; padding:12px 18px;
                border-radius:10px; z-index:1000; font-size:14px;">
    </div>

    <script>
        function showNotif(msg) {
            const n = document.getElementById("notif");
            n.textContent = msg;
            n.style.display = "block";
            setTimeout(() => n.style.display = "none", 2000);
        }

        function updateTotal() {
            let total = 0;
            document.querySelectorAll("tbody tr").forEach(row => {
                const qty = parseInt(row.querySelector(".qty").textContent);
                const price = parseInt(row.querySelector(".price").dataset.price);
                total += qty * price;
            });
            document.getElementById("total-price").textContent =
                `₱${total.toLocaleString()}.00`;
        }

        async function updateQuantity(bookId, qty) {
            await fetch("cart/update_quantity.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    book_id: bookId,
                    qty
                })
            });
            showNotif("Quantity updated!");
        }

        async function removeBook(bookId, row) {
            await fetch("cart/remove_saved.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    book_id: bookId
                })
            });
            row.remove();
            showNotif("Book removed from saved list!");
            updateTotal();
        }

        document.querySelectorAll("tbody tr").forEach(row => {
            const bookId = row.dataset.id;
            const qtySpan = row.querySelector(".qty");
            const priceCell = row.querySelector(".price");

            row.querySelector(".plus").onclick = () => {
                let qty = parseInt(qtySpan.textContent) + 1;
                qtySpan.textContent = qty;
                priceCell.textContent = `₱${(qty * priceCell.dataset.price).toLocaleString()}.00`;
                updateQuantity(bookId, qty);
                updateTotal();
            };

            row.querySelector(".minus").onclick = () => {
                let qty = parseInt(qtySpan.textContent);
                if (qty > 1) {
                    qty--;
                    qtySpan.textContent = qty;
                    priceCell.textContent = `₱${(qty * priceCell.dataset.price).toLocaleString()}.00`;
                    updateQuantity(bookId, qty);
                    updateTotal();
                }
            };

            row.querySelector(".remove-btn").onclick = () => {
                removeBook(bookId, row);
            };
        });

        document.getElementById("checkout-btn").onclick = () => {
            showNotif("Checkout functionality is not implemented yet.");
        };

        updateTotal();
    </script>

</body>

</html>
console.log(subtotal)