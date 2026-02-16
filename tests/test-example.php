<?php
/**
 * Example test case.
 *
 * @package PowerPlugins\MiniGDPR
 */

/**
 * Example test case for Mini WP GDPR plugin.
 */
class Test_Example extends WP_UnitTestCase {

	/**
	 * Test that the plugin is loaded.
	 */
	public function test_plugin_loaded() {
		// Check that the main plugin constant is defined.
		$this->assertTrue(
			defined( 'MINI_GDPR_FOR_WP_VERSION' ),
			'Plugin constant MINI_GDPR_FOR_WP_VERSION should be defined'
		);
	}

	/**
	 * Test that WordPress is available.
	 */
	public function test_wordpress_is_loaded() {
		$this->assertTrue(
			function_exists( 'do_action' ),
			'WordPress core functions should be available'
		);
	}

	/**
	 * Example test for basic PHP functionality.
	 */
	public function test_phpunit_works() {
		$this->assertEquals( 2, 1 + 1, 'Basic arithmetic should work' );
		$this->assertTrue( true, 'True should be true' );
	}

	/**
	 * Test that required plugin files exist.
	 */
	public function test_plugin_files_exist() {
		$plugin_dir = dirname( __DIR__ );

		$this->assertFileExists(
			$plugin_dir . '/mini-gdpr-for-wp.php',
			'Main plugin file should exist'
		);

		$this->assertFileExists(
			$plugin_dir . '/includes/constants.php',
			'Constants file should exist'
		);
	}
}
