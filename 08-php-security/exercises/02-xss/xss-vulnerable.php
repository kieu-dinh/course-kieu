<?php
session_start();

// Initialize comments array in session
if (!isset($_SESSION['comments'])) {
    $_SESSION['comments'] = [];
}

// TODO: Handle form submission (add comment to session)


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XSS Vulnerable Version</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
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
        .comment {
            background: #f0f0f0;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 4px;
        }
        .comment strong {
            color: #007bff;
        }
        input, textarea {
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
        <strong>WARNING: This is the VULNERABLE version!</strong>
        <p>This page demonstrates XSS vulnerabilities. Try injecting:</p>
        <code>&lt;script&gt;alert('XSS')&lt;/script&gt;</code>
    </div>

    <h1>Comments (Vulnerable to XSS)</h1>

    <form method="POST">
        <input type="text" name="name" placeholder="Your name" required>
        <textarea name="comment" placeholder="Your comment" rows="3" required></textarea>
        <button type="submit">Post Comment</button>
    </form>

    <h2>Comments:</h2>
    <?php if (empty($_SESSION['comments'])): ?>
        <p>No comments yet.</p>
    <?php else: ?>
        <?php foreach ($_SESSION['comments'] as $comment): ?>
            <div class="comment">
                <!-- TODO: Display name and comment WITHOUT escaping (VULNERABLE!) -->
                <strong><?php echo $comment['name']; ?></strong>
                <p><?php echo $comment['comment']; ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <p><a href="xss-secure.php">View Secure Version</a> | <a href="?clear=1">Clear Comments</a></p>
</body>
</html>
