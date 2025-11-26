<?php
// TODO: Start session


// TODO: Check if user is logged in, if not redirect to login


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }
        .welcome {
            background: #d4edda;
            color: #155724;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .logout-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .logout-btn:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
    <div class="welcome">
        <!-- TODO: Display welcome message with user's name -->
        <h1>Welcome back, !</h1>
        <p>You are logged in.</p>
        <!-- TODO: Display user email from session -->
        <p>Email: </p>
    </div>

    <a href="logout.php" class="logout-btn">Logout</a>
</body>
</html>
