<?php
    session_start();
    include "includes/db.php";

    /* =========================
       ADMIN GUARD
    ========================= */
    if (!isset($_SESSION["user_id"]) || strtolower($_SESSION["role"]) !== "admin") {
        header("Location: index.php");
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
   COUNT FEATURED RECIPES
========================= */
$featuredCountResult = mysqli_query($conn, "
    SELECT COUNT(*) as total 
    FROM recipes 
    WHERE is_featured = 1 AND status='Approved'
");
$featuredData = mysqli_fetch_assoc($featuredCountResult);
$featuredCount = $featuredData['total'];

/* =========================
   COUNT PENDING CONCERNS (for badge)
========================= */
$pendingConcerns = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM concerns WHERE status='Pending'")
)['total'];

/* =========================
   HANDLE ACTIONS
========================= */
if (isset($_GET['action'], $_GET['id'])) {

    $id = (int)$_GET['id'];
    $action = $_GET['action'];

    if ($action === 'approve') {
        mysqli_query($conn, "UPDATE recipes SET status='Approved' WHERE recipe_id=$id");

    } elseif ($action === 'reject') {
        mysqli_query($conn, "UPDATE recipes SET status='Rejected' WHERE recipe_id=$id");

    } elseif ($action === 'feature') {

        if ($featuredCount < 3) {
            mysqli_query($conn, "
                UPDATE recipes 
                SET is_featured=1 
                WHERE recipe_id=$id AND status='Approved'
            ");
        }

    } elseif ($action === 'unfeature') {
        mysqli_query($conn, "UPDATE recipes SET is_featured=0 WHERE recipe_id=$id");

    } elseif ($action === 'delete') {
        mysqli_query($conn, "DELETE FROM recipes WHERE recipe_id=$id");
    }

    header("Location: manage_recipe.php");
    exit;
}

/* =========================
   FETCH RECIPES
========================= */
$recipes = [];

$sql = "
SELECT 
    r.recipe_id,
    r.title,
    r.category,
    r.status,
    r.is_featured,
    u.username
FROM recipes r
JOIN users u ON r.user_id = u.user_id
ORDER BY r.created_at DESC
";

$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    $recipes[] = $row;
}
?>
<!DOCTYPE html>
    <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Manage Recipes | Fantastic Food</title>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="icon" href="images/fantasticfood.ico">
            <link rel="stylesheet" href="assets/manage_recipe.css">
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
                <div class="logo">
                    <a href="admin_dashboard.php">
                    	<img src="images/navigation_logo.png" alt="Logo">
                    </a>
                </div>

                <ul class="nav-links">
                    <li><a href="admin_dashboard.php">Admin Dashboard</a></li>
                    <li><a href="manage_recipe.php" class="active">Manage Recipes</a></li>
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
                    <a href="manage_recipe.php?action=logout" class="logout-btn">Logout</a>
                </div>
            </nav>
        </header>

    <div class="container">

        <h1>Recipes</h1>

            <div class="note">
                Featured recipes are limited to 3. 
                (Current: <?= $featuredCount ?> / 3)
            </div>

            <table>
                <tr>
                    <th>Recipe Title</th>
                    <th>Category</th>
                    <th>Submitted By</th>
                    <th>Status</th>
                    <th>Featured</th>
                    <th>Actions</th>
                </tr>

                <?php foreach ($recipes as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['title']) ?></td>
                        <td><?= htmlspecialchars($r['category']) ?></td>
                        <td><?= htmlspecialchars($r['username']) ?></td>

                        <td class="status-<?= strtolower($r['status']) ?>">
                        <?= htmlspecialchars($r['status']) ?>
                        </td>

                        <td><?= $r['is_featured'] ? '⭐ Yes' : '—' ?></td>

                        <td class="actions">

                        <a href="edit_recipe.php?id=<?= $r['recipe_id'] ?>" class="view">View</a>

                        <?php if ($r['status'] === 'Pending'): ?>
                        <a href="?action=approve&id=<?= $r['recipe_id'] ?>" class="approve">Approve</a>
                        <a href="?action=reject&id=<?= $r['recipe_id'] ?>" class="reject">Reject</a>
                        <?php endif; ?>

                        <?php if ($r['status'] === 'Approved'): ?>

                        <?php if (!$r['is_featured']): ?>

                        <?php if ($featuredCount < 3): ?>
                        <a href="?action=feature&id=<?= $r['recipe_id'] ?>" class="feature">Feature</a>
                        <?php else: ?>
                        <span class="feature disabled">Feature</span>
                        <?php endif; ?>

                        <?php else: ?>
                        <a href="?action=unfeature&id=<?= $r['recipe_id'] ?>" class="unfeature">Unfeature</a>
                        <?php endif; ?>

                        <?php endif; ?>

                        <a href="?action=delete&id=<?= $r['recipe_id'] ?>"
                        class="delete"
                        onclick="return confirm('Delete this recipe?')">
                        Delete
                        </a>

                        </td>
                    </tr>
                <?php endforeach; ?>

            </table>
        </div>

    </body>
</html>
