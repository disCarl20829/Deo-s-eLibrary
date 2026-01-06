<?php
include __DIR__ . '/db_connect.php';
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: SignIn.php");
    exit();
}

$stmt = $const->prepare("
    SELECT 
        b.*,
        c.category_id
    FROM books b
    JOIN book_category c ON b.book_id = c.book_id ORDER BY b.book_id DESC
");
$stmt->execute();
$result = $stmt->get_result();
$books = $result->fetch_all(MYSQLI_ASSOC);

/* 🏷 Category mapping */
$categories = [
    1 => "Fiction",
    2 => "Educational",
    3 => "Classic"
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Management</title>
    <link rel="stylesheet" href="style.css">
</head>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Management</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .book-table input,
        .book-table textarea,
        .book-table select {
            width: 100%;
            padding: 6px 8px;
            font-size: 13px;
            border: 1px solid #ccc;
            border-radius: 6px;
            background: #f9f9f9;
        }

        .book-table textarea {
            resize: vertical;
            min-height: 50px;
        }

        .book-table input:disabled,
        .book-table textarea:disabled,
        .book-table select:disabled {
            background: transparent;
            border: none;
            color: var(--text-dark);
            padding: 0;
        }

        /* Save / Cancel states */
        .save-btn {
            background: var(--success-color);
            color: white;
        }

        .cancel-btn {
            background: #6c757d;
            color: white;
        }
    </style>
</head>

<body>

    <header class="main-header">
        <h1>eLibrary</h1>
        <nav class="main-nav">
            <ul>
                <li><a href="#">Dashboard</a></li>
                <li><a href="#" class="active">Manage Books</a></li>
                <li><a href="#">Users</a></li>
                <li><a href="#">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>

        <section class="management-section">

            <div class="table-wrapper">
                <table class="book-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cover</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Published</th>
                            <th>Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($books as $bk): ?>
                            <tr data-id="<?= $bk['book_id'] ?>">

                                <td>
                                    <input type="hidden" name="book_id" value="<?= $bk['book_id'] ?>">
                                    <?= $bk['book_id'] ?>
                                </td>

                                <input type="hidden" name="old_book_img_path" value="<?= $bk['book_img_path'] ?>">

                                <td id="img_upload_<?= $bk['book_id'] ?>"> <img src="<?= htmlspecialchars($bk['book_img_path']) ?>" class="table-book-img"> </td>

                                <td>
                                    <input name="book_title" type="text"
                                        value="<?= htmlspecialchars($bk['book_title']) ?>" disabled>
                                </td>

                                <td>
                                    <input name="book_author" type="text"
                                        value="<?= htmlspecialchars($bk['book_author']) ?>" disabled>
                                </td>

                                <td>
                                    <select name="book_category" disabled>
                                        <?php foreach ($categories as $id => $name): ?>
                                            <option value="<?= $id ?>"
                                                <?= $bk['category_id'] == $id ? 'selected' : '' ?>>
                                                <?= $name ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>

                                <td>
                                    <textarea name="book_description" disabled><?= htmlspecialchars($bk['book_description']) ?></textarea>
                                </td>

                                <td>
                                    <input name="book_pubdate" type="text"
                                        value="<?= $bk['book_pubdate'] ?>" disabled>
                                </td>

                                <td>
                                    <input name="book_price" type="number" step="0.01"
                                        value="<?= $bk['book_price'] ?>" disabled>
                                </td>

                                <td class="action-buttons">
                                    <button class="edit-btn">Edit</button>
                                    <button class="delete-btn" style="background-color: red;">Delete</button>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </section>

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

        document.querySelectorAll(".edit-btn").forEach(btn => {
            let createImg = null;

            btn.addEventListener("click", async () => {
                const row = btn.closest("tr");
                const inputs = row.querySelectorAll("input, textarea, select");

                if (btn.textContent === "Edit") {
                    inputs.forEach(i => i.disabled = false);
                    btn.textContent = "Save";
                    btn.classList.add("save-btn");

                    const img_upload = document.querySelector("#img_upload_" + row.dataset.id);

                    createImg = document.createElement("input");
                    createImg.type = "file";
                    createImg.name = "book_img_path";
                    createImg.accept = "image/*";
                    img_upload.appendChild(createImg);

                    const cancelBtn = document.createElement("button");
                    cancelBtn.textContent = "Cancel";
                    cancelBtn.className = "edit-btn cancel-btn";

                    cancelBtn.onclick = () => {
                        inputs.forEach(i => i.disabled = true);
                        btn.textContent = "Edit";
                        btn.classList.remove("save-btn");
                        if (createImg) createImg.remove();
                        cancelBtn.remove();
                    };

                    btn.after(cancelBtn);
                    return;
                }

                const formData = new FormData();
                row.querySelectorAll("input, textarea, select").forEach(el => {
                    if (el.type === "file" && el.files[0]) {
                        formData.append(el.name, el.files[0]);
                    } else if (el.name) {
                        formData.append(el.name, el.value);
                    }
                });

                try {
                    const res = await fetch("bookHandler/edit_book.php", {
                        method: "POST",
                        body: formData
                    });

                    const data = await res.json();

                    if (data.success) {
                        showNotif("Book successfully updated.");
                        inputs.forEach(i => i.disabled = true);
                        btn.textContent = "Edit";
                        btn.classList.remove("save-btn");
                        if (createImg) createImg.remove();
                        row.querySelector(".cancel-btn")?.remove();
                    } else {
                        showNotif(data.message || "Update failed.");
                    }
                } catch (err) {
                    console.error(err);
                    showNotif("Server error.");
                }
            });
        });

        document.querySelectorAll(".delete-btn").forEach(btn => {
            btn.addEventListener("click", async () => {
                if (!confirm("Delete this book?")) return;

                const row = btn.closest("tr");
                const book_id = row.dataset.id;

                try {
                    const res = await fetch("bookHandler/delete_book.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            book_id
                        })
                    });

                    const data = await res.json();

                    if (data.success) {
                        row.remove();
                        showNotif("Book deleted.");
                    } else {
                        showNotif("Delete failed.");
                    }
                } catch {
                    showNotif("Server error.");
                }
            });
        });
    </script>

</body>

</html>