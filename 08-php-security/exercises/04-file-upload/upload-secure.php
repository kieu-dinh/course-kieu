<?php
$message = '';
$error = '';
$uploadedFile = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];

    // TODO: Validate file was uploaded without errors


    // TODO: Check file size (max 2MB)


    // TODO: Get file extension


    // TODO: Whitelist allowed extensions


    // TODO: Validate MIME type


    // TODO: Validate it's a real image using getimagesize()


    // TODO: Generate secure random filename


    // TODO: Create uploads directory if it doesn't exist


    // TODO: Move uploaded file


    // TODO: Set success message

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure File Upload</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        button {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }
        img {
            max-width: 100%;
            height: auto;
            margin-top: 20px;
            border: 2px solid #ddd;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="info">
        <strong>SECURE VERSION</strong>
        <p>Upload validation: Extension + MIME type + Image validation</p>
        <p>Allowed: JPG, JPEG, PNG, GIF (max 2MB)</p>
    </div>

    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($message): ?>
        <div class="success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <h1>Secure Image Upload</h1>

    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="file" accept="image/*" required>
        <br><br>
        <button type="submit">Upload Image</button>
    </form>

    <?php if ($uploadedFile && file_exists($uploadedFile)): ?>
        <h2>Uploaded Image:</h2>
        <img src="<?php echo htmlspecialchars($uploadedFile); ?>" alt="Uploaded image">
    <?php endif; ?>

    <p style="margin-top: 30px;">
        <a href="upload-vulnerable.php">View Vulnerable Version</a>
    </p>
</body>
</html>
