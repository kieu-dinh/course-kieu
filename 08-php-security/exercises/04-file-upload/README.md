# Exercise 8.4 - Secure File Upload

## Objective

Learn to implement secure file uploads with proper validation and security measures.

## Duration

3-4 hours

## Task

Create a secure file upload system that prevents common file upload vulnerabilities.

## Requirements

- [ ] Create file upload form (images only)
- [ ] Validate file type (whitelist: jpg, jpeg, png, gif)
- [ ] Check file size (max 2MB)
- [ ] Validate file extension AND MIME type
- [ ] Rename uploaded files to prevent overwriting
- [ ] Store files outside web root OR in protected directory
- [ ] Prevent PHP file uploads
- [ ] Check for double extensions (.php.jpg)
- [ ] Display uploaded images securely
- [ ] Create TWO versions: vulnerable and secure

## Starter Files

Work in `upload-vulnerable.php`, `upload-secure.php`, `uploads/` folder.

## Common File Upload Vulnerabilities

1. **Unrestricted File Upload**: Attacker uploads .php file
2. **Double Extensions**: file.php.jpg (executed as PHP)
3. **MIME Type Bypass**: Fake image with PHP code
4. **Path Traversal**: ../../../etc/passwd
5. **File Overwriting**: Overwrite existing files

## Security Measures

- [ ] Whitelist allowed extensions
- [ ] Check MIME type with `mime_content_type()`
- [ ] Verify image dimensions with `getimagesize()`
- [ ] Generate random filenames
- [ ] Store uploads in protected directory
- [ ] Set proper file permissions
- [ ] Implement upload rate limiting
- [ ] Scan files with antivirus (optional)

## Expected Behavior

**Vulnerable Version:**
- Accepts any file type
- Uses original filename
- Can be exploited to upload PHP shells

**Secure Version:**
- Only accepts valid images
- Validates extension and MIME type
- Uses random filenames
- Cannot be exploited

## Checklist

- [ ] File type validation (extension + MIME)
- [ ] File size limits enforced
- [ ] Filenames sanitized/randomized
- [ ] Upload directory protected
- [ ] Error handling implemented
- [ ] Both versions created for comparison
- [ ] Directory permissions set correctly (755 for folders, 644 for files)

## Tips

- Use `$_FILES['file']['tmp_name']` for validation
- Check MIME with `mime_content_type($tmpPath)`
- Validate image with `getimagesize($tmpPath)`
- Generate filename: `uniqid() . '_' . bin2hex(random_bytes(8)) . '.' . $ext`
- Move file: `move_uploaded_file($tmp, $destination)`
- Create .htaccess in uploads folder to prevent PHP execution:
  ```apache
  php_flag engine off
  ```
- Store uploads in: `uploads/` (with .htaccess) or outside web root
