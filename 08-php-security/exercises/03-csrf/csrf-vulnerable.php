<?php
session_start();

// Simulate logged-in user
if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = [
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ];
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // TODO: Update user settings WITHOUT CSRF protection (VULNERABLE!)
    $_SESSION['user']['name'] = $_POST['name'] ?? '';
    $_SESSION['user']['email'] = $_POST['email'] ?? '';
    $message = 'Settings updated!';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSRF Vulnerable</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
        }
        .warning {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        input {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            box-sizing: border-box;
        }
        button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="warning">
        <strong>WARNING: This version is VULNERABLE to CSRF!</strong>
        <p>Open <a href="attacker.php" target="_blank">attacker.php</a> in another tab to see the attack.</p>
    </div>

    <?php if ($message): ?>
        <div class="success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <h1>User Settings (Vulnerable)</h1>

    <form method="POST">
        <label>Name:</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($_SESSION['user']['name']); ?>" required>

        <label>Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($_SESSION['user']['email']); ?>" required>

        <button type="submit">Update Settings</button>
    </form>

    <p><a href="csrf-secure.php">View Secure Version</a></p>
</body>
</html>
