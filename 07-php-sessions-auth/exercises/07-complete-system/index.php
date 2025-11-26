<?php
session_start();
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auth System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }
        nav {
            background: #f0f0f0;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 30px;
        }
        nav a {
            margin-right: 15px;
            text-decoration: none;
            color: #007bff;
        }
        .hero {
            text-align: center;
            padding: 50px 0;
        }
        .btn {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            margin: 5px;
        }
    </style>
</head>
<body>
    <nav>
        <?php if (isLoggedIn()): ?>
            <!-- TODO: Show authenticated navigation -->
            <a href="dashboard.php">Dashboard</a>
            <a href="profile.php">Profile</a>
            <?php if (isAdmin()): ?>
                <a href="admin.php">Admin</a>
            <?php endif; ?>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <!-- TODO: Show guest navigation -->
            <a href="index.php">Home</a>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </nav>

    <div class="hero">
        <h1>Welcome to the Auth System</h1>
        <p>A complete authentication system built with PHP</p>

        <?php if (!isLoggedIn()): ?>
            <div style="margin-top: 30px;">
                <a href="register.php" class="btn">Get Started</a>
                <a href="login.php" class="btn">Login</a>
            </div>
        <?php else: ?>
            <p>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>!</p>
            <a href="dashboard.php" class="btn">Go to Dashboard</a>
        <?php endif; ?>
    </div>
</body>
</html>
