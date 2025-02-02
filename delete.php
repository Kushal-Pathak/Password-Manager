<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    die("No entry specified.");
}

$entry_id = intval($_GET['id']);

// Delete only if the entry belongs to the user
$stmt = $pdo->prepare("DELETE FROM entries WHERE id = ? AND user_id = ?");
$stmt->execute([$entry_id, $user_id]);

if (!headers_sent()) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Entry - Password Manager</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="container">
        <p>Deleting entry... If you are not redirected automatically, <a href="dashboard.php">click here</a>.</p>
    </div>
</body>
</html>
