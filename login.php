<?php
session_start();
include "includes/db.php";

/* =========================
   LOGOUT HANDLER
========================= */
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: index.php");
    exit;
}

/* =========================
   LOGIN LOGIC
========================= */
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    if ($username === "" || $password === "") {
        $error = "All fields are required.";
    } else {

        $sql = "SELECT user_id, username, password, role 
                FROM users 
                WHERE username = ? OR email = ?
                LIMIT 1";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $username, $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($result)) {

            if (password_verify($password, $user["password"]) || $password === $user["password"]) {

                $_SESSION["user_id"]  = $user["user_id"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["role"]     = $user["role"];

                if ($user["role"] === "admin") {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit;

            } else {
                $error = "Invalid username or password.";
            }

        } else {
            $error = "Invalid username or password.";
        }

        mysqli_stmt_close($stmt);
    }
}

$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
	<html lang="en">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>Login | Fantastic Food</title>
			<link rel="icon" href="images/fantasticfood.ico">
			<link rel="stylesheet" href="assets/login.css">

		<style>
			@font-face {
                font-family: 'Londrina Solid';
                src: url('LondrinaSolid-Regular.woff2') format('woff2');
                font-weight: normal;
                font-style: normal;
            }

			.login-right h1 {
    			font-family: "Londrina Solid", sans-serif;
			}    

            /* LEFT */
            .login-left{
                background:
                    linear-gradient(to bottom, rgba(0,0,0,.55), rgba(0,0,0,.25)),
                    url("./images/lumpia.jpg") center/cover no-repeat;
                color:#fff;
                padding:3rem;
                display:flex;
                flex-direction:column;
                justify-content:flex-end;
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
                <li><a href="index.php">Home</a></li>
                <li><a href="recipes.php">Recipes</a></li>
                <li><a href="website.php">About Fantastic Food</a></li>
            </ul>

            <div class="auth-buttons">
                <?php if(isset($_SESSION["user_id"])): ?>
                <button class="menu-button" onclick="toggleMenu()">
                Hello, <?= htmlspecialchars($_SESSION["username"]); ?> 👋
                </button>
                <div id="menu" class="menu-content">
                <a href="#" onclick="confirmLogout()">Logout</a>
                </div>
                <?php else: ?>
                <a href="login.php" class="btn-nav active">Login / Sign Up</a>
                <?php endif; ?>
            </div>

        </nav>
    </header>

    <main>

        <div class="login-wrapper">

            <div class="login-left">
                <h2>Kumain ka na ba?</h2>
                <p>Log in and discover recipes made with love by the community.</p>
            </div>

            <div class="login-right">
            <	h1>Login</h1>
            <p class="subtitle">Welcome back 👋</p>

            <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

                <form method="POST">
                	<input type="text" name="username" placeholder="Username or Email" required>
                	<input type="password" name="password" placeholder="Password" required>
                	<button type="submit" class="login-btn">Login</button>
                </form>

                <p class="signup-redirect">
                	New here? <a href="sign_up.php">Create an account</a>
                </p>

        	</div>

        </div>

    </main>

    <footer>
    	<p>WEBPROG & DATAMA2. All Rights Reserved. Made by Fantastic Four</p>
    </footer>

    <script>
    function toggleMenu(){
        document.getElementById("menu").classList.toggle("show");
    }
    function confirmLogout(){
        if(confirm("Logout?")){
            window.location.href="login.php?action=logout";
        }
    }
    window.onclick=function(e){
        if(!e.target.matches('.menu-button')){
            document.getElementById("menu")?.classList.remove('show');
        }
    }
    </script>

</body>
</html>
