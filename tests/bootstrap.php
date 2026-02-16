<?php
/**
 * PHPUnit bootstrap file for Mini WP GDPR plugin tests.
 *
 * @package PowerPlugins\MiniGDPR
 */

// Define test environment constants.
define( 'MINI_GDPR_TESTS', true );
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', false );
define( 'WP_DEBUG_DISPLAY', true );

// Disable external HTTP requests.
define( 'WP_HTTP_BLOCK_EXTERNAL', true );

// Allow localhost requests (for local testing).
define( 'WP_ACCESSIBLE_HOSTS', 'localhost,127.0.0.1' );

// Get the path to the WordPress tests library.
$_tests_dir = getenv( 'WP_TESTS_DIR' );

// Fallback: Try to find WordPress test library in common locations.
if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

// Check if tests library exists.
if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find WordPress test library. Please set WP_TESTS_DIR environment variable.\n";
	echo "Example: export WP_TESTS_DIR=/tmp/wordpress-tests-lib\n";
	echo "\n";
	echo "To install WordPress test library:\n";
	echo "  bash bin/install-wp-tests.sh wordpress_test root '' localhost latest\n";
	exit( 1 );
}

// Load WordPress test library.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( __DIR__ ) . '/mini-gdpr-for-wp.php';
}

// Load the plugin before WordPress initializes.
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WordPress testing environment.
require $_tests_dir . '/includes/bootstrap.php';
