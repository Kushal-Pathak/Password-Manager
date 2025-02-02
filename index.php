<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password Manager - Secure &amp; Simple</title>
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <h1>Password Manager</h1>
            <nav class="nav">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php" class="btn">Dashboard</a>
                    <a href="logout.php" class="btn">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn">Login</a>
                    <a href="register.php" class="btn">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="landing-content">
        <section class="intro">
            <h2>Welcome to Password Manager</h2>
            <p>Securely store, manage, and update all your passwords in one place.</p>
        </section>

        <section class="features">
            <h3>Features</h3>
            <ul>
                <li>Advanced AES-256 encryption for maximum security</li>
                <li>User-friendly interface with simple navigation</li>
                <li>Instant access to your credentials anywhere</li>
                <li>Effortless management of login details</li>
            </ul>
        </section>

        <section class="call-to-action">
            <?php if (!isset($_SESSION['user_id'])): ?>
                <p>Ready to secure your digital life?</p>
                <a href="register.php" class="btn">Join Now</a>
            <?php else: ?>
                <p>Manage your stored credentials from your dashboard.</p>
                <a href="dashboard.php" class="btn">Go to Dashboard</a>
            <?php endif; ?>
        </section>
    </main>

    <footer class="footer">
        <p>&copy; <?php echo date("Y"); ?> Password Manager. All rights reserved.</p>
    </footer>
</body>
</html>
