<?php
require_once 'db.php';

$message = '';
$reset_link = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    // TODO: Check if email exists in database


    // TODO: Generate a secure random token


    // TODO: Store token in password_resets table with expiration (1 hour)


    // TODO: Create reset link (for testing, just display it)
    // $reset_link = "http://localhost/reset-password.php?token=" . $token;


    // TODO: In production, send email with reset link
    // mail($email, "Password Reset", "Click here to reset: $reset_link");


    $message = "Password reset link sent! (Check below for testing)";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            background: #007bff;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .reset-link {
            background: #fff3cd;
            padding: 10px;
            border-radius: 4px;
            margin-top: 10px;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <h1>Forgot Password</h1>

    <?php if ($message): ?>
        <div class="success"><?php echo htmlspecialchars($message); ?></div>
        <?php if ($reset_link): ?>
            <div class="reset-link">
                <strong>Testing Link:</strong><br>
                <a href="<?php echo htmlspecialchars($reset_link); ?>">
                    <?php echo htmlspecialchars($reset_link); ?>
                </a>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label for="email">Email Address:</label>
            <input type="email" id="email" name="email" required>
        </div>

        <button type="submit">Send Reset Link</button>
    </form>

    <p style="text-align: center; margin-top: 20px;">
        <a href="login.php">Back to Login</a>
    </p>
</body>
</html>
