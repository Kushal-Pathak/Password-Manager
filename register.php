<?php
session_start();
require 'db.php';

// If the user is already logged in, redirect them to the dashboard.
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    if (empty($username) || empty($password)) {
        $error = "Please fill all fields.";
    } else {
        // Hash the password for login authentication.
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Generate a random salt (16 bytes) for key derivation (hex encoded)
        $enc_salt = bin2hex(random_bytes(16));

        // Insert into database using a prepared statement.
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, enc_salt) VALUES (?, ?, ?)");
        try {
            $stmt->execute([$username, $password_hash, $enc_salt]);
            header("Location: login.php");
            exit;
        } catch (Exception $e) {
            $error = "Username may already exist.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Password Manager</title>
    <link rel="stylesheet" href="css/register.css">
</head>
<body>
    
    <header class="header">
        <div class="header-content">
            <h1>Password Manager</h1>
            <nav class="nav">
                <a href="index.php" class="btn">Home</a>
                <a href="login.php" class="btn">Login</a>
                <a href="register.php" class="btn active">Register</a>
            </nav>
        </div>
    </header>

    <main class="landing-content">
        <div class="container">
            <h2>Register</h2>
            <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
            <form method="post" action="register.php">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Master Password" required>
                <button type="submit">Register</button>
            </form>
            <br>
            <p>Already registered? <a href="login.php">Login here</a>.</p>
        </div>
    </main>

  
    <footer class="footer">
        <p>&copy; <?php echo date("Y"); ?> Password Manager. All rights reserved.</p>
    </footer>
</body>
</html>
