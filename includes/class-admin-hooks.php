<?php

/**
 * Admin hooks handler.
 *
 * @package Mini_Wp_Gdpr
 * @since   1.0.0
 */

namespace Mini_Wp_Gdpr;

defined( 'ABSPATH' ) || die();

/**
 * Handles WordPress admin-side hooks for the plugin.
 *
 * Registered by Plugin::admin_init() during the 'admin_init' action.
 *
 * @since 1.0.0
 */
class Admin_Hooks extends Component {

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
	 * Enqueue admin scripts and styles for the plugin settings pages.
	 *
	 * @since 1.0.0
	 * @param string $current_page The current admin page hook suffix.
	 * @return void
	 */
	public function admin_enqueue_scripts( $current_page ) {
		$are_assets_required = false;

		$settings = get_settings_controller();

		if ( current_user_can( $settings->get_settings_cap() ) ) {
			$are_assets_required = ( $current_page === 'settings_page_' . $settings->get_settings_page_name() );
		}

		if ( $are_assets_required ) {
			pp_enqueue_admin_assets();

			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_enqueue_script( $this->name, PP_MWG_ASSETS_URL . "mini-gdpr-admin$suffix.js", [], $this->version, true );

			if ( is_cf7_installed() ) {
				wp_enqueue_script( $this->name . '-cf7', PP_MWG_ASSETS_URL . "mini-gdpr-admin-cf7$suffix.js", [], $this->version, true );
			}
		}
	}

	/**
	 * Add the GDPR consent column to the Users list table.
	 *
	 * @since 1.0.0
	 * @param array $columns Existing columns array.
	 * @return array Modified columns array with GDPR status column appended.
	 */
	public function manage_users_columns( $columns ) {
		$columns['gdpr-status'] = __( 'Privacy Consent', 'mini-wp-gdpr' );

		return $columns;
	}

	/**
	 * Render the GDPR consent column value for a given user.
	 *
	 * @since 1.0.0
	 * @param string $val         Current column value.
	 * @param string $column_name Column identifier.
	 * @param int    $user_id     WordPress user ID.
	 * @return string HTML output for the column cell.
	 */
	public function manage_users_custom_column( $val, $column_name, $user_id ) {
		$result = $val;

		if ( 'gdpr-status' === $column_name ) {
			$user_controller = get_user_controller();
			$date_format     = get_option( 'date_format', 'Y-m-d H:i:s' );
			$when_display    = $user_controller->when_did_user_accept_gdpr( $user_id, $date_format );
			$when_title      = $user_controller->when_did_user_accept_gdpr( $user_id );

			if ( ! empty( $when_display ) ) {
				$result = sprintf(
					'<div class="user-gdpr user-gdpr-when-accepted" title="%s">%s</div>',
					esc_attr( $when_title ),
					esc_html( $when_display )
				);
			}
		}

		return $result;
	}
}
