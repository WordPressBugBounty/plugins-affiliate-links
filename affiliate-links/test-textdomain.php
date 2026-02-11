<?php
/**
 * Test Script for Affiliate Links Text Domain Loading
 * 
 * IMPORTANT: This is a development/testing file and should be removed before
 * submitting to WordPress.org plugin repository.
 * 
 * This script helps verify that the text domain loading fix is working correctly.
 * Access this file via your WordPress site URL + path to this file.
 * 
 * @internal For development use only
 */

// Load WordPress
require_once( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php' );

// Enable error reporting for this test
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Affiliate Links Text Domain Test</h1>";
echo "<pre>";

// Test 1: Check if plugin is active
echo "=== Test 1: Plugin Status ===\n";
if ( is_plugin_active( 'affiliate-links/affiliate-links.php' ) ) {
    echo "✓ Plugin is active\n";
} else {
    echo "✗ Plugin is NOT active\n";
}

// Test 2: Check if text domain is loaded
echo "\n=== Test 2: Text Domain Loading ===\n";
$loaded_domains = $GLOBALS['l10n'] ?? array();
if ( isset( $loaded_domains['affiliate-links'] ) ) {
    echo "✓ Text domain 'affiliate-links' is loaded\n";
    // Note: get_filename() method may not exist in all WordPress versions
} else {
    echo "✗ Text domain 'affiliate-links' is NOT loaded\n";
}

// Test 3: Test translation function
echo "\n=== Test 3: Translation Function Test ===\n";
$test_string = __( 'Affiliate Link Base', 'affiliate-links' );
echo esc_html( "Translation test: '$test_string'\n" );
if ( $test_string !== 'Affiliate Link Base' ) {
    echo "✓ Translation is working (string was translated)\n";
} else {
    echo "! Translation returned original string (this is normal if no translation exists)\n";
}

// Test 4: Check for _load_textdomain_just_in_time warnings
echo "\n=== Test 4: Checking for Text Domain Warnings ===\n";
echo "Check the debug.log file for any '_load_textdomain_just_in_time' warnings.\n";
echo esc_html( "Debug log location: " . WP_CONTENT_DIR . "/debug.log\n" );

// Test 5: Check settings fields
echo "\n=== Test 5: Settings Fields Translation ===\n";
if ( class_exists( 'Affiliate_Links_Settings' ) ) {
    $fields = Affiliate_Links_Settings::$fields;
    if ( ! empty( $fields ) ) {
        echo "✓ Settings fields are loaded\n";
        echo "Sample field titles:\n";
        foreach ( array_slice( $fields, 0, 3 ) as $field ) {
            echo esc_html( "  - " . $field['title'] . "\n" );
        }
        
        // Check if translations were applied
        $has_translated_fields = false;
        foreach ( $fields as $field ) {
            if ( isset( $field['title'] ) && ! isset( $field['title_i18n'] ) ) {
                $has_translated_fields = true;
                break;
            }
        }
        
        if ( $has_translated_fields ) {
            echo "✓ Field translations have been applied\n";
        } else {
            echo "✗ Field translations may not have been applied yet\n";
        }
    } else {
        echo "✗ No settings fields found\n";
    }
} else {
    echo "✗ Affiliate_Links_Settings class not found\n";
}

// Test 6: Hook timing check
echo "\n=== Test 6: Hook Timing ===\n";
echo esc_html( "Current action: " . current_action() . "\n" );
echo "Did 'plugins_loaded': " . ( did_action( 'plugins_loaded' ) ? 'Yes' : 'No' ) . "\n";
echo "Did 'init': " . ( did_action( 'init' ) ? 'Yes' : 'No' ) . "\n";
echo "Did 'af_link_init': " . ( did_action( 'af_link_init' ) ? 'Yes' : 'No' ) . "\n";

// Test 7: Pro version check
echo "\n=== Test 7: Pro Version ===\n";
if ( class_exists( 'Affiliate_Links_PRO' ) ) {
    echo "✓ Pro class is loaded\n";
    if ( function_exists( 'AFL_PRO' ) ) {
        echo "✓ Pro instance function exists\n";
    }
} else {
    echo "! Pro class not found (this is normal if Pro is not installed)\n";
}

echo "\n=== Test Complete ===\n";
echo "</pre>";

// Clear any debug log entries from before this test
echo "<hr>";
echo "<h2>Recent Debug Log Entries</h2>";
echo "<p>Checking for '_load_textdomain_just_in_time' warnings...</p>";

$debug_log = WP_CONTENT_DIR . '/debug.log';
if ( file_exists( $debug_log ) ) {
    $log_contents = file_get_contents( $debug_log );
    $lines = explode( "\n", $log_contents );
    $recent_lines = array_slice( $lines, -50 ); // Last 50 lines
    
    $found_warnings = false;
    echo "<pre style='background: #f0f0f0; padding: 10px; overflow-x: auto;'>";
    foreach ( $recent_lines as $line ) {
        if ( strpos( $line, '_load_textdomain_just_in_time' ) !== false && strpos( $line, 'affiliate-links' ) !== false ) {
            echo "<span style='color: red; font-weight: bold;'>" . esc_html( $line ) . "</span>\n";
            $found_warnings = true;
        }
    }
    
    if ( ! $found_warnings ) {
        echo "<span style='color: green;'>✓ No '_load_textdomain_just_in_time' warnings found for affiliate-links!</span>\n";
    }
    echo "</pre>";
} else {
    echo "<p>Debug log file not found. Make sure WP_DEBUG_LOG is enabled.</p>";
}

echo "<hr>";
echo "<h2>Testing Instructions</h2>";
echo "<ol>";
echo "<li>Access this test page: <code>http://localhost/wordpress/wp-content/plugins/affiliate-links/test-textdomain.php</code></li>";
echo "<li>Clear the debug.log file first to see only new warnings</li>";
echo "<li>Navigate to the WordPress admin dashboard</li>";
echo "<li>Go to <strong>Affiliate Links > Settings</strong></li>";
echo "<li>Check this test page again for any new warnings</li>";
echo "<li>Verify that all text in the settings page is displayed correctly</li>";
echo "</ol>";