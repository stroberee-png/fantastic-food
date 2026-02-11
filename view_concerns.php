<?php
session_start();
include "includes/db.php";

/* =========================
   ADMIN PROTECTION
========================= */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
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
   UPDATE STATUS
========================= */
if (isset($_GET['resolve']) && is_numeric($_GET['resolve'])) {
    $cid = (int) $_GET['resolve'];
    mysqli_query($conn, "UPDATE concerns SET status='Resolved' WHERE concern_id=$cid");
    header("Location: view_concerns.php");
    exit;
}

/* =========================
   COUNT PENDING (for badge)
========================= */
$pendingConcerns = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM concerns WHERE status='Pending'")
)['total'];

/* =========================
   FETCH CONCERNS
========================= */
$sql = "
SELECT 
    c.concern_id,
    c.first_name,
    c.last_name,
    c.email,
    c.message,
    c.status,
    c.created_at,
    r.title AS recipe_title
FROM concerns c
JOIN recipes r ON c.recipe_id = r.recipe_id
ORDER BY c.created_at DESC
";

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
    <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>View Concerns | Fantastic Food</title>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="icon" href="images/fantasticfood.ico">
            <link rel="stylesheet" href="assets/view_concerns.css">
        <style>
            @font-face {
            font-family: 'Londrina Solid';
            src: url('LondrinaSolid-Regular.woff2') format('woff2');
            font-weight: normal;
            font-style: normal;
        	}

        .container h1 {
            font-size: 4.3 rem;
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
                    <li><a href="manage_recipe.php">Manage Recipes</a></li>
                    <li>
                        <a href="view_concerns.php" class="active">
                        Concerns
                        <?php if ($pendingConcerns > 0): ?>
                        <span class="badge"><?= $pendingConcerns ?></span>
                        <?php endif; ?>
                        </a>
                    </li>
                </ul>

                <div class="admin-info">
                    👤 Admin
                    <a href="view_concerns.php?action=logout" class="logout-btn">Logout</a>
                </div>
            </nav>
        </header>

    <div class="container">
    	<h1>Submitted Concerns</h1>

        <?php if ($result && mysqli_num_rows($result) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Recipe</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
        <tbody>

        <?php while($c = mysqli_fetch_assoc($result)): ?>
        <tr>
        <td><?= htmlspecialchars($c['first_name'].' '.$c['last_name']) ?></td>
        <td><?= htmlspecialchars($c['email']) ?></td>
        <td><?= htmlspecialchars($c['recipe_title']) ?></td>

        <td>
        <div class="message-preview">
        <?= htmlspecialchars($c['message']) ?>
        </div>
        <br>
        <span class="btn btn-view"
        onclick="openModal(
        '<?= htmlspecialchars(addslashes($c['first_name'].' '.$c['last_name'])) ?>',
        '<?= htmlspecialchars(addslashes($c['email'])) ?>',
        '<?= htmlspecialchars(addslashes($c['recipe_title'])) ?>',
        '<?= htmlspecialchars(addslashes($c['message'])) ?>'
        )">
        View
        </span>
        </td>

    <td>
    <span class="status <?= strtolower($c['status']) ?>">
    <?= $c['status'] ?>
    </span>
    </td>

    <td>
    <?php if ($c['status'] === 'Pending'): ?>
    <a class="btn btn-resolve" href="?resolve=<?= $c['concern_id'] ?>">Resolve</a>
    <?php else: ?>
    —
    <?php endif; ?>
    </td>
    </tr>
    <?php endwhile; ?>

    </tbody>
    </table>
    <?php else: ?>
    <div class="empty">
    <h3>No concerns submitted yet 📭</h3>
    </div>
    <?php endif; ?>
    </div>

    <!-- MODAL -->
    <div class="modal" id="concernModal">
    <div class="modal-content">
    <span class="close" onclick="closeModal()">&times;</span>
    <h2>Concern Details</h2>
    <p><strong>User:</strong> <span id="mUser"></span></p>
    <p><strong>Email:</strong> <span id="mEmail"></span></p>
    <p><strong>Recipe:</strong> <span id="mRecipe"></span></p>
    <p><strong>Message:</strong></p>
    <p id="mMessage"></p>
    </div>
    </div>

    <script>
    function openModal(user,email,recipe,message){
    document.getElementById("mUser").innerText=user;
    document.getElementById("mEmail").innerText=email;
    document.getElementById("mRecipe").innerText=recipe;
    document.getElementById("mMessage").innerText=message;
    document.getElementById("concernModal").style.display="flex";
    }
    function closeModal(){
    document.getElementById("concernModal").style.display="none";
    }
    </script>

    </body>
    </html>
