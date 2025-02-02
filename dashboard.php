<?php
// dashboard.php
session_start();
require 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['encryption_key'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$encryption_key = $_SESSION['encryption_key'];

// If a new entry is being submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'], $_POST['login_username'], $_POST['login_password'])) {
    $title = trim($_POST['title']);
    $login_username = trim($_POST['login_username']);
    $login_password = $_POST['login_password'];

    // Generate a random IV for encryption (16 bytes)
    $iv = random_bytes(16);
    $iv_hex = bin2hex($iv);

    // Encrypt the login username and password
    $username_enc = openssl_encrypt($login_username, 'AES-256-CBC', $encryption_key, 0, $iv);
    $password_enc = openssl_encrypt($login_password, 'AES-256-CBC', $encryption_key, 0, $iv);

    // Insert into database
    $stmt = $pdo->prepare("INSERT INTO entries (user_id, title, username_enc, password_enc, iv) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $title, $username_enc, $password_enc, $iv_hex]);

    header("Location: dashboard.php");
    exit;
}

// Fetch entries for this user
$stmt = $pdo->prepare("SELECT id, title, username_enc, password_enc, iv FROM entries WHERE user_id = ?");
$stmt->execute([$user_id]);
$entries = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard - Password Manager</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>

<body>
    <header class="header">
        <div class="header-content">
            <h1>Password Manager</h1>
            <nav class="nav">
                <a href="index.php" class="btn">Home</a>
                <a href="dashboard.php" class="btn active">Dashboard</a>
                <a href="logout.php" class="btn">Logout</a>
            </nav>
        </div>
    </header>

    <main class="landing-content">
        <div class="container">
            <h2>Your Password Entries</h2>


            <section class="add-entry">
                <h3>Add New Entry</h3>
                <form method="post" action="dashboard.php">
                    <input type="text" name="title" placeholder="Entry Title (e.g., Gmail)" required>
                    <input type="text" name="login_username" placeholder="Login Username/ID" required>
                    <div class="password-wrapper">
                        <input type="password" name="login_password" placeholder="Login Password" required id="newPassword">
                        <span class="toggle-icon" data-target="newPassword">👁️</span>
                    </div>
                    <button type="submit">Add Entry</button>
                </form>
            </section>


            <section class="entries">
                <h3>Saved Entries</h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Username/ID</th>
                                <th>Password</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($entries as $entry):
                                // Convert stored hex IV back to binary
                                $iv = hex2bin($entry['iv']);
                                // Decrypt the username and password using the session encryption key.
                                $decrypted_username = openssl_decrypt($entry['username_enc'], 'AES-256-CBC', $encryption_key, 0, $iv);
                                $decrypted_password = openssl_decrypt($entry['password_enc'], 'AES-256-CBC', $encryption_key, 0, $iv);
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($entry['title']); ?></td>
                                    <td><?php echo htmlspecialchars($decrypted_username); ?></td>
                                    <td>

                                        <div class="password-wrapper">
                                            <input class="view-pwd" type="password" value="<?php echo htmlspecialchars($decrypted_password); ?>" readonly id="pwd-<?php echo $entry['id']; ?>">
                                            <span class="toggle-icon" data-target="pwd-<?php echo $entry['id']; ?>">👁️</span>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="edit.php?id=<?php echo $entry['id']; ?>">Edit</a> |
                                        <a href="delete.php?id=<?php echo $entry['id']; ?>" onclick="return confirm('Delete this entry?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>


    <footer class="footer">
        <p>&copy; <?php echo date("Y"); ?> Password Manager. All rights reserved.</p>
    </footer>

    <!-- JavaScript to toggle password visibility -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var toggleIcons = document.querySelectorAll('.toggle-icon');
            toggleIcons.forEach(function(icon) {
                icon.addEventListener('click', function() {
                    var targetId = icon.getAttribute('data-target');
                    var pwdField = document.getElementById(targetId);
                    if (pwdField.type === "password") {
                        pwdField.type = "text";
                        icon.textContent = "🙈";
                    } else {
                        pwdField.type = "password";
                        icon.textContent = "👁️";
                    }
                });
            });
        });
    </script>
</body>

</html>