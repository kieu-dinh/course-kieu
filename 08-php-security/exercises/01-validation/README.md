# Exercise 8.1 - Input Validation

## Objective

Learn to properly validate and sanitize user input to prevent security vulnerabilities.

## Duration

2-3 hours

## Task

Create a form that demonstrates various input validation techniques using PHP.

## Requirements

- [ ] Create a contact form with: name, email, phone, age, website, message
- [ ] Validate each field with appropriate rules:
  - Name: required, min 2 chars, max 50 chars, letters only
  - Email: required, valid email format
  - Phone: optional, valid phone format (10 digits)
  - Age: required, integer between 18-120
  - Website: optional, valid URL
  - Message: required, min 10 chars, max 500 chars
- [ ] Sanitize all inputs before processing
- [ ] Display specific error messages for each field
- [ ] Preserve form data on validation errors
- [ ] Only process form if all validations pass
- [ ] Display success message with sanitized data

## Starter Files

Work in `contact-form.php` - see starter code there.

## Expected Output

**Validation Errors:**
```
- Name must contain only letters and spaces
- Invalid email format
- Age must be between 18 and 120
- Message must be at least 10 characters
```

**Success:**
```
Form submitted successfully!
Name: John Doe
Email: john@example.com
Phone: 0123456789
Age: 25
Website: https://example.com
Message: This is a test message...
```

## Checklist

- [ ] All fields validated with appropriate rules
- [ ] Inputs sanitized using filter_var or htmlspecialchars
- [ ] Custom validation functions created
- [ ] Error messages are specific and helpful
- [ ] Form data persists on errors
- [ ] Success message shows sanitized data
- [ ] Code prevents XSS and injection attacks

## Tips

- Use `filter_var()` for email and URL validation
- Use `preg_match()` for pattern validation (phone, name)
- Use `trim()` to remove whitespace
- Use `htmlspecialchars()` to prevent XSS
- Store errors in an array: `$errors['field_name']`
- Check `!empty($errors)` before processing
- Use `filter_var($var, FILTER_SANITIZE_STRING)` (deprecated in PHP 8+, use htmlspecialchars instead)
