<?php
// login.php
session_start();
require 'db.php';

// If the user is already logged in, redirect them to the dashboard.
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    if (empty($username) || empty($password)) {
        $error = "Please fill all fields.";
    } else {
        // Fetch user data from DB
        $stmt = $pdo->prepare("SELECT id, password_hash, enc_salt FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password_hash'])) {
            // Derive an encryption key using PBKDF2 (using 100,000 iterations and SHA256)
            $encryption_key = hash_pbkdf2("sha256", $password, hex2bin($user['enc_salt']), 100000, 32, true);
            
            // Store user id and encryption key in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['encryption_key'] = $encryption_key;
            
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid credentials.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Password Manager</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <h1>Password Manager</h1>
            <nav class="nav">
                <a href="index.php" class="btn">Home</a>
                <a href="login.php" class="btn active">Login</a>
                <a href="register.php" class="btn">Register</a>
            </nav>
        </div>
    </header>
    
    <main class="landing-content">
        <div class="container">
            <h2>Login</h2>
            <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
            <form method="post" action="login.php">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Master Password" required>
                <button type="submit">Login</button>
            </form>
            <br>
            <p>Not registered yet? <a href="register.php">Register here</a>.</p>
        </div>
    </main>
    
    <footer class="footer">
        <p>&copy; <?php echo date("Y"); ?> Password Manager. All rights reserved.</p>
    </footer>
</body>
</html>
