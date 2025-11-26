<?php
// TODO: Include auth.php to protect this page


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }
        nav {
            background: #f0f0f0;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        nav a {
            margin-right: 15px;
            text-decoration: none;
            color: #007bff;
        }
    </style>
</head>
<body>
    <nav>
        <a href="profile.php">Profile</a>
        <a href="settings.php">Settings</a>
        <a href="logout.php">Logout</a>
    </nav>

    <h1>Settings</h1>

    <p>This is a protected page. Only logged-in users can see this.</p>

    <!-- TODO: Add some settings options -->
    <form>
        <h3>Account Settings</h3>
        <label>
            <input type="checkbox"> Email notifications
        </label>
        <br>
        <label>
            <input type="checkbox"> Newsletter
        </label>
    </form>
</body>
</html>
