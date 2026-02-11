<?php
session_start();
include "includes/db.php";

/* =========================
   AUTH CHECK
========================= */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

/* =========================
   FETCH CATEGORIES (ORDER FIXED)
========================= */
$cat_query = "
SELECT * FROM categories
ORDER BY FIELD(category_name, 'Breakfast', 'Lunch', 'Dinner', 'Dessert')
";
$cat_result = mysqli_query($conn, $cat_query);

/* =========================
   FORM SUBMISSION LOGIC
========================= */
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $user_id = $_SESSION['user_id'];
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']); 
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $ingredients = mysqli_real_escape_string($conn, $_POST['ingredients']);
    $instructions = mysqli_real_escape_string($conn, $_POST['instructions']);
    $prep_time = trim($_POST['prep_time']);
    $status = "Pending";

    // ✅ VALID PREP TIME FORMAT
    $prep_pattern = "/^(\d+\s*(hour|hours|hr|hrs))?[\s,]*?(\d+\s*(minute|minutes|min|mins))?$/i";

    if (!preg_match($prep_pattern, $prep_time)) {
        $message = "❌ Please enter a valid prep time (e.g. 30 mins, 1 hour, 1 hour 30 minutes).";
    }

    $prep_time = mysqli_real_escape_string($conn, $prep_time);

    $imageName = null;

    if ($message === "" && !empty($_FILES['recipe_image']['name'])) {
        $imageName = time() . "_" . basename($_FILES['recipe_image']['name']);
        $targetPath = "images/" . $imageName;

        if (!move_uploaded_file($_FILES['recipe_image']['tmp_name'], $targetPath)) {
            $message = "❌ Image upload failed.";
        }
    }

    if ($message === "") {
        $sql = "INSERT INTO recipes 
                (user_id, category_id, title, ingredients, instructions, prep_time, image, status)
                VALUES 
                ('$user_id', '$category_id', '$title', '$ingredients', '$instructions', '$prep_time', '$imageName', '$status')";

        if (mysqli_query($conn, $sql)) {
            header("Location: index.php");
            exit;
        } else {
            $message = "❌ Error: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
    <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Submit Recipe | Fantastic Food</title>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="icon" href="images/fantasticfood.ico">
            <link rel="stylesheet" href="assets/submit_recipe.css">

        <style>
            @font-face {
                font-family: 'Londrina Solid';
                src: url('LondrinaSolid-Regular.woff2') format('woff2');
                font-weight: normal;
                font-style: normal;
            }

            .banner-text h1 {
                font-family: "Londrina Solid", sans-serif;
            } 
        </style>
        </head>
    <body>

        <header>
            <nav>
                <div class="logo">
                    <a href="index.php">
                    <img src="images/navigation_logo.png" alt="Fantastic Food Logo">
                    </a>
                </div>
                <ul class="nav-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="recipes.php">Recipes</a></li>
                    <li><a href="website.php">About Fantastic Food</a></li>
                </ul>
                <div class="auth-buttons">
                    <span style="font-weight:bold;color:#1e5f38">
                    Hello, <?= htmlspecialchars($_SESSION["username"]) ?> 👋
                    </span>
                </div>
            </nav>
        </header>

        <form action="submit_recipe.php" method="POST" enctype="multipart/form-data">

            <section class="page-header">
                <div class="banner-content">
                    <div class="banner-text">
                    <h1>Submit a recipe here</h1>
                    <p>Maglagay ka rito ng recipe! Share your masterpiece and make them yearn for that taste!</p>
                	</div>

                    <div class="upload-container">
                        <img id="image-preview">
                        <div class="upload-inner">
                        <p>Click to upload image</p>
                    	</div>
                        <input type="file" name="recipe_image" id="recipe_image" accept="image/*" required>
                    </div>
                </div>
            </section>

            <section class="form-section">

                <?php if($message != ""): ?>
                <div class="error-message"><?= $message ?></div>
                <?php endif; ?>

                <div class="grid-container">

                <div class="form-group">
                <label>Recipe Title *</label>
                <input type="text" name="title" required>
                </div>

                <div class="form-group" style="flex-direction: row; align-items: flex-end; gap: 20px;">
                <div style="flex: 1;">
                <label>Prep Time *</label>
                <input type="text" name="prep_time" placeholder="e.g. 1 hour 30 minutes" required>
                </div>

                <div style="flex: 1;">
                <label>Category *</label>
                <select name="category_id" required>
                <option value="">Select Category</option>
                <?php while($row = mysqli_fetch_assoc($cat_result)): ?>
                <option value="<?= $row['category_id']; ?>">
                <?= htmlspecialchars($row['category_name']); ?>
                </option>
                <?php endwhile; ?>
                </select>
                </div>
                </div>

                <div class="form-group">
                <label>Ingredients *</label>
                <textarea name="ingredients" required></textarea>
                </div>

                <div class="form-group">
                <label>Instructions *</label>
                <textarea name="instructions" required></textarea>
                </div>

                <div class="footer-buttons">
                <button type="button" class="btn-cancel" onclick="window.location.href='index.php'">Cancel</button>
                <button type="submit" class="btn-submit">Submit Recipe</button>
                </div>

            </div>
            </section>
        </form>

    <footer>
    	<p>WEBPROG & DATAMA2. All Rights Reserved. Made by Fantastic Four</p>
    </footer>

    <script>
    document.getElementById("recipe_image").addEventListener("change", function () {
    const file = this.files[0];
    const preview = document.getElementById("image-preview");
    const placeholder = document.querySelector(".upload-inner");

    if (file) {
    const reader = new FileReader();
    reader.onload = function (e) {
    preview.src = e.target.result;
    preview.style.display = "block";
    placeholder.style.display = "none";
    };
    reader.readAsDataURL(file);
    }
    });
    </script>

    </body>
    </html>
