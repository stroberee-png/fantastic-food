<?php
session_start();
include "includes/db.php";

/* =========================
   LOGOUT HANDLER
========================= */
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: website.php");
    exit;
}

/* =========================
   FETCH RECIPES FOR DROPDOWN
========================= */
$recipes = mysqli_query($conn, "SELECT recipe_id, title FROM recipes WHERE status='Approved'");

/* =========================
   HANDLE CONCERN SUBMISSION
========================= */
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name  = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email      = mysqli_real_escape_string($conn, $_POST['email']);
    $recipe_id  = (int) $_POST['recipe_id'];
    $message    = mysqli_real_escape_string($conn, $_POST['message']);

    $sql = "
        INSERT INTO concerns 
        (first_name, last_name, email, recipe_id, message, status)
        VALUES
        ('$first_name', '$last_name', '$email', $recipe_id, '$message', 'Pending')
    ";

    if (mysqli_query($conn, $sql)) {
        $success = "✅ Your concern has been sent successfully. Thank you!";
    }
}

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>About Us | Fantastic Food</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="images/fantasticfood.ico">
<link rel="stylesheet" href="assets/website.css">
<style>
@font-face {
    font-family: 'Londrina Solid';
    src: url('LondrinaSolid-Regular.woff2') format('woff2');
    font-weight: normal;
    font-style: normal;
}

.about-left h1, .concern h2, .team h2 {
    font-family: "Londrina Solid", sans-serif;
}
</style>
</head>

<body>

<header>
<nav>

<a href="index.php" class="logo">
<img src="images/navigation_logo.png" alt="Fantastic Food Logo">
</a>

<ul class="nav-links">
<li><a href="index.php" class="<?= ($currentPage=='index.php')?'active':'' ?>">Home</a></li>
<li><a href="recipes.php" class="<?= ($currentPage=='recipes.php')?'active':'' ?>">Recipes</a></li>
<li><a href="website.php" class="<?= ($currentPage=='website.php')?'active':'' ?>">About Fantastic Food</a></li>
</ul>

<div class="auth-buttons">
<?php if(isset($_SESSION["user_id"])): ?>
<span style="font-weight:bold;color:#1e5f38;">
Hello, <?= htmlspecialchars($_SESSION["username"]) ?> 👋
</span>
<a href="website.php?action=logout" class="btn-nav">Logout</a>
<?php else: ?>
<a href="login.php" class="btn-nav">Login / Sign Up</a>
<?php endif; ?>
</div>

</nav>
</header>

<!-- ABOUT -->
<section class="about">
<div class="about-left">
<h1>Ano ba ang<br>Fantastic Food?</h1>
<p>
Fantastic Food is a community-driven recipe website where
real people share real food experiences.
</p>
</div>
<div class="about-right">
<p style="text-align: justify">Welcome to our little corner of the internet where recipes come to life! This platform is built for food lovers who enjoy discovering new dishes, sharing their own creations, and exploring meals made by a growing community of home cooks. From quick breakfasts to comforting dinners and sweet desserts, every recipe here is carefully organized so you can easily find your next favorite dish.</p>
<p style="text-align: justify">Whether you’re a beginner experimenting in the kitchen or someone who just wants fresh meal ideas, this website makes cooking more fun and less overwhelming. Registered users can share their own recipes, while our admin team keeps everything organized and high-quality — so you get reliable, inspiring meals every time you visit.</p>
</div>
</section>

<!-- CONCERN -->
<section class="concern">
<div class="concern-container">

<div>
<h2>Do you have any<br>concerns to our recipes?</h2>
<small>Fill up the required forms and send us your feedback.</small>
</div>

<div>
<?php if (!empty($success)): ?>
<div style="background:#fff;color:#1e5f38;padding:1rem;border-radius:15px;margin-bottom:1.2rem;font-weight:bold">
<?= $success ?>
</div>
<?php endif; ?>

<form method="POST">
<input type="text" name="first_name" placeholder="First Name" required>
<input type="text" name="last_name" placeholder="Last Name" required>
<input type="email" name="email" placeholder="Email Address" required>

<select name="recipe_id" required>
<option value="">Choose Recipe</option>
<?php while($rec=mysqli_fetch_assoc($recipes)): ?>
<option value="<?= $rec['recipe_id'] ?>">
<?= htmlspecialchars($rec['title']) ?>
</option>
<?php endwhile; ?>
</select>

<textarea name="message" placeholder="Message / Concern" required></textarea>
<button type="submit">Send Message</button>
</form>
</div>

</div>
</section>

<!-- TEAM -->
<section class="team">
<h2>Fantastic Food's Team</h2>
<p>Meet the people behind Fantastic Food 🍳 We’re a group of passionate students who believe that technology and creativity go perfectly together — especially when food is involved. This website is the result of our collaboration, late-night brainstorming sessions, and shared goal of building something useful, interactive, and fun for fellow food lovers.</p>

<div class="team-grid">
<div class="member"><img src="images/team1.png"><h4>Prince Jess Caraig</h4><small>Backend-Frontend</small></div>
<div class="member"><img src="images/team2.png"><h4>Justine Gabriel Chin</h4><small>Frontend</small></div>
<div class="member"><img src="images/team3.png"><h4>John Darwin Custodio</h4><small>Frontend</small></div>
<div class="member"><img src="images/team4.png"><h4>Riza Marie Hombre</h4><small>Project Manager</small></div>
</div>
</section>

<footer>
<p>WEBPROG & DATAMA2. All Rights Reserved. Made by Fantastic Four</p>
</footer>

</body>
</html>
