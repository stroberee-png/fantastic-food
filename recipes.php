<?php
    session_start();
    include "includes/db.php";

    /* =========================
       SEARCH & FILTER LOGIC
    ========================= */
    $search = $_GET['search'] ?? '';
    $category = $_GET['category'] ?? '';

    $where = "WHERE r.status = 'Approved'";

    if (!empty($search)) {
        $searchSafe = mysqli_real_escape_string($conn, $search);
        $where .= " AND r.title LIKE '%$searchSafe%'";
    }

    if (!empty($category)) {
        $categorySafe = mysqli_real_escape_string($conn, $category);
        $where .= " AND r.category_id = '$categorySafe'";
    }

/* =========================
   FETCH RECIPES WITH CORRECT AVERAGE
========================= */
$sql = "
SELECT 
    r.recipe_id,
    r.title,
    r.instructions,
    r.image,
    r.category_id,
    c.category_name,
    IFNULL(ROUND(AVG(rr.rating),1),0) AS avg_rating,
    COUNT(rr.rating) AS total_ratings
FROM recipes r
JOIN categories c ON r.category_id = c.category_id
LEFT JOIN recipe_ratings rr ON r.recipe_id = rr.recipe_id
$where
GROUP BY r.recipe_id
ORDER BY avg_rating DESC, r.created_at DESC
";

$result = mysqli_query($conn, $sql);

/* =========================
   FETCH CATEGORIES
========================= */
$catQuery = "
SELECT * FROM categories
ORDER BY FIELD(category_name, 'Breakfast', 'Lunch', 'Dinner', 'Dessert')
";
$catResult = mysqli_query($conn, $catQuery);
?>

<!DOCTYPE html>
    <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Recipes | Fantastic Food</title>
            <link rel="icon" href="images/fantasticfood.ico">
            <link rel="stylesheet" href="assets/recipes.css">

        <style>
            @font-face {
                font-family: 'Londrina Solid';
                src: url('LondrinaSolid-Regular.woff2') format('woff2');
                font-weight: normal;
                font-style: normal;
            }

            .banner h1 {
                font-family: "Londrina Solid", sans-serif;
            }
        </style>
        </head>

    <body>

        <header>
            <nav>
                <div class="logo">
                    <a href="index.php">
                    	<img src="images/navigation_logo.png">
                    </a>
                </div>

                <ul class="nav-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="recipes.php" class="active">Recipes</a></li>
                    <li><a href="website.php">About Fantastic Food</a></li>
                </ul>

                <div class="auth-buttons">
                    <?php if(isset($_SESSION["user_id"])): ?>
                    <span style="font-weight:bold;color:#1e5f38;">
                    Hello, <?= htmlspecialchars($_SESSION["username"]) ?> 👋
                    </span>
                    <a href="logout.php" class="btn-nav">Logout</a>
                    <?php else: ?>
                    <a href="login.php" class="btn-nav">Login / Sign Up</a>
                    <?php endif; ?>
                </div>

            </nav>
        </header>

    <section class="banner">
        <h1>Nagkaon ka na, dear?</h1>
    </section>

        <section class="search-filter">
            <form method="GET">
                <input type="text" name="search" placeholder="Search Recipe..."
                value="<?= htmlspecialchars($search) ?>">

                <select name="category" onchange="this.form.submit()">
                <option value="">Category</option>
                <?php while($cat = mysqli_fetch_assoc($catResult)): ?>
                <option value="<?= $cat['category_id'] ?>"
                <?= ($category == $cat['category_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['category_name']) ?>
                </option>
                <?php endwhile; ?>
                </select>
            </form>
        </section>

        <section class="recipe-list">
        <div class="recipe-grid">

        <?php if ($result && mysqli_num_rows($result) > 0): ?>
        <?php while($r = mysqli_fetch_assoc($result)): ?>
        <div class="recipe-card">

        <img src="images/<?= htmlspecialchars($r['image']) ?>">

        <h3><?= htmlspecialchars($r['title']) ?></h3>

        <div class="rating">
        <?php
        $avg = (float)$r['avg_rating'];

        $fullStars = floor($avg);
        $halfStar = ($avg - $fullStars) >= 0.5 ? 1 : 0;
        $emptyStars = 5 - ($fullStars + $halfStar);

        for ($i = 0; $i < $fullStars; $i++) {
            echo '<span class="active">★</span>';
        }

        if ($halfStar) {
            echo '<span class="half">★</span>';
        }

        for ($i = 0; $i < $emptyStars; $i++) {
            echo '<span>★</span>';
        }
        ?>

        <small>
        <?= $avg > 0 ? number_format($avg,1).'/5' : 'No ratings' ?>
        (<?= $r['total_ratings'] ?>)
        </small>
        </div>

        <p><?= htmlspecialchars(substr($r['instructions'],0,90)) ?>...</p>

        <a href="view_recipe.php?id=<?= $r['recipe_id'] ?>" class="btn-view">
        	View Recipe
        </a>

        </div>
        <?php endwhile; ?>
        <?php else: ?>
        <div class="empty-state">
        	<h3>🍽️ No recipes found</h3>
        </div>
        <?php endif; ?>

        </div>
    </section>

    <footer>
    	<p>WEBPROG & DATAMA2. All Rights Reserved. Made by Fantastic Four</p>
    </footer>

    </body>
    </html>
