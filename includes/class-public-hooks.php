<?php

/**
 * Public (front-end) hooks handler.
 *
 * @package Mini_Wp_Gdpr
 * @since   1.0.0
 */

namespace Mini_Wp_Gdpr;

defined( 'ABSPATH' ) || die();

/**
 * Handles WordPress front-end hooks for the plugin.
 *
 * Registered by Plugin::init() during the 'init' action when the plugin
 * is enabled and the current request is not an admin request.
 *
 * @since 1.0.0
 */
class Public_Hooks extends Component {

	/**
	 * Constructor.
	 *
	 * @param string $name    Plugin slug.
	 * @param string $version Plugin version.
	 */
	public function __construct( string $name, string $version ) { // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found -- Kept for future extension and docblock clarity.
		parent::__construct( $name, $version );
	}

	/**
	 * Inject all configured and enabled tracker scripts.
	 *
	 * Hooked to 'wp_enqueue_scripts'. Fires the mwg_inject_tracker_{handle}
	 * action for each tracker that passes the mwg_is_tracker_enabled filter.
	 *
	 * @return void
	 */
	public function inject_configured_trackers() {
		$script_blocker  = get_script_blocker();
		$blockable_scripts = $script_blocker->get_blockable_scripts();

		$blockable_scripts = (array) apply_filters( 'mwg_injectable_tracker_metas', $blockable_scripts );

		foreach ( $blockable_scripts as $handle => $blockable_script ) {
			$is_enabled = (bool) apply_filters( 'mwg_is_tracker_enabled', true, $handle );

			if ( $is_enabled ) {
				do_action( 'mwg_inject_tracker_' . $handle );
			}
		}
	}

	/**
	 * Output the GDPR consent checkbox inside the WooCommerce registration form.
	 *
	 * Hooked to 'woocommerce_register_form' at priority 30.
	 *
	 * @return void
	 */
	public function add_to_woocommerce_form() {
		enqueue_frontend_assets();

		echo '<p class="form-row form-row-mini-gdpr">';
		echo get_accept_gdpr_checkbox_outer_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted HTML from plugin helper.
		echo '</p>';
	}

	/**
	 * Output the GDPR mini-accept form inside the WooCommerce My Account endpoint.
	 *
	 * Hooked dynamically to the configured woocommerce_account_*_endpoint action.
	 *
	 * @return void
	 */
	public function inject_into_wc_myaccount_endpoint() {
		// mwg_get_mini_accept_terms_form_for_current_user() outputs via include — no return value.
		mwg_get_mini_accept_terms_form_for_current_user();
	}
}
