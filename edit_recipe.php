<?php
	session_start();
	include "includes/db.php";

/* ==========================
   ADMIN AUTH CHECK
========================== */
	if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    	header("Location: index.php");
    	exit;
}

/* ==========================
   GET RECIPE ID
========================== */
	if (!isset($_GET["id"])) {
    	header("Location: manage_recipe.php");
    	exit;
}

$recipe_id = intval($_GET["id"]);

/* ==========================
   FETCH RECIPE
========================== */
$sql = "SELECT * FROM recipes WHERE recipe_id = $recipe_id";
$result = mysqli_query($conn, $sql);
$recipe = mysqli_fetch_assoc($result);

if (!$recipe) {
    die("Recipe not found.");
}

/* ==========================
   FETCH CATEGORIES FOR DROPDOWN
========================== */
$catQuery = mysqli_query($conn, "
    SELECT * FROM categories
    ORDER BY FIELD(category_name,'Breakfast','Lunch','Dinner','Dessert')
");

/* ==========================
   UPDATE RECIPE
========================== */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = mysqli_real_escape_string($conn, $_POST["title"]);
    $category_id = intval($_POST["category_id"]);
    $ingredients = mysqli_real_escape_string($conn, $_POST["ingredients"]);
    $instructions = mysqli_real_escape_string($conn, $_POST["instructions"]);
    $prep_time = mysqli_real_escape_string($conn, $_POST["prep_time"]);
    $status = $_POST["status"];

    $update = "
        UPDATE recipes SET
        title = '$title',
        category_id = '$category_id',
        ingredients = '$ingredients',
        instructions = '$instructions',
        prep_time = '$prep_time',
        status = '$status'
        WHERE recipe_id = $recipe_id
    ";

    if (mysqli_query($conn, $update)) {
        header("Location: manage_recipe.php");
        exit;
    } else {
        $error = "Update failed.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<title>Review Recipe | Fantastic Food</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
    	<link rel="icon" href="images/fantasticfood.ico">
		<link rel="stylesheet" href="assets/edit_recipe.css">
	</head>
		<body>

			<div class="container">

				<!-- ✅ BACK BUTTON MOVED TO TOP -->
				<a href="manage_recipe.php" class="top-back">← Back to Manage Recipes</a>
				<h1>Review Recipe</h1>

<?php if(isset($error)) echo "<p style='color:red'>$error</p>"; ?>

	<form method="POST">

	<label>Recipe Title</label>
	<input type="text" name="title" value="<?= htmlspecialchars($recipe['title']) ?>" required>

<label>Category</label>
<select name="category_id" required>
<?php while($cat = mysqli_fetch_assoc($catQuery)): ?>
<option value="<?= $cat['category_id'] ?>"
<?= ($recipe['category_id'] == $cat['category_id']) ? 'selected' : '' ?>>
<?= htmlspecialchars($cat['category_name']) ?>
</option>
<?php endwhile; ?>
</select>

	<label>Prep Time</label>
	<input type="text" name="prep_time" value="<?= htmlspecialchars($recipe['prep_time']) ?>">

	<label>Ingredients</label>
	<textarea name="ingredients"><?= htmlspecialchars($recipe['ingredients']) ?></textarea>

	<label>Instructions</label>
	<textarea name="instructions"><?= htmlspecialchars($recipe['instructions']) ?></textarea>

<?php if($recipe['image']): ?>
<div class="image-preview">
	<label>Submitted Image</label><br>
	<img src="images/<?= htmlspecialchars($recipe['image']) ?>">
</div>
<?php endif; ?>

<label>Status</label>
<select name="status">
    <option value="Pending" <?= $recipe['status']=="Pending"?"selected":"" ?>>Pending</option>
    <option value="Approved" <?= $recipe['status']=="Approved"?"selected":"" ?>>Approved</option>
</select>

<div class="actions">
<button type="submit" class="save">Save Changes</button>
</div>

</form>
</div>

</body>
</html>
