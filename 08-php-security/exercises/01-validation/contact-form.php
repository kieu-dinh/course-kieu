<?php
$errors = [];
$success = false;
$data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // TODO: Get form data
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $age = trim($_POST['age'] ?? '');
    $website = trim($_POST['website'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // TODO: Validate name (required, 2-50 chars, letters and spaces only)


    // TODO: Validate email (required, valid format)


    // TODO: Validate phone (optional, but if provided must be 10 digits)


    // TODO: Validate age (required, integer, 18-120)


    // TODO: Validate website (optional, but if provided must be valid URL)


    // TODO: Validate message (required, 10-500 chars)


    // TODO: If no errors, sanitize and process the data


    if (empty($errors)) {
        $success = true;
        // TODO: Store sanitized data
        $data = [
            'name' => htmlspecialchars($name),
            'email' => htmlspecialchars($email),
            'phone' => htmlspecialchars($phone),
            'age' => (int)$age,
            'website' => htmlspecialchars($website),
            'message' => htmlspecialchars($message)
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Form - Input Validation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
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
        input, textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .error {
            color: #dc3545;
            font-size: 14px;
            margin-top: 5px;
        }
        .errors-box {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .success-box {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
        .required {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <h1>Contact Form</h1>
    <p>All fields marked with <span class="required">*</span> are required.</p>

    <?php if (!empty($errors)): ?>
        <div class="errors-box">
            <strong>Please fix the following errors:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success-box">
            <strong>Form submitted successfully!</strong>
            <ul>
                <li><strong>Name:</strong> <?php echo $data['name']; ?></li>
                <li><strong>Email:</strong> <?php echo $data['email']; ?></li>
                <?php if ($data['phone']): ?>
                    <li><strong>Phone:</strong> <?php echo $data['phone']; ?></li>
                <?php endif; ?>
                <li><strong>Age:</strong> <?php echo $data['age']; ?></li>
                <?php if ($data['website']): ?>
                    <li><strong>Website:</strong> <?php echo $data['website']; ?></li>
                <?php endif; ?>
                <li><strong>Message:</strong> <?php echo $data['message']; ?></li>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label for="name">Name <span class="required">*</span></label>
            <input type="text" id="name" name="name"
                   value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
            <?php if (isset($errors['name'])): ?>
                <div class="error"><?php echo $errors['name']; ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="email">Email <span class="required">*</span></label>
            <input type="email" id="email" name="email"
                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            <?php if (isset($errors['email'])): ?>
                <div class="error"><?php echo $errors['email']; ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="phone">Phone (10 digits)</label>
            <input type="tel" id="phone" name="phone"
                   value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
            <?php if (isset($errors['phone'])): ?>
                <div class="error"><?php echo $errors['phone']; ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="age">Age <span class="required">*</span></label>
            <input type="number" id="age" name="age"
                   value="<?php echo htmlspecialchars($_POST['age'] ?? ''); ?>">
            <?php if (isset($errors['age'])): ?>
                <div class="error"><?php echo $errors['age']; ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="website">Website</label>
            <input type="url" id="website" name="website"
                   value="<?php echo htmlspecialchars($_POST['website'] ?? ''); ?>">
            <?php if (isset($errors['website'])): ?>
                <div class="error"><?php echo $errors['website']; ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="message">Message <span class="required">*</span></label>
            <textarea id="message" name="message" rows="5"><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
            <?php if (isset($errors['message'])): ?>
                <div class="error"><?php echo $errors['message']; ?></div>
            <?php endif; ?>
        </div>

        <button type="submit">Submit</button>
    </form>
</body>
</html>
