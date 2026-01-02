<?php 

include __DIR__ . '/db_connect.php';
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: SignIn.php");
    exit();
}

$stmt = $const->prepare("SELECT t1.* FROM shop AS t1 JOIN users AS t2 ON t1.shop_id = t2.user_id WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$shop = $stmt->get_result()->fetch_assoc();

$defImg = 'profile_img/default.jpg' ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eLibrary - Shop Profile</title>
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
                <li><a href="profile.php">Profile</a></li> <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?> <li><a href="signout.php">Logout</a></li> <?php endif; ?>
            </ul>
        </nav>
    </header>
    <main class="profile-page-container">
        <h2>About eLibrary & The Owner</h2>
        <section class="owner-info">
            <div class="owner-details" id="profile-view">
                <h3>Meet the Founder, <?php echo htmlspecialchars($shop['shop_owner']) ?></h3>
                <p> <?php echo htmlspecialchars($shop['shop_history']) ?></p>
                </p>
            </div>
            <div class="owner-details" id="profile-edit" style="display:none;"> <input type="text" id="edit-owner" value="<?php echo htmlspecialchars($shop['shop_owner']); ?>">
                <p> <textarea id="edit-history"><?php echo htmlspecialchars($shop['shop_history']); ?></textarea> </p>
            </div>
            <div class="owner-image-box"> <img src="<?php echo htmlspecialchars($shop['shop_img_path']) || $defImg ?>" alt="<?php echo htmlspecialchars($shop['shop_owner']) ?>-image" id="profileImg" class="owner-image"> <input type="file" id="imgInput" accept="image/*" style="display:none;"> </div>
        </section>
        <section class="shop-background">
            <h3>Our Vision</h3>
            <p id="view-vision"><?php echo htmlspecialchars($shop['shop_vision']) ?></p> <textarea id="edit-vision" style="display:none;"><?php echo htmlspecialchars($shop['shop_vision']); ?></textarea>
            <h3>Our Mission</h3>
            <p id="view-mission"><?php echo htmlspecialchars($shop['shop_mission']) ?></p> <textarea id="edit-mission" style="display:none;"><?php echo htmlspecialchars($shop['shop_mission']); ?></textarea>
            <div class="util"> <?php if ($_SESSION['user_id'] == $_SESSION['user_id']): ?> <button id="changeImgBtn" style="display:none;">Change Image</button> <button id="editProfileBtn">Edit Profile</button> <button id="saveProfileBtn" style="display:none;">Save Profile</button> <?php endif; ?> </div>
        </section>
        <section class="customer-feedback">
            <h2>Customer Feedback</h2>
            <div class="feedback-form"> <textarea id="feedback-message" placeholder="Write your feedback..." required></textarea> <select id="feedback-rating">
                    <option value="5">★★★★★</option>
                    <option value="4">★★★★☆</option>
                    <option value="3">★★★☆☆</option>
                    <option value="2">★★☆☆☆</option>
                    <option value="1">★☆☆☆☆</option>
                </select> <button id="submit-feedback">Submit Feedback</button> </div>
            <h3>What Our Readers Say</h3>
            <div class="testimonial-list" id="testimonial-list">
                <blockquote class="testimonial-card">
                    <p>"eLibrary made finding niche local history books so easy. The design is clean and navigation is fast. Highly recommend!"</p> <cite>— Lat B.</cite>
                </blockquote>
                <blockquote class="testimonial-card">
                    <p>"The 'Save Book' feature is perfect for my reading list. Great service and excellent collection variety."</p> <cite>— Nilo B.</cite>
                </blockquote>
                <blockquote class="testimonial-card">
                    <p>"Reliable platform with fair prices. I appreciate their dedication to supporting Filipino authors."</p> <cite>— Nur Wa Lid P.</cite>
                </blockquote>
                <blockquote class="testimonial-card">
                    <p>"Reliable platform with fair prices. I appreciate their dedication to supporting Filipino authors."</p> <cite>— Nur Wa Lid P.</cite>
                </blockquote>
            </div>
        </section>
    </main>
    <footer>
        <p>&copy; 2025 eLibrary. All rights reserved.</p>
    </footer>
    <div id="notif" style=" display:none; position:fixed; top:20px; right:20px; background:#4CAF50; color:white; padding:12px 18px; border-radius:10px; z-index:1000; font-size:14px;"> </div>
    <div id="confirmModal" class="modal" style="display: none;">
        <div class="modal-box">
            <p id="confirmMessage">Are you sure?</p> <button id="confirmYes">Yes</button> <button id="confirmNo">No</button>
        </div>
    </div>
    <script>
        // Notification popup
        function showNotif(message) {
            const notif = document.getElementById("notif");
            notif.textContent = message;
            notif.style.display = "block";

            setTimeout(() => {
                notif.style.display = "none";
            }, 2000);
        }

        // Show logout notification if needed
        const params = new URLSearchParams(window.location.search);
        if (params.get("logged_out") === "1") {
            showNotif("You have been logged out successfully.");
        }

        // Custom confirm modal using Promise
        function customConfirm(message = "Are you sure?") {
            return new Promise((resolve) => {
                const modal = document.getElementById("confirmModal");
                const msg = document.getElementById("confirmMessage");
                const yesBtn = document.getElementById("confirmYes");
                const noBtn = document.getElementById("confirmNo");

                msg.textContent = message;
                modal.style.display = "flex"; // Corrected

                const cleanup = () => {
                    modal.style.display = "none";
                    yesBtn.onclick = null;
                    noBtn.onclick = null;
                    modal.onclick = null;
                };

                yesBtn.onclick = () => {
                    cleanup();
                    resolve(true);
                };

                noBtn.onclick = () => {
                    cleanup();
                    resolve(false);
                };

                // Optional: close if user clicks outside the box
                modal.onclick = (e) => {
                    if (e.target === modal) {
                        cleanup();
                        resolve(false);
                    }
                };
            });
        }

        // Load feedbacks dynamically
        async function loadFeedbacks() {
            const res = await fetch("profile/fetch_feedbacks.php");
            const feedbacks = await res.json();
            const list = document.getElementById("testimonial-list");
            list.innerHTML = "";

            feedbacks.forEach(f => {
                list.innerHTML += `
            <blockquote class="testimonial-card" data-id="${f.feedback_id}">
                <p>"${f.feedback_message}"</p>
                <cite>— ${f.user_name}</cite>
                <p class="stars">
                    ${"★".repeat(f.feedback_rating)}${"☆".repeat(5 - f.feedback_rating)}
                </p>
                ${f.can_edit ? `
                    <button class="edit-btn">Edit</button>
                    <div class="edit-box" style="display:none;">
                        <textarea>${f.feedback_message}</textarea>
                        <select>
                            ${[5,4,3,2,1].map(r => 
                                `<option value="${r}" ${r == f.feedback_rating ? 'selected' : ''}>${"★".repeat(r)}</option>`
                            ).join("")}
                        </select>
                        <button class="save-btn">Save</button>
                        <button class="del-btn">Delete</button>
                    </div>
                ` : ""}
            </blockquote>
            `;
            });

            setupEditButtons();
        }

        // Setup edit/delete buttons for feedback
        function setupEditButtons() {
            document.querySelectorAll(".edit-btn").forEach(btn => {
                btn.onclick = () => {
                    const card = btn.closest(".testimonial-card");
                    card.querySelector(".edit-box").style.display = "block";
                    btn.style.display = "none";
                };
            });

            document.querySelectorAll(".del-btn").forEach(btn => {
                btn.onclick = async () => {
                    const confirmed = await customConfirm("Remove this feedback?");
                    if (!confirmed) return;

                    const card = btn.closest(".testimonial-card");
                    const feedbackId = card.dataset.id;

                    await fetch("profile/delete_feedback.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            feedback_id: feedbackId
                        })
                    });

                    showNotif("Feedback removed!");
                    loadFeedbacks();
                };
            });

            document.querySelectorAll(".save-btn").forEach(btn => {
                btn.onclick = async () => {
                    const confirmed = await customConfirm("Save changes to this feedback??");
                    if (!confirmed) return;

                    const card = btn.closest(".testimonial-card");
                    const feedbackId = card.dataset.id;
                    const msg = card.querySelector("textarea").value;
                    const rating = card.querySelector("select").value;

                    await fetch("profile/update_feedback.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            feedback_id: feedbackId,
                            message: msg,
                            rating
                        })
                    });

                    showNotif("Feedback updated!");
                    loadFeedbacks();
                };
            });
        }

        // Submit new feedback
        document.getElementById("submit-feedback").onclick = async () => {
            const message = document.getElementById("feedback-message").value.trim();
            const rating = document.getElementById("feedback-rating").value;

            if (!message) {
                showNotif("Please write a feedback.");
                return;
            }

            const res = await fetch("profile/submit_feedback.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    message,
                    rating
                })
            });

            const data = await res.json();

            if (data.success) {
                document.getElementById("feedback-message").value = "";
                loadFeedbacks();
                showNotif("Feedback submitted successfully!");
            }
        };

        // Profile edit/save functionality
        const editBtn = document.getElementById("editProfileBtn");
        const saveBtn = document.getElementById("saveProfileBtn");
        const changeImgBtn = document.getElementById("changeImgBtn");
        const imgInput = document.getElementById("imgInput");
        const profileImg = document.getElementById("profileImg");

        editBtn.onclick = () => {
            document.getElementById("profile-view").style.display = "none";
            document.getElementById("profile-edit").style.display = "block";
            document.getElementById("view-vision").style.display = "none";
            document.getElementById("view-mission").style.display = "none";
            document.getElementById("edit-vision").style.display = "block";
            document.getElementById("edit-mission").style.display = "block";
            changeImgBtn.style.display = "inline-block";
            editBtn.style.display = "none";
            saveBtn.style.display = "inline-block";
        };

        saveBtn.onclick = async () => {
            const confirmed = await customConfirm("Save changes to profile?");
            if (!confirmed) return;

            const formData = new FormData();
            formData.append("owner", document.getElementById("edit-owner").value);
            formData.append("history", document.getElementById("edit-history").value);
            formData.append("vision", document.getElementById("edit-vision").value);
            formData.append("mission", document.getElementById("edit-mission").value);

            if (imgInput.files[0]) {
                formData.append("profile_img", imgInput.files[0]);
            }

            const res = await fetch("profile/update_profile.php", {
                method: "POST",
                body: formData
            });
            const data = await res.json();

            if (!data.success) {
                showNotif("Failed to update profile.");
                return;
            }

            document.querySelector("#profile-view h3").textContent = `Meet the Founder, ${data.owner}`;
            document.querySelector("#profile-view p").textContent = data.history;
            document.getElementById("view-vision").textContent = data.vision;
            document.getElementById("view-mission").textContent = data.mission;

            if (data.img_path) profileImg.src = data.img_path;

            document.getElementById("profile-view").style.display = "block";
            document.getElementById("profile-edit").style.display = "none";
            document.getElementById("view-vision").style.display = "block";
            document.getElementById("view-mission").style.display = "block";
            document.getElementById("edit-vision").style.display = "none";
            document.getElementById("edit-mission").style.display = "none";

            saveBtn.style.display = "none";
            editBtn.style.display = "inline-block";
            changeImgBtn.style.display = "none";

            showNotif("Profile updated successfully!");
        };

        // Change profile image
        changeImgBtn.onclick = () => imgInput.click();

        imgInput.onchange = () => {
            const file = imgInput.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = () => profileImg.src = reader.result;
            reader.readAsDataURL(file);
        };

        // Load feedbacks on page load
        loadFeedbacks();
    </script>

</body>

</html>