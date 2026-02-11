<?php
// Database configuration
$host = "sql201.infinityfree.com";
$user = "if0_41123645";
$password = "FantasticFood1";
$database = "if0_41123645_recipe_platform_db"; // CHANGE if your DB name is different

// Create connection
$conn = mysqli_connect($host, $user, $password, $database);

// Check connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
