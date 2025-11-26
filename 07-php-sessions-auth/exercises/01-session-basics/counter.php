<?php
// TODO: Start the session


// TODO: Initialize visit counter if it doesn't exist


// TODO: Increment the visit counter


// TODO: Store the session start time if not already set


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Counter</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
        }
        .info {
            background: #f0f0f0;
            padding: 20px;
            border-radius: 8px;
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
    </style>
</head>
<body>
    <h1>Welcome!</h1>

    <div class="info">
        <!-- TODO: Display visit count -->
        <p>This is visit #</p>

        <!-- TODO: Display session start time -->
        <p>Session started: </p>

        <!-- TODO: Display session ID -->
        <p>Session ID: </p>
    </div>

    <form action="reset.php" method="POST">
        <button type="submit">Reset Counter</button>
    </form>
</body>
</html>
