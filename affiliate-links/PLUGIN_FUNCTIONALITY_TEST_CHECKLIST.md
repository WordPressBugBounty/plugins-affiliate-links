# Affiliate Links Plugin - Functionality Test Checklist

This checklist ensures all plugin features work correctly after the text domain loading fix.

## Pre-Test Setup
- [x] WordPress debugging enabled
- [x] Debug log cleared
- [ ] Browser console open (F12) to check for JavaScript errors

## 1. Core Plugin Functionality Tests

### 1.1 Plugin Activation/Deactivation
- [ ] **Deactivate** the plugin from Plugins page
  - No errors should appear
  - Check debug.log for PHP errors
- [ ] **Reactivate** the plugin
  - Plugin should activate without errors
  - Check debug.log for PHP errors

### 1.2 Creating Affiliate Links
- [ ] Go to **Affiliate Links → Add New**
- [ ] Create a new affiliate link with:
  - Title: "Test Link"
  - Target URL: "https://example.com/test"
  - Description: "Test description"
- [ ] **Save** the link
  - Link should save without errors
  - All fields should retain their values

### 1.3 Settings Page
- [ ] Go to **Affiliate Links → Settings**
- [ ] Check **General** tab:
  - [ ] All fields display correctly
  - [ ] Change "Affiliate Link Base" from "go" to "out"
  - [ ] Toggle "Show Category in Link URL"
  - [ ] Save settings - should work without errors
- [ ] Check **Defaults** tab:
  - [ ] Toggle "Nofollow Affiliate Links"
  - [ ] Change "Redirect Type" between 301/302/307
  - [ ] Save settings - should work without errors

### 1.4 Link Redirection Test
- [ ] Visit the test link created earlier:
  ```
  http://localhost/wordpress/go/test-link/
  ```
  (or `/out/test-link/` if you changed the base)
- [ ] Should redirect to the target URL
- [ ] Check that redirect type matches settings (301/302/307)

### 1.5 Link Categories
- [ ] Go to **Affiliate Links → Categories**
- [ ] Create a new category:
  - Name: "Test Category"
  - Slug: "test-category"
- [ ] Save without errors
- [ ] Assign the test link to this category
- [ ] If "Show Category in Link URL" is enabled, test:
  ```
  http://localhost/wordpress/go/test-category/test-link/
  ```

## 2. Pro Features Tests (if Pro is active)

### 2.1 Pro Settings
- [ ] Check if Pro settings appear in **Settings** page
- [ ] Test "Parameters Whitelist" field:
  - Add: `utm_source,utm_campaign`
  - Save settings
  - No errors should occur

### 2.2 Custom Target URL Rules
- [ ] Edit an affiliate link
- [ ] Check if "Custom Target URL" section appears
- [ ] Try adding a custom rule (if available)

### 2.3 Link Statistics
- [ ] Check if **Reports** menu item exists
- [ ] Access reports page - should load without errors
- [ ] Check for JavaScript errors in console

### 2.4 Widgets
- [ ] Go to **Appearance → Widgets**
- [ ] Check if these widgets are available:
  - Recent Affiliate Links
  - Popular Affiliate Links
- [ ] Add widgets to sidebar
- [ ] View frontend - widgets should display

## 3. Frontend Tests

### 3.1 Shortcode Test
- [ ] Create a new page/post
- [ ] Add shortcode: `[affiliate_link id="X"]` (replace X with your test link ID)
- [ ] Publish and view - link should display correctly

### 3.2 Link Replacer (Pro)
- [ ] If Pro: Check if automatic link replacement works in content

## 4. Error Checking

### 4.1 PHP Errors
```bash
# Check debug log for any PHP errors
tail -100 /mnt/c/xampp/htdocs/wordpress/wp-content/debug.log | grep -i "error\|warning" | grep -v "rank-math"
```

### 4.2 JavaScript Console
- [ ] Open browser console (F12)
- [ ] Navigate through all admin pages
- [ ] Check for any JavaScript errors

### 4.3 Database Queries
- [ ] Install Query Monitor plugin (optional)
- [ ] Check for any database errors

## 5. Performance Check

### 5.1 Page Load Times
- [ ] Settings page loads quickly
- [ ] Add/Edit link pages load quickly
- [ ] No timeout errors

### 5.2 Memory Usage
- [ ] Check debug.log for memory limit errors
- [ ] Pages should load without hitting memory limits

## Test Results Summary

### ✅ Working Features:
- [ ] Plugin activation/deactivation
- [ ] Creating and editing links
- [ ] Settings save correctly
- [ ] Link redirects work
- [ ] Categories function properly
- [ ] No PHP errors in debug.log
- [ ] No JavaScript errors in console

### ❌ Issues Found:
_List any issues discovered during testing_

### 📝 Notes:
_Any additional observations_

## Quick Test Commands

```bash
# Clear debug log before testing
echo "" > /mnt/c/xampp/htdocs/wordpress/wp-content/debug.log

# Watch debug log during testing
tail -f /mnt/c/xampp/htdocs/wordpress/wp-content/debug.log

# Check for affiliate-links errors specifically
grep -i "error.*affiliate-links" /mnt/c/xampp/htdocs/wordpress/wp-content/debug.log

# Check if redirects are working (replace with your test URL)
curl -I http://localhost/wordpress/go/test-link/
```

## After Testing

Once all tests pass:
1. Document any issues found
2. Disable debugging in wp-config.php
3. Clear debug.log
4. Remove test links/categories created during testing