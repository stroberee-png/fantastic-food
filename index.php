<?php
	session_start();
	include "includes/db.php";

/* ============================
   LOGOUT HANDLER
============================ */
	if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    	session_destroy();
    	header("Location: index.php");
    	exit;
}

/* ============================
   FETCH FEATURED RECIPES
============================ */
$featuredRecipes = [];

$sql = "SELECT recipe_id, title, category, instructions, image
        FROM recipes
        WHERE status = 'Approved' AND is_featured = 1
        ORDER BY created_at DESC
        LIMIT 3";

$result = mysqli_query($conn, $sql);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $featuredRecipes[] = $row;
    }
}

/* ============================
   CURRENT PAGE FOR ACTIVE NAV
============================ */
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
	<html lang="en">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>Home | Fantastic Food</title>
			<link rel="icon" href="images/fantasticfood.ico">
			<link rel="stylesheet" href="assets/index.css">
		<style>
			@font-face {
    			font-family: 'Londrina Solid';
    			src: url('LondrinaSolid-Regular.woff2') format('woff2');
    			font-weight: normal;
    			font-style: normal;
			}

			h1 {
    			font-family: "Londrina Solid", sans-serif;
			}
		</style>
		</head>

			<body>

				<header>
					<nav>
						<a href="index.php" class="logo">
    						<img src="images/navigation_logo.png" alt="Fantastic Food">
						</a>

						<ul class="nav-links">
    						<li>
     						   <a href="index.php" class="<?= ($currentPage == 'index.php') ? 'active' : ''; ?>">
       						     Home
       							</a>
    						</li>
    						<li>
        						<a href="recipes.php" class="<?= ($currentPage == 'recipes.php') ? 'active' : ''; ?>">
            						Recipes
        						</a>
    						</li>
    						<li>
        						<a href="website.php" class="<?= ($currentPage == 'website.php') ? 'active' : ''; ?>">
            						About Fantastic Food
        						</a>
    						</li>
						</ul>

                    <div class="auth-buttons">
                    	<?php if(isset($_SESSION["user_id"])): ?>

                    		<span style="font-weight:bold; color:#1e5f38;">
                    			Hello, <?= htmlspecialchars($_SESSION["username"]); ?> 👋
                    		</span>

                    		<a href="index.php?action=logout" class="btn-nav">
                   				Logout
                    		</a>

                    	<?php else: ?>
                    		<a href="login.php" class="btn-nav">Login / Sign Up</a>
                    	<?php endif; ?>
                    </div>

				</nav>
			</header>

			<section class="hero">
				<div class="hero-text">
					<h1>Hello, kumain ka na ba?<br>Halika na, kain na!</h1>
					<p>Prepare for mealtime, it's CHOW-BERIN TIME!!!</p>
					<div class="hero-buttons">
						<a href="recipes.php" class="btn btn-green">View Recipes</a>
						<a href="<?= isset($_SESSION['user_id']) ? 'submit_recipe.php' : 'login.php'; ?>" class="btn btn-outline">
							Submit Recipe
						</a>
					</div>
				</div>

				<div class="hero-image">
					<img src="images/mascot.png" alt="Fantastic Food Mascot">
				</div>
			</section>

			<section class="featured-recipes">
				<h2>Binibidang Recipes</h2>
    			<p class="subtitle">Looking for fun and new recipes to try? See our <em>binibidang</em> recipes here!<br>'Di mo malalaman kung hindi mo susubukan.</p>

				<div class="recipe-grid">
                    <?php foreach($featuredRecipes as $recipe): ?>
                    	<div class="recipe-card">
                    		<div class="card-img" style="background-image:url('images/<?= htmlspecialchars($recipe['image']); ?>')"></div>
                    			<h3><?= htmlspecialchars($recipe['title']); ?></h3>
                    			<div class="category"><?= htmlspecialchars($recipe['category']); ?></div>
                    				<p><?= htmlspecialchars(substr($recipe['instructions'],0,90)); ?>...</p>
                    				<a href="view_recipe.php?id=<?= $recipe['recipe_id']; ?>" class="view-btn">View Recipe</a>
                    		</div>
                    <?php endforeach; ?>
               </div>

				<div class="see-more-wrapper">
					<a href="recipes.php" class="btn btn-other">See Other Recipes</a>
				</div>
			</section>

			<footer>
				<p>WEBPROG & DATAMA2. All Rights Reserved. Made by Fantastic Four</p>
			</footer>

</body>
</html>
