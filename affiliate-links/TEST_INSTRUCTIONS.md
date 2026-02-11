# Testing the Text Domain Loading Fix

## Prerequisites
✅ WordPress debugging is now enabled in `wp-config.php`
✅ Test script created at `test-textdomain.php`
✅ Debug log cleared and ready

## Step-by-Step Testing Guide

### 1. Initial Test
Open your browser and navigate to:
```
http://localhost/wordpress/wp-content/plugins/affiliate-links/test-textdomain.php
```

This test script will show you:
- Plugin activation status
- Text domain loading status
- Translation functionality
- Any recent warnings in debug.log

### 2. Clear Browser Cache
Clear your browser cache to ensure you're seeing fresh results:
- Chrome/Edge: Ctrl+Shift+Delete → Clear browsing data
- Or use Incognito/Private mode

### 3. Test Admin Interface
1. Go to WordPress admin: `http://localhost/wordpress/wp-admin/`
2. Navigate to **Affiliate Links → Settings**
3. Check if all text labels appear correctly
4. Look for any error messages on the page

### 4. Check Debug Log
After visiting the admin pages, check for warnings:

**Via Command Line:**
```bash
# View last 50 lines of debug log
tail -50 /mnt/c/xampp/htdocs/wordpress/wp-content/debug.log

# Search specifically for text domain warnings
grep "_load_textdomain_just_in_time" /mnt/c/xampp/htdocs/wordpress/wp-content/debug.log | grep "affiliate-links"
```

**Via Test Script:**
Refresh the test page to see the latest debug log entries.

### 5. What to Look For

#### ✅ **Success Indicators:**
- No `_load_textdomain_just_in_time` warnings for `affiliate-links` domain
- All settings page text displays correctly
- No PHP errors or warnings on admin pages
- Test script shows "Text domain 'affiliate-links' is loaded"

#### ❌ **Problem Indicators:**
- Warning: `_load_textdomain_just_in_time was called incorrectly`
- Missing or untranslated text in admin interface
- PHP errors related to undefined translation functions
- Test script shows text domain is NOT loaded

### 6. Additional Tests

#### Test Pro Features (if available):
1. Check any Pro settings or features
2. Verify Pro-specific translations work

#### Test Different Languages:
1. Change WordPress language in Settings → General
2. Install Dutch language pack (since you have nl_NL files)
3. Verify translations load correctly

### 7. Performance Check
With debugging enabled, check page load times:
1. Settings page should load without delay
2. No timeout errors
3. No memory limit errors

## Interpreting Results

### If Everything Works:
- No warnings in debug.log
- All text displays correctly
- The fix is successful!

### If You Still See Warnings:
1. Note the exact error message
2. Check which file/line is mentioned
3. Run the test script again
4. Share the debug.log output for further analysis

## Disable Debugging (After Testing)
Once testing is complete, disable debugging to avoid performance impact:

Edit `wp-config.php` and change:
```php
define( 'WP_DEBUG', false );
// Remove or comment out the other debug constants
```

## Quick Command Reference
```bash
# Clear debug log
echo "" > /mnt/c/xampp/htdocs/wordpress/wp-content/debug.log

# Watch debug log in real-time
tail -f /mnt/c/xampp/htdocs/wordpress/wp-content/debug.log

# Count text domain warnings
grep -c "_load_textdomain_just_in_time.*affiliate-links" /mnt/c/xampp/htdocs/wordpress/wp-content/debug.log
```