<?php
session_start();
include "includes/db.php";

$showUsernamePopup = false;
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $f_name   = trim($_POST["f_name"]);
    $l_name   = trim($_POST["l_name"]);
    $username = trim($_POST["username"]);
    $email    = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (empty($f_name) || empty($l_name) || empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } else {

        $checkSql = "SELECT user_id FROM users WHERE username = ?";
        $checkStmt = mysqli_prepare($conn, $checkSql);
        mysqli_stmt_bind_param($checkStmt, "s", $username);
        mysqli_stmt_execute($checkStmt);
        mysqli_stmt_store_result($checkStmt);

        if (mysqli_stmt_num_rows($checkStmt) > 0) {
            $showUsernamePopup = true;
        } else {

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (f_name, l_name, username, email, password)
                    VALUES (?, ?, ?, ?, ?)";

            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssss",
                $f_name, $l_name, $username, $email, $hashed_password
            );

            if (mysqli_stmt_execute($stmt)) {
                $success = "Account created successfully! You may now login.";
            } else {
                $error = "Something went wrong. Please try again.";
            }

            mysqli_stmt_close($stmt);
        }

        mysqli_stmt_close($checkStmt);
    }
}
?>

<!DOCTYPE html>
    <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Sign Up | Fantastic Food</title>
            <link rel="icon" href="images/fantasticfood.ico">
            <link rel="stylesheet" href="assets/sign_up.css">

            <style>
                @font-face {
                    font-family: 'Londrina Solid';
                    src: url('LondrinaSolid-Regular.woff2') format('woff2');
                    font-weight: normal;
                    font-style: normal;
                }

                .signup-right h1 {
                    font-family: "Londrina Solid", sans-serif;
                } 
                /* LEFT SIDE */
                .signup-left{
                    background:
                        linear-gradient(to bottom, rgba(0,0,0,.55), rgba(0,0,0,.25)),
                        url("images/lumpia.jpg") center/cover no-repeat;
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

                <div>
                	<a href="login.php" class="btn-nav active">Login / Sign Up</a>
                </div>

            </nav>
        </header>

    <main>

        <div class="signup-wrapper">

        <div class="signup-left">
            <h2>Join Fantastic Food</h2>
            <p>Create an account and share recipes with the community.</p>
        </div>

        <div class="signup-right">

            <h1>Sign Up</h1>
            <p class="subtitle">It only takes a minute ✨</p>

            <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST">

            <div class="name-row">
            <input type="text" name="f_name" placeholder="First Name" required>
            <input type="text" name="l_name" placeholder="Last Name" required>
            </div>

            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>

            <button type="submit" class="signup-btn">Create Account</button>

            </form>

            <p class="login-redirect">
            Already have an account? <a href="login.php">Login</a>
            </p>

            </div>
        </div>

    </main>

    <footer>
    	<p>WEBPROG & DATAMA2. All Rights Reserved. Made by Fantastic Four</p>
    </footer>

    </body>
    </html>
