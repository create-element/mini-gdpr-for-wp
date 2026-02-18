<?php

/**
 * Plugin Name:  Mini WP GDPR
 * Plugin URI:   https://power-plugins.com/plugin/mini-wp-gdpr/
 * description:  Mini GDPR compliance plugin with cookie and tracking-script consent popup.
 * Version:      1.4.3
 * Author:       Power Plugins
 * Author URI:   https://power-plugins.com/
 * License:      GPLv2 or later
 * License URI:  http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  mini-wp-gdpr
 */

defined( 'ABSPATH' ) || die();

const PP_MWG_NAME    = 'mini-wp-gdpr';
const PP_MWG_VERSION = '1.4.3';

define( 'PP_MWG_DIR', plugin_dir_path( __FILE__ ) );
define( 'PP_MWG_URL', plugin_dir_url( __FILE__ ) );
define( 'PP_MWG_ADMIN_TEMPLATES_DIR', trailingslashit( PP_MWG_DIR . 'admin-templates' ) );
define( 'PP_MWG_PUBLIC_TEMPLATES_DIR', trailingslashit( PP_MWG_DIR . 'public-templates' ) );
define( 'PP_MWG_ASSETS_DIR', trailingslashit( PP_MWG_DIR . 'assets' ) );
define( 'PP_MWG_ASSETS_URL', trailingslashit( PP_MWG_URL . 'assets' ) );

// Plugin base classes (replaces pp-core.php — removed in M3).
require_once PP_MWG_DIR . 'includes/class-component.php';
require_once PP_MWG_DIR . 'includes/class-settings-core.php';
require_once PP_MWG_DIR . 'includes/functions-admin-ui.php';

require_once PP_MWG_DIR . 'constants.php';
require_once PP_MWG_DIR . 'functions.php';
require_once PP_MWG_DIR . 'functions-private.php';

require_once PP_MWG_DIR . 'includes/class-settings.php';
require_once PP_MWG_DIR . 'includes/class-user-controller.php';
require_once PP_MWG_DIR . 'includes/class-tracker-registry.php';
require_once PP_MWG_DIR . 'includes/class-script-blocker.php';
require_once PP_MWG_DIR . 'includes/class-admin-hooks.php';
require_once PP_MWG_DIR . 'includes/class-public-hooks.php';
require_once PP_MWG_DIR . 'includes/class-cf7-helper.php';

require_once PP_MWG_DIR . 'trackers/tracker-google-analytics.php';
require_once PP_MWG_DIR . 'trackers/tracker-facebook-pixel.php';
require_once PP_MWG_DIR . 'trackers/tracker-microsoft-clarity.php';

require_once PP_MWG_DIR . 'includes/class-plugin.php';

function pp_mwg_plugin_activate() {
	update_option( Mini_Wp_Gdpr\OPT_IS_COOKIE_CONSENT_POPUP_ENABLED, 1 );
	update_option( Mini_Wp_Gdpr\OPT_SHOW_CONSENT_POPUP_EVEN_IF_NO_SCRIPTS_FOUND, 1 );
}
register_activation_hook( __FILE__, 'pp_mwg_plugin_activate' );

function pp_mwg_plugin_run() {
	global $pp_mwg_plugin;

	$pp_mwg_plugin = new Mini_Wp_Gdpr\Plugin( PP_MWG_NAME, PP_MWG_VERSION );
	$pp_mwg_plugin->run();
}
pp_mwg_plugin_run();
