# Registration Debugging Guide

## Common Issues and Solutions

### 1. Browser Extension Interference
The error `content.js:5215 Uncaught (in promise) Object` suggests a browser extension is interfering.

**Solution:**
- Disable browser extensions (especially ad blockers, privacy extensions)
- Try in incognito/private mode
- Try a different browser

### 2. Database Connection Issues

**Check:**
1. Run `test_registration.php` to test database connection
2. Verify database credentials in `app/config.php`
3. Ensure database exists: `if0_40051151_ethco_db`
4. Ensure `users` table exists

**Solution:**
```sql
-- Check if database exists
SHOW DATABASES LIKE 'if0_40051151_ethco_db';

-- Check if users table exists
USE if0_40051151_ethco_db;
SHOW TABLES LIKE 'users';

-- If table doesn't exist, run:
SOURCE database/ethco_schema.sql;
```

### 3. PHP Errors

**Check error logs:**
- `logs/error.log`
- Server error logs
- PHP error log

**Enable error display (temporarily):**
In `app/config.php`:
```php
ini_set('display_errors', 1);
```

### 4. Session Issues

**Check:**
- Session directory is writable
- No output before headers
- Cookies are enabled

**Solution:**
```php
// Check session path
echo session_save_path();
```

### 5. File Permissions

**Check:**
- `logs/` directory is writable (755)
- `uploads/` directory is writable (755)
- PHP can write to these directories

### 6. Missing Dependencies

**Check:**
- PDO extension is enabled
- password_hash() function available
- All required files exist

**Test:**
Run `test_registration.php` to check all dependencies.

## Quick Fix Steps

1. **Run the test script:**
   ```
   Visit: http://yourdomain.com/test_registration.php
   ```

2. **Check database:**
   - Verify database credentials
   - Ensure users table exists
   - Test connection manually

3. **Clear cache:**
   - Clear browser cache
   - Clear PHP session files
   - Restart web server if needed

4. **Check error logs:**
   - Check `logs/error.log`
   - Check server error logs
   - Check browser console

5. **Test with simple form:**
   - Try submitting with minimal fields
   - Check if POST data is received

## Testing Registration Manually

1. **Test database connection:**
   ```php
   <?php
   require_once 'app/config.php';
   $db = getDBConnection();
   echo "Connected!";
   ?>
   ```

2. **Test registration function:**
   ```php
   <?php
   require_once 'app/config.php';
   require_once 'app/functions.php';
   require_once 'app/controllers/AuthController.php';
   
   $auth = new AuthController();
   $result = $auth->registerUser('testuser', 'test@test.com', 'Test123!', 'Test123!');
   print_r($result);
   ?>
   ```

## Contact Support

If issues persist:
1. Check `logs/error.log` for detailed errors
2. Run `test_registration.php` and share results
3. Check server PHP version (should be 7.4+)
4. Verify InfinityFree hosting supports PDO and password_hash()

