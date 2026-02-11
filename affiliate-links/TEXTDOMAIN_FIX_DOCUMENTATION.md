# Affiliate Links Plugin - Text Domain Loading Fix Documentation

## Problem Overview

Starting with WordPress 6.7, the Affiliate Links plugin (version 3.1.0) was generating the following warning:

```
_load_textdomain_just_in_time was called incorrectly. Translation loading for the affiliate-links domain was triggered too early. This is usually an indicator for some code in the plugin or theme running too early. Translations should be loaded at the init action or later.
```

This warning indicates that the plugin was attempting to use translation functions before WordPress had properly loaded the plugin's text domain.

## Root Cause Analysis

### The Loading Sequence Problem

The issue occurred due to the following execution sequence:

1. **Plugin Initialization** (`affiliate-links.php`):
   ```php
   // Line 30 - Plugin instantiates main class immediately
   $Affiliate_Links = new Affiliate_Links();
   ```

2. **Main Class Constructor** (`class-affiliate-links.php`):
   ```php
   public function __construct() {
       // Lines 36-37 - Immediately loads and instantiates settings class
       require_once AFFILIATE_LINKS_PLUGIN_DIR . 'admin/class-affiliate-links-settings.php';
       new Affiliate_Links_Settings();
       
       // Line 34 - Text domain loaded later via hook
       add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
   }
   ```

3. **Settings Class Constructor** (`class-affiliate-links-settings.php`):
   ```php
   public function __construct() {
       // Line 27 - Calls method that uses translation functions
       self::$fields = self::get_default_fields();
   }
   ```

4. **Translation Functions Called Too Early**:
   ```php
   public static function get_default_fields() {
       return array(
           array(
               'title' => __( 'Affiliate Link Base', 'affiliate-links' ),
               // Multiple __() calls here - text domain not yet loaded!
           ),
       );
   }
   ```

### The Timing Issue

- **Problem**: Translation functions (`__()`, `_e()`, etc.) were being called during class construction
- **WordPress Expectation**: Text domain should be loaded before any translation functions are used
- **Actual Behavior**: Text domain was scheduled to load on `plugins_loaded` hook, but classes were instantiated before this hook fired

## Solution Implementation

### 1. Lazy Translation Loading

Instead of calling translation functions immediately, we store untranslated strings with metadata flags:

**Before (problematic):**
```php
array(
    'title' => __( 'Affiliate Link Base', 'affiliate-links' ),
    'description' => __( 'Enter your base URL', 'affiliate-links' ),
)
```

**After (fixed):**
```php
array(
    'title' => 'Affiliate Link Base',
    'title_i18n' => true,  // Flag indicating this needs translation
    'description' => 'Enter your base URL',
    'description_i18n' => true,
)
```

### 2. Deferred Class Loading

We moved class instantiation to occur after the text domain is loaded:

**Before:**
```php
public function __construct() {
    // Classes loaded immediately
    require_once AFFILIATE_LINKS_PLUGIN_DIR . 'admin/class-affiliate-links-settings.php';
    new Affiliate_Links_Settings();
    
    // Text domain loaded later
    add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
}
```

**After:**
```php
public function __construct() {
    // Text domain loaded first
    add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
    
    // Classes loaded after text domain (priority 20)
    add_action( 'plugins_loaded', array( $this, 'load_admin_classes' ), 20 );
}
```

### 3. Translation Application Method

Added a method to apply translations after the text domain is loaded:

```php
public function apply_field_translations() {
    if ( self::$translations_applied ) {
        return;
    }

    foreach ( self::$fields as &$field ) {
        // Translate title if flagged
        if ( ! empty( $field['title_i18n'] ) && $field['title_i18n'] === true ) {
            $field['title'] = __( $field['title'], 'affiliate-links' );
        }

        // Translate description if flagged
        if ( ! empty( $field['description_i18n'] ) && $field['description_i18n'] === true ) {
            $field['description'] = __( $field['description'], 'affiliate-links' );
        }

        // Handle radio/select options
        if ( ! empty( $field['values'] ) && is_array( $field['values'] ) ) {
            foreach ( $field['values'] as $key => &$value ) {
                if ( is_array( $value ) && ! empty( $value['i18n'] ) ) {
                    $field['values'][$key] = __( $value['label'], 'affiliate-links' );
                }
            }
        }
    }

    self::$translations_applied = true;
}
```

### 4. Dynamic Field Addition Support

For fields added dynamically (like Pro version fields), we check if translations have already been applied:

```php
public static function add_field( $field ) {
    // If translations already applied, translate immediately
    if ( self::$translations_applied ) {
        if ( ! empty( $field['title_i18n'] ) && $field['title_i18n'] === true ) {
            $field['title'] = __( $field['title'], 'affiliate-links' );
        }
        if ( ! empty( $field['description_i18n'] ) && $field['description_i18n'] === true ) {
            $field['description'] = __( $field['description'], 'affiliate-links' );
        }
    }
    array_push( self::$fields, $field );
}
```

## Why This Fix Works

### 1. **Proper Hook Timing**
- Text domain loads at `plugins_loaded` (priority 10)
- Admin classes load at `plugins_loaded` (priority 20)
- Pro files load at `plugins_loaded` (priority 30)
- Translations applied at `init` (priority 5)

### 2. **No Early Translation Calls**
- All translation function calls are deferred until after text domain is loaded
- Initial field definitions use plain strings, not translated strings
- Translation happens only when WordPress is ready

### 3. **Backward Compatibility**
- The fix maintains the same public API
- Field structure remains compatible
- No changes required to theme or other plugin integrations

### 4. **WordPress 6.7+ Compliance**
- Follows WordPress best practices for translation loading
- Eliminates the "just in time" warning
- Compatible with future WordPress versions

## Files Modified

1. **`/admin/class-affiliate-links-settings.php`**
   - Added translation flags to field definitions
   - Added `apply_field_translations()` method
   - Modified `add_field()` to handle dynamic additions
   - Updated field rendering to use pre-translated values

2. **`/includes/class-affiliate-links.php`**
   - Deferred admin class loading to `plugins_loaded` hook
   - Added `load_admin_classes()` method
   - Deferred Pro file loading to ensure proper timing
   - Moved `af_link_init` action to `init` hook

3. **`/pro/class-affiliate-links-pro-settings.php`**
   - Updated field definitions to use translation flags
   - Ensured Pro fields follow same pattern as core fields

## Testing the Fix

To verify the fix is working:

1. Enable WordPress debugging:
   ```php
   define( 'WP_DEBUG', true );
   define( 'WP_DEBUG_LOG', true );
   define( 'WP_DEBUG_DISPLAY', true );
   ```

2. Check that no `_load_textdomain_just_in_time` warnings appear for the affiliate-links domain

3. Verify that all plugin text is still properly translated in the admin interface

4. Test that dynamic field additions (Pro features) still work correctly

## Conclusion

This fix ensures the Affiliate Links plugin follows WordPress translation best practices by:
- Loading the text domain before any translation functions are called
- Using a lazy initialization pattern for translatable content
- Maintaining full backward compatibility
- Complying with WordPress 6.7+ stricter translation loading requirements

The warning should no longer appear, and all plugin functionality remains intact.