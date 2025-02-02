<?php
// edit.php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['encryption_key'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$encryption_key = $_SESSION['encryption_key'];

// Get entry ID from query string
if (!isset($_GET['id'])) {
    die("No entry specified.");
}

$entry_id = intval($_GET['id']);

// Fetch the entry ensuring it belongs to the user
$stmt = $pdo->prepare("SELECT * FROM entries WHERE id = ? AND user_id = ?");
$stmt->execute([$entry_id, $user_id]);
$entry = $stmt->fetch();

if (!$entry) {
    die("Entry not found or access denied.");
}

// Decrypt existing data
$iv = hex2bin($entry['iv']);
$current_username = openssl_decrypt($entry['username_enc'], 'AES-256-CBC', $encryption_key, 0, $iv);
$current_password = openssl_decrypt($entry['password_enc'], 'AES-256-CBC', $encryption_key, 0, $iv);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'], $_POST['login_username'], $_POST['login_password'])) {
    $title = trim($_POST['title']);
    $login_username = trim($_POST['login_username']);
    $login_password = $_POST['login_password'];

    // For simplicity, we use a new IV on update.
    $new_iv = random_bytes(16);
    $new_iv_hex = bin2hex($new_iv);

    $username_enc = openssl_encrypt($login_username, 'AES-256-CBC', $encryption_key, 0, $new_iv);
    $password_enc = openssl_encrypt($login_password, 'AES-256-CBC', $encryption_key, 0, $new_iv);

    $stmt = $pdo->prepare("UPDATE entries SET title = ?, username_enc = ?, password_enc = ?, iv = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$title, $username_enc, $password_enc, $new_iv_hex, $entry_id, $user_id]);

    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Entry - Password Manager</title>
    <link rel="stylesheet" href="css/edit.css">
</head>

<body>

    <header class="header">
        <div class="header-content">
            <h1>Password Manager</h1>
            <nav class="nav">
                <a href="index.php" class="btn">Home</a>
                <a href="dashboard.php" class="btn">Dashboard</a>
                <a href="logout.php" class="btn">Logout</a>
            </nav>
        </div>
    </header>


    <main class="landing-content">
        <div class="container">
            <h2>Edit Entry</h2>
            <form method="post" action="edit.php?id=<?php echo $entry_id; ?>">
                <input type="text" name="title" placeholder="Title" value="<?php echo htmlspecialchars($entry['title']); ?>" required>
                <input type="text" name="login_username" placeholder="Login Username/ID" value="<?php echo htmlspecialchars($current_username); ?>" required>


                <div class="password-wrapper">
                    <input type="password" id="passwordField" name="login_password" placeholder="Login Password" value="<?php echo htmlspecialchars($current_password); ?>" required>
                    <span class="toggle-icon" id="toggleIcon">👁️</span>
                </div>

                <button type="submit">Save Changes</button>
            </form>
            <br>
            <p><a href="dashboard.php">Back to Dashboard</a></p>
        </div>
    </main>


    <footer class="footer">
        <p>&copy; <?php echo date("Y"); ?> Password Manager. All rights reserved.</p>
    </footer>


    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var pwdField = document.getElementById('passwordField');
            var toggleIcon = document.getElementById('toggleIcon');
            toggleIcon.addEventListener('click', function() {
                if (pwdField.type === "password") {
                    pwdField.type = "text";

                    toggleIcon.textContent = "🙈";
                } else {
                    pwdField.type = "password";
                    toggleIcon.textContent = "👁️";
                }
            });
        });
    </script>
</body>

</html>