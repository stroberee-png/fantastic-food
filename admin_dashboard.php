<?php
	session_start();
	include "includes/db.php";

/* =========================
   AUTH CHECK (ADMIN ONLY)
========================= */
	if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    	header("Location: login.php");
    	exit;
}

/* =========================
   LOGOUT
========================= */
	if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    	session_destroy();
    	header("Location: login.php");
    	exit;
}

/* =========================
   DASHBOARD COUNTS
========================= */
$totalRecipes = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM recipes"))['total'];
$approvedRecipes = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS approved FROM recipes WHERE status='Approved'"))['approved'];
$pendingRecipes = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS pending FROM recipes WHERE status='Pending'"))['pending'];
$pendingConcerns = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM concerns WHERE status='Pending'")
)['total'];
?>
<!DOCTYPE html>
	<html lang="en">
		<head>
			<meta charset="UTF-8">
			<title>Admin Dashboard | Fantastic Food</title>
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<link rel="icon" href="images/fantasticfood.ico">
			<link rel="stylesheet" href="assets/admin_dashboard.css">
            
		<style>
   			@font-face {
    			font-family: 'Londrina Solid';
    			src: url('LondrinaSolid-Regular.woff2') format('woff2');
    			font-weight: normal;
    			font-style: normal;
			}

			.left-banner h1 {
    			font-size: 4.3 rem;
    			font-family: "Londrina Solid", sans-serif;
			}    
		</style>
		</head>

		<body>

			<header>
				<nav>
    			<!-- CLICKABLE LOGO -->
    			<div class="logo">
                    <a href="admin_dashboard.php">
            			<img src="images/navigation_logo.png" alt="Fantastic Food Logo">
        			</a>
    			</div>

    			<ul class="nav-links">
        			<li><a href="admin_dashboard.php" class="active">Admin Dashboard</a></li>
        			<li><a href="manage_recipe.php">Manage Recipes</a></li>
        			<li>
            			<a href="view_concerns.php">
                			Concerns
                		<?php if ($pendingConcerns > 0): ?>
                    		<span class="badge"><?= $pendingConcerns ?></span>
               			<?php endif; ?>
            			</a>
        			</li>
    			</ul>

    			<div class="admin-info">
        			👤 Admin
        			<a href="admin_dashboard.php?action=logout" class="logout-btn">Logout</a>
    			</div>
			</nav>
		</header>

		<section class="main-container">
    		<div class="left-banner">
        		<h1 id="greeting">Magandang Umaga 👑</h1>
    		</div> 

		<!-- Link external JS -->
		<script src="greeting.js" defer></script>

    		<div class="dashboard-content">

        		<div class="stats">
            		<div class="stat-box">
                	<h3>Total Recipes</h3>
	                <span><?= $totalRecipes ?></span>
	            </div>
	            <div class="stat-box">
	                <h3>Approved Recipes</h3>
	                <span><?= $approvedRecipes ?></span>
	            </div>
				<div class="stat-box">
 	               <h3>Pending Recipes</h3>
   		           <span><?= $pendingRecipes ?></span>
            	</div>
                <div class="stat-box">
                    <h3>Pending Concerns</h3>
                    <span><?= $pendingConcerns ?></span>
                </div>
            </div>

        <div class="manage-box">
            <div>
                <h2>Manage Recipes</h2>
                <p>Manage and edit current recipes here</p>
            </div>
            <a href="manage_recipe.php" class="btn">Proceed to Recipes</a>
        </div>

        <div class="manage-box">
            <div>
                <h2>User Concerns</h2>
                <p>View and resolve feedback & concerns</p>
            </div>
            <a href="view_concerns.php" class="btn">View Concerns</a>
        </div>

    </div>
</section>

<footer>
    WEBPROG & DATAMA2. All Rights Reserved. Made by Fantastic Four
</footer>

</body>
</html>
