<?php
session_start();
include "includes/db.php";

/* =========================
   GET RECIPE ID
========================= */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: recipes.php");
    exit;
}
$recipe_id = (int)$_GET['id'];

/* =========================
   HANDLE COMMENT SUBMIT
========================= */
if (isset($_POST['add_comment']) && isset($_SESSION['user_id'])) {
    $comment = trim($_POST['comment']);
    if ($comment !== '') {
        $uid = $_SESSION['user_id'];
        $stmt = $conn->prepare(
            "INSERT INTO recipe_comments (recipe_id, user_id, comment)
             VALUES (?, ?, ?)"
        );
        $stmt->bind_param("iis", $recipe_id, $uid, $comment);
        $stmt->execute();
    }
    header("Location: view_recipe.php?id=$recipe_id");
    exit;
}

/* =========================
   HANDLE RATING
========================= */
if (isset($_POST['rating']) && isset($_SESSION['user_id'])) {
    $rating = (int)$_POST['rating'];
    if ($rating >= 1 && $rating <= 5) {
        $uid = $_SESSION['user_id'];

        $stmt = $conn->prepare("
            INSERT INTO recipe_ratings (recipe_id, user_id, rating)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE rating = VALUES(rating)
        ");
        $stmt->bind_param("iii", $recipe_id, $uid, $rating);
        $stmt->execute();
    }
    exit;
}

/* =========================
   FETCH RECIPE
========================= */
$sql = "
SELECT r.*, u.username
FROM recipes r
JOIN users u ON r.user_id = u.user_id
WHERE r.recipe_id = $recipe_id
AND r.status='Approved'
LIMIT 1";
$res = mysqli_query($conn, $sql);

if (!$res || mysqli_num_rows($res) === 0) {
    echo "<h2 style='text-align:center;margin-top:3rem;'>Recipe not found.</h2>";
    exit;
}
$recipe = mysqli_fetch_assoc($res);

/* =========================
   FETCH AVERAGE RATING
========================= */
$ratingData = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT 
        IFNULL(ROUND(AVG(rating),1),0) AS avg_rating,
        COUNT(*) AS total
    FROM recipe_ratings
    WHERE recipe_id = $recipe_id
"));

$avgRating = (float)$ratingData['avg_rating'];
$totalRatings = (int)$ratingData['total'];

/* =========================
   FETCH USER RATING
========================= */
$userRating = 0;

if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $userRateRes = mysqli_query($conn, "
        SELECT rating 
        FROM recipe_ratings
        WHERE recipe_id = $recipe_id
        AND user_id = $uid
        LIMIT 1
    ");

    if ($userRateRes && mysqli_num_rows($userRateRes) > 0) {
        $userRating = (int)mysqli_fetch_assoc($userRateRes)['rating'];
    }
}

/* =========================
   FETCH COMMENTS
========================= */
$comments = mysqli_query($conn, "
    SELECT c.comment, c.created_at, u.username
    FROM recipe_comments c
    JOIN users u ON c.user_id = u.user_id
    WHERE c.recipe_id = $recipe_id
    ORDER BY c.created_at DESC
");
?>

<!DOCTYPE html>
    <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?= htmlspecialchars($recipe['title']) ?> | Fantastic Food</title>
            <link rel="icon" href="images/fantasticfood.ico">
            <link rel="stylesheet" href="assets/view_recipe.css">
        <style>
            @font-face {
                font-family: 'Londrina Solid';
                src: url('LondrinaSolid-Regular.woff2') format('woff2');
                font-weight: normal;
                font-style: normal;
            }

            h1, h3 {
                font-family: "Londrina Solid", sans-serif;
            }
        </style>
        </head>

    <body>

        <header>
            <nav>
                <a href="index.php" class="logo">
                	<img src="images/navigation_logo.png">
                </a>

                <ul class="nav-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="recipes.php" class="active">Recipes</a></li>
                    <li><a href="website.php">About Fantastic Food</a></li>
                </ul>

                <?php if(isset($_SESSION["user_id"])): ?>
                <button class="menu-button">
                Hello, <?= htmlspecialchars($_SESSION["username"]); ?> 👋
                </button>
                <?php else: ?>
                <a href="login.php" class="btn-nav">Login / Sign Up</a>
                <?php endif; ?>

            </nav>
        </header>

    <div class="container">

    <div class="top-nav">
    <a href="recipes.php" class="back-btn">← Back to Recipes</a>
    </div>

    <div class="image-box"
    style="background-image:url('images/<?= htmlspecialchars($recipe['image']) ?>')"></div>

    <div class="content">

    <h1><?= htmlspecialchars($recipe['title']) ?></h1>
    <p>By <strong><?= htmlspecialchars($recipe['username']) ?></strong></p>

    <div class="section">
    <h3>Rating</h3>

    <div class="avg-stars">
    <?php
    $fullStars = floor($avgRating);
    $halfStar = ($avgRating - $fullStars) >= 0.5 ? 1 : 0;
    $emptyStars = 5 - ($fullStars + $halfStar);

    for ($i=0;$i<$fullStars;$i++) echo '<span class="active">★</span>';
    if ($halfStar) echo '<span class="half">★</span>';
    for ($i=0;$i<$emptyStars;$i++) echo '<span>★</span>';
    ?>
    </div>

    <div class="rating-meta">
    <?= $avgRating > 0 ? number_format($avgRating,1).'/5' : 'No ratings' ?>
    • <?= $totalRatings ?> ratings
    </div>

    <?php if(isset($_SESSION['user_id'])): ?>
    <div style="margin-top:1rem;font-weight:bold;">Rate here:</div>

    <div class="rate-stars" id="rateStars">
    <?php for($i=1;$i<=5;$i++): ?>
    <span data-value="<?= $i ?>" class="<?= $i <= $userRating ? 'active':'' ?>">★</span>
    <?php endfor; ?>
    </div>
    <?php endif; ?>

    </div>

    <div class="section">
    <h3>Ingredients</h3>
    <p><?= nl2br(htmlspecialchars($recipe['ingredients'])) ?></p>
    </div>

    <div class="section">
    <h3>Instructions</h3>
    <p><?= nl2br(htmlspecialchars($recipe['instructions'])) ?></p>
    </div>

    <div class="section">
    <h3>Comments</h3>

    <?php if(isset($_SESSION['user_id'])): ?>
    <form method="post">
    <textarea name="comment" placeholder="Write a comment..." required></textarea>
    <button name="add_comment">Post Comment</button>
    </form>
    <?php else: ?>
    <p><em>Login to comment.</em></p>
    <?php endif; ?>

    <?php while($c = mysqli_fetch_assoc($comments)): ?>
    <div class="comment">
    <strong><?= htmlspecialchars($c['username']) ?></strong><br>
    <?= nl2br(htmlspecialchars($c['comment'])) ?><br>
    <small><?= $c['created_at'] ?></small>
    </div>
    <?php endwhile; ?>

    </div>

    </div>
    </div>

    <script>
    <?php if(isset($_SESSION['user_id'])): ?>
    document.querySelectorAll('#rateStars span').forEach(star=>{
    star.addEventListener('click',()=>{
    let rating = star.dataset.value;
    fetch('view_recipe.php?id=<?= $recipe_id ?>',{
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:'rating='+rating
    }).then(()=>location.reload());
    });
    });
    <?php endif; ?>
    </script>

    </body>
</html>
