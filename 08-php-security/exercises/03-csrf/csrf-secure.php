<?php
session_start();

// Simulate logged-in user
if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = [
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ];
}

// TODO: Generate CSRF token if not exists


$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // TODO: Validate CSRF token


    if (true) { // TODO: Replace with token validation
        // Update settings
        $_SESSION['user']['name'] = $_POST['name'] ?? '';
        $_SESSION['user']['email'] = $_POST['email'] ?? '';
        $message = 'Settings updated successfully!';

        // TODO: Regenerate CSRF token

    } else {
        $error = 'Invalid CSRF token! Possible attack detected.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSRF Secure</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
        }
        .success-box {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .message {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        input {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            box-sizing: border-box;
        }
        button {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="success-box">
        <strong>SECURE VERSION</strong>
        <p>This version uses CSRF tokens. Try the attack - it will fail!</p>
    </div>

    <?php if ($message): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <h1>User Settings (CSRF Protected)</h1>

    <form method="POST">
        <!-- TODO: Add CSRF token as hidden field -->

        <label>Name:</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($_SESSION['user']['name']); ?>" required>

        <label>Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($_SESSION['user']['email']); ?>" required>

        <button type="submit">Update Settings</button>
    </form>

    <p><a href="csrf-vulnerable.php">View Vulnerable Version</a> | <a href="attacker.php" target="_blank">Try Attack</a></p>
</body>
</html>
