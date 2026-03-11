<?php

/**
 * Plugin settings class.
 *
 * @package Mini_Wp_Gdpr
 * @since   1.0.0
 */

namespace Mini_Wp_Gdpr;

defined( 'ABSPATH' ) || die();

/**
 * Manages the plugin settings page and option persistence.
 *
 * Handles nonce-protected settings forms, typed option get/set helpers,
 * admin page scaffolding, and WordPress Settings API registration for all
 * plugin options.
 *
 * The form uses a custom nonce approach. All options are additionally
 * registered via register_setting() so WordPress is aware of them and can
 * apply sanitisation rules when options are updated programmatically.
 *
 * Prior to v2.1.0 this class extended Settings_Core (which in turn extended
 * Component). Both abstractions have been inlined here — the plugin only has
 * one settings page, so the indirection added no value.
 *
 * @since 1.0.0
 */
class Settings {

	// -------------------------------------------------------------------------
	// Properties (formerly in Settings_Core).
	// -------------------------------------------------------------------------

	/**
	 * Nonce action string for settings form submission.
	 *
	 * @var string
	 */
	private $settings_action;

	/**
	 * Nonce field name embedded in the settings form.
	 *
	 * @var string
	 */
	private $settings_nonce;

	/**
	 * WordPress capability required to access the settings page.
	 *
	 * @var string
	 */
	protected $settings_cap;

	/**
	 * Settings page slug (used in add_options_page and the URL ?page= param).
	 *
	 * @var string
	 */
	protected $settings_page_name;

	// -------------------------------------------------------------------------
	// Constructor.
	// -------------------------------------------------------------------------

	/**
	 * Constructor.
	 *
	 * Derives action/nonce strings from the plugin slug so they are unique
	 * per plugin. The strings match the original pp-core.php values to avoid
	 * invalidating any in-flight form sessions during the upgrade.
	 *
	 * Does not register any hooks directly — the Plugin class handles all
	 * hook registration so this class stays testable and free of side effects
	 * on instantiation.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->settings_action    = 'svestngsact' . PP_MWG_NAME;
		$this->settings_nonce     = 'svestngsnce' . PP_MWG_NAME;
		$this->settings_cap       = 'manage_options';
		$this->settings_page_name = PP_MWG_NAME;
	}

	// -------------------------------------------------------------------------
	// Capability & page name accessors.
	// -------------------------------------------------------------------------

	/**
	 * Return the capability required to manage settings.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_settings_cap() {
		return $this->settings_cap;
	}

	/**
	 * Return the settings page slug.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_settings_page_name() {
		return $this->settings_page_name;
	}

	/**
	 * Return the full admin URL to the settings page.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_settings_page_url() {
		return admin_url( 'options-general.php?page=' . $this->settings_page_name );
	}

	// -------------------------------------------------------------------------
	// WordPress Settings API registration.
	// -------------------------------------------------------------------------

	/**
	 * Register all plugin options with the WordPress Settings API.
	 *
	 * Called on `admin_init` by Plugin::admin_init(). Registers each option
	 * name, type, and sanitise callback so WordPress is aware of which options
	 * this plugin owns. Sanitise callbacks here apply when options are written
	 * via the WP Options API directly; the settings form uses the custom nonce
	 * approach which calls save_settings() directly.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_settings() {
		$option_group = 'pp_mwg_settings';

		// Boolean options — stored as 'true' (present) or absent.
		$bool_options = array(
			OPT_IS_COOKIE_CONSENT_POPUP_ENABLED,
			OPT_SHOW_CONSENT_POPUP_EVEN_IF_NO_SCRIPTS_FOUND,
			OPT_IS_GA_TRACKING_ENABLED,
			OPT_GA_CONSENT_MODE_ENABLED,
			OPT_IS_ADMIN_TRACKING_ENABLED,
			OPT_IS_FB_PIXEL_TRACKING_ENABLED,
			OPT_IS_FB_PIXEL_NOSCRIPT_ENABLED,
			OPT_IS_MS_CLARITY_TRACKING_ENABLED,
			OPT_IS_WC_MYACCOUNT_INJECT_ENABLED,
			OPT_IS_NEW_ORDER_TCSANDCS_CONSENT_ENABLED,
		);

		foreach ( $bool_options as $option_name ) {
			register_setting(
				$option_group,
				$option_name,
				array(
					'type'    => 'boolean',
					'default' => false,
				)
			);
		}

		// Google Analytics tracking code (e.g. G-XXXXXXXXXX).
		register_setting(
			$option_group,
			OPT_GA_TRACKING_CODE,
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			)
		);

		// WooCommerce My Account endpoint slug.
		register_setting(
			$option_group,
			OPT_WHICH_WC_MYACCOUNT_ENDPOINT,
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_title',
				'default'           => '',
			)
		);

		// Cookie consent popup message (limited HTML allowed).
		register_setting(
			$option_group,
			OPT_COOKIE_AND_TRACKER_CONSENT_MESSAGE,
			array(
				'type'              => 'string',
				'sanitize_callback' => 'wp_kses_post',
				'default'           => '',
			)
		);

		// Consent popup button labels (plain text).
		$button_text_options = array(
			OPT_CONSENT_ACCEPT_TEXT,
			OPT_CONSENT_REJECT_TEXT,
			OPT_CONSENT_INFO_BTN_TEXT,
		);

		foreach ( $button_text_options as $option_name ) {
			register_setting(
				$option_group,
				$option_name,
				array(
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
					'default'           => '',
				)
			);
		}

		// Facebook Pixel ID (numeric string).
		register_setting(
			$option_group,
			OPT_FB_PIXEL_ID,
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			)
		);

		// Microsoft Clarity tracking ID.
		register_setting(
			$option_group,
			OPT_MS_CLARITY_ID,
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			)
		);

		// Consent duration in days.
		register_setting(
			$option_group,
			OPT_SCRIPT_CONSENT_DURATION,
			array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'default'           => DEFAULT_SCRIPT_CONSENT_DURATION,
			)
		);

		// Consent box screen position (integer index 0–8).
		register_setting(
			$option_group,
			OPT_CONSENT_BOX_POSITION,
			array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'default'           => DEFAULT_CONSENT_BOX_POSITION,
			)
		);
	}

	// -------------------------------------------------------------------------
	// Admin menu.
	// -------------------------------------------------------------------------

	/**
	 * Add the plugin settings page to the WordPress admin Options menu.
	 *
	 * Hooked to `admin_menu` by Plugin::run(). The page appears at
	 * Settings → Mini WP GDPR.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function initialise_admin_menu() {
		add_options_page(
			__( 'Mini WP GDPR', 'mini-wp-gdpr' ),
			__( 'Mini WP GDPR', 'mini-wp-gdpr' ),
			$this->settings_cap,
			$this->get_settings_page_name(),
			array( $this, 'render_settings_page' )
		);
	}

	// -------------------------------------------------------------------------
	// Settings page rendering.
	// -------------------------------------------------------------------------

	/**
	 * Render the plugin settings page.
	 *
	 * Outputs one of three views depending on context:
	 * - An authorisation error if the current user lacks the required capability.
	 * - A prompt to create a Privacy Policy page if none is configured.
	 * - The full settings form with all admin template sections.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_settings_page() {
		if ( ! current_user_can( $this->settings_cap ) ) {
			printf( '<p>%s</p>', esc_html__( 'Not authorised.', 'mini-wp-gdpr' ) );
		} elseif ( ! is_mini_gdpr_enabled() ) {
			printf(
				'<p>%s</p><p><a href="%s" class="button">%s</a></p>',
				esc_html__( 'You need to create a Privacy Policy page first.', 'mini-wp-gdpr' ),
				esc_url( admin_url( 'options-privacy.php' ) ),
				esc_html__( 'Create a Privacy Policy', 'mini-wp-gdpr' )
			);
		} else {
			echo '<div class="wrap mwg-wrap">';

			printf(
				'<h1>%s%s</h1>',
				esc_html( get_admin_page_title() ),
				get_settings_header_html( 'https://power-plugins.com/plugin/mini-wp-gdpr/' ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_settings_header_html() returns pre-escaped HTML.
			);

			// --- Tab navigation ---------------------------------------------------
			echo '<nav class="nav-tab-wrapper wp-clearfix">';
			printf( '<a href="#consent" class="nav-tab nav-tab-active" data-tab="consent">%s</a>', esc_html__( 'Consent Popup', 'mini-wp-gdpr' ) );
			printf( '<a href="#trackers" class="nav-tab" data-tab="trackers">%s</a>', esc_html__( 'Trackers', 'mini-wp-gdpr' ) );
			printf( '<a href="#integrations" class="nav-tab" data-tab="integrations">%s</a>', esc_html__( 'Integrations', 'mini-wp-gdpr' ) );
			printf( '<a href="#status" class="nav-tab" data-tab="status">%s</a>', esc_html__( 'Status', 'mini-wp-gdpr' ) );
			echo '</nav>';

			echo '<form method="post">';
			wp_nonce_field( $this->settings_action, $this->settings_nonce );

			// $settings is referenced by the included admin templates.
			$settings = $this; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- local variable, not a global override.

			// --- Tab: Consent Popup -----------------------------------------------
			echo '<div id="consent-panel" class="mwg-tab-panel mwg-tab-panel--active">';
			include PP_MWG_ADMIN_TEMPLATES_DIR . 'cookie-consent-settings.php';
			echo '</div>';

			// --- Tab: Trackers ----------------------------------------------------
			echo '<div id="trackers-panel" class="mwg-tab-panel">';
			include PP_MWG_ADMIN_TEMPLATES_DIR . 'trackers-settings.php';
			echo '</div>';

			// --- Tab: Integrations ------------------------------------------------
			echo '<div id="integrations-panel" class="mwg-tab-panel">';

			$has_wc  = is_woocommerce_available();
			$has_cf7 = is_cf7_installed();

			if ( $has_wc ) {
				include PP_MWG_ADMIN_TEMPLATES_DIR . 'woocommerce-settings.php';
			}

			if ( $has_wc && $has_cf7 ) {
				echo '<hr />';
			}

			if ( $has_cf7 ) {
				include PP_MWG_ADMIN_TEMPLATES_DIR . 'contact-form-7-settings.php';
			}

			if ( ! $has_wc && ! $has_cf7 ) {
				printf(
					'<p class="mwg-help">%s</p>',
					esc_html__( 'No supported integrations are currently active. Install WooCommerce or Contact Form 7 to see integration options here.', 'mini-wp-gdpr' )
				);
			}

			echo '</div>';

			// --- Tab: Status ------------------------------------------------------
			echo '<div id="status-panel" class="mwg-tab-panel">';
			include PP_MWG_ADMIN_TEMPLATES_DIR . 'consent-stats.php';

			if ( IS_RESET_ALL_CONSENT_ENABLED && current_user_can( 'manage_options' ) ) {
				echo '<hr />';

				printf(
					'<p class="mwg-form-row"><span class="dashicons dashicons-warning"></span> %s</p>',
					esc_html__( 'This will reset all consent given by all the registered users of the site.', 'mini-wp-gdpr' )
				);

				$reset_args  = array(
					'action'         => RESET_PRIVACY_POLICY_CONSENTS,
					'nonce'          => wp_create_nonce( RESET_PRIVACY_POLICY_CONSENTS ),
					'confirmMessage' => __( 'Really reset all user consents now?', 'mini-wp-gdpr' ),
				);
				$reset_props = sprintf(
					'data-reset-all-consents="%s"',
					esc_attr( wp_json_encode( $reset_args ) )
				);

				echo get_button_with_spinner_html( __( 'Reset Now', 'mini-wp-gdpr' ), '', $reset_props ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_button_with_spinner_html() returns pre-escaped HTML.
			}

			echo '</div>';

			submit_button( esc_html__( 'Save Changes', 'mini-wp-gdpr' ) );

			echo '</form>';

			echo '</div>';
		}
	}

	// -------------------------------------------------------------------------
	// Settings save handler.
	// -------------------------------------------------------------------------

	/**
	 * Save settings if a valid form submission is detected.
	 *
	 * Validates: admin context, nonce, and user capability before delegating
	 * to save_settings().
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function maybe_save_settings() {
		$is_valid_request = is_admin()
			&& ! wp_doing_ajax()
			&& array_key_exists( $this->settings_nonce, $_POST )
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- nonce value must not be altered before verification.
			&& wp_verify_nonce( wp_unslash( $_POST[ $this->settings_nonce ] ), $this->settings_action )
			&& current_user_can( $this->settings_cap );

		if ( $is_valid_request ) {
			$this->save_settings();
		}
	}

	/**
	 * Persist all settings form values to wp_options.
	 *
	 * Called by maybe_save_settings() after nonce and capability checks pass.
	 * Reads values from $_POST and delegates to the typed set_*() helpers.
	 *
	 * Nonce verification is performed by the caller (maybe_save_settings()).
	 * The phpcs:disable comment below suppresses the resulting false-positive
	 * NonceVerification warnings throughout this method.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function save_settings() {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- nonce verified by maybe_save_settings().
		$this->set_bool( OPT_IS_WC_MYACCOUNT_INJECT_ENABLED, array_key_exists( OPT_IS_WC_MYACCOUNT_INJECT_ENABLED, $_POST ) );
		$this->set_bool( OPT_IS_COOKIE_CONSENT_POPUP_ENABLED, array_key_exists( OPT_IS_COOKIE_CONSENT_POPUP_ENABLED, $_POST ) );
		$this->set_bool( OPT_SHOW_CONSENT_POPUP_EVEN_IF_NO_SCRIPTS_FOUND, array_key_exists( OPT_SHOW_CONSENT_POPUP_EVEN_IF_NO_SCRIPTS_FOUND, $_POST ) );
		$this->set_bool( OPT_IS_GA_TRACKING_ENABLED, array_key_exists( OPT_IS_GA_TRACKING_ENABLED, $_POST ) );
		$this->set_bool( OPT_GA_CONSENT_MODE_ENABLED, array_key_exists( OPT_GA_CONSENT_MODE_ENABLED, $_POST ) );
		$this->set_bool( OPT_IS_ADMIN_TRACKING_ENABLED, array_key_exists( OPT_IS_ADMIN_TRACKING_ENABLED, $_POST ) );
		$this->set_bool( OPT_IS_NEW_ORDER_TCSANDCS_CONSENT_ENABLED, array_key_exists( OPT_IS_NEW_ORDER_TCSANDCS_CONSENT_ENABLED, $_POST ) );
		$this->set_bool( OPT_IS_FB_PIXEL_TRACKING_ENABLED, array_key_exists( OPT_IS_FB_PIXEL_TRACKING_ENABLED, $_POST ) );
		$this->set_bool( OPT_IS_FB_PIXEL_NOSCRIPT_ENABLED, array_key_exists( OPT_IS_FB_PIXEL_NOSCRIPT_ENABLED, $_POST ) );
		$this->set_bool( OPT_IS_MS_CLARITY_TRACKING_ENABLED, array_key_exists( OPT_IS_MS_CLARITY_TRACKING_ENABLED, $_POST ) );
		if ( array_key_exists( OPT_WHICH_WC_MYACCOUNT_ENDPOINT, $_POST ) ) {
			$this->set_string( OPT_WHICH_WC_MYACCOUNT_ENDPOINT, sanitize_title( wp_unslash( $_POST[ OPT_WHICH_WC_MYACCOUNT_ENDPOINT ] ) ) );
		}

		if ( array_key_exists( OPT_GA_TRACKING_CODE, $_POST ) ) {
			$this->set_string( OPT_GA_TRACKING_CODE, sanitize_text_field( wp_unslash( $_POST[ OPT_GA_TRACKING_CODE ] ) ) );
		} else {
			$this->set_string( OPT_GA_TRACKING_CODE, '' );
		}

		if ( array_key_exists( OPT_SCRIPT_CONSENT_DURATION, $_POST ) ) {
			$this->set_int( OPT_SCRIPT_CONSENT_DURATION, absint( $_POST[ OPT_SCRIPT_CONSENT_DURATION ] ) );
		}

		if ( array_key_exists( OPT_CONSENT_BOX_POSITION, $_POST ) ) {
			$this->set_int( OPT_CONSENT_BOX_POSITION, absint( $_POST[ OPT_CONSENT_BOX_POSITION ] ) );
		}

		if ( array_key_exists( OPT_COOKIE_AND_TRACKER_CONSENT_MESSAGE, $_POST ) ) {
			$this->set_string( OPT_COOKIE_AND_TRACKER_CONSENT_MESSAGE, wp_kses_post( wp_unslash( $_POST[ OPT_COOKIE_AND_TRACKER_CONSENT_MESSAGE ] ) ) );
		} else {
			$this->set_string( OPT_COOKIE_AND_TRACKER_CONSENT_MESSAGE, '' );
		}

		if ( array_key_exists( OPT_FB_PIXEL_ID, $_POST ) ) {
			$this->set_string( OPT_FB_PIXEL_ID, sanitize_text_field( wp_unslash( $_POST[ OPT_FB_PIXEL_ID ] ) ) );
		} else {
			$this->set_string( OPT_FB_PIXEL_ID, '' );
		}

		if ( array_key_exists( OPT_MS_CLARITY_ID, $_POST ) ) {
			$this->set_string( OPT_MS_CLARITY_ID, sanitize_text_field( wp_unslash( $_POST[ OPT_MS_CLARITY_ID ] ) ) );
		} else {
			$this->set_string( OPT_MS_CLARITY_ID, '' );
		}

		// Consent popup button labels (plain text; empty = use default).
		if ( array_key_exists( OPT_CONSENT_ACCEPT_TEXT, $_POST ) ) {
			$this->set_string( OPT_CONSENT_ACCEPT_TEXT, sanitize_text_field( wp_unslash( $_POST[ OPT_CONSENT_ACCEPT_TEXT ] ) ) );
		} else {
			$this->set_string( OPT_CONSENT_ACCEPT_TEXT, '' );
		}

		if ( array_key_exists( OPT_CONSENT_REJECT_TEXT, $_POST ) ) {
			$this->set_string( OPT_CONSENT_REJECT_TEXT, sanitize_text_field( wp_unslash( $_POST[ OPT_CONSENT_REJECT_TEXT ] ) ) );
		} else {
			$this->set_string( OPT_CONSENT_REJECT_TEXT, '' );
		}

		if ( array_key_exists( OPT_CONSENT_INFO_BTN_TEXT, $_POST ) ) {
			$this->set_string( OPT_CONSENT_INFO_BTN_TEXT, sanitize_text_field( wp_unslash( $_POST[ OPT_CONSENT_INFO_BTN_TEXT ] ) ) );
		} else {
			$this->set_string( OPT_CONSENT_INFO_BTN_TEXT, '' );
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing
	}

	// -------------------------------------------------------------------------
	// Default value and sanitisation overrides.
	// -------------------------------------------------------------------------

	/**
	 * Return the default value for a named option.
	 *
	 * Called by get_string() / get_int() when no value has been stored yet.
	 *
	 * @since 1.0.0
	 * @param string $option_name wp_options key.
	 * @return mixed Default value, or null if no default is defined for this option.
	 */
	public function get_default_value( string $option_name ) {
		$value = null;

		if ( OPT_COOKIE_AND_TRACKER_CONSENT_MESSAGE === $option_name ) {
			$value = sprintf(
				/* translators: %s: site name. */
				__( '%s uses cookies and analytics to create a better user experience. Are you OK with this?', 'mini-wp-gdpr' ),
				get_bloginfo( 'name' )
			);
		}

		return $value;
	}

	/**
	 * Apply business-rule sanitisation to a raw option value.
	 *
	 * Called by the get_*() helpers after reading from the database. Enforces
	 * that GA tracking is disabled when a conflicting third-party GA injector
	 * plugin is active.
	 *
	 * @since 1.0.0
	 * @param string $option_name wp_options key.
	 * @param mixed  $value       Raw value retrieved from get_option().
	 * @return mixed Sanitised value.
	 */
	public function sanitise_value( string $option_name, $value ) {
		$result = $value;

		if ( OPT_IS_GA_TRACKING_ENABLED === $option_name && is_external_ga_injector_plugin_installed() ) {
			$result = false;
		}

		return $result;
	}

	// -------------------------------------------------------------------------
	// Typed option getters and setters (formerly in Settings_Core).
	// -------------------------------------------------------------------------

	/**
	 * Get a string option value.
	 *
	 * @since 2.0.0
	 * @param string $option_name wp_options key.
	 * @param string $fallback    Fallback when the option is absent or empty.
	 * @return string
	 */
	public function get_string( string $option_name, string $fallback = '' ): string {
		if ( empty( $fallback ) ) {
			$fallback = strval( $this->get_default_value( $option_name ) );
		}

		$value = strval( get_option( $option_name, $fallback ) );

		return $this->sanitise_value( $option_name, $value );
	}

	/**
	 * Persist a string option. Deletes the option when value is empty.
	 *
	 * @since 2.0.0
	 * @param string    $option_name wp_options key.
	 * @param string    $value       Value to store.
	 * @param bool|null $autoload    Optional autoload flag passed to update_option().
	 * @return void
	 */
	public function set_string( string $option_name, string $value = '', $autoload = null ) {
		if ( ! empty( $value ) ) {
			update_option( $option_name, $value, $autoload );
		} else {
			delete_option( $option_name );
		}
	}

	/**
	 * Get a boolean option value.
	 *
	 * Uses filter_var with FILTER_VALIDATE_BOOLEAN so stored strings like
	 * 'true', '1', 'yes', 'on' are all treated as true.
	 *
	 * @since 2.0.0
	 * @param string $option_name wp_options key.
	 * @param bool   $fallback    Fallback when the option is absent.
	 * @return bool
	 */
	public function get_bool( string $option_name, bool $fallback = false ): bool {
		return filter_var( get_option( $option_name, $fallback ), FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Persist a boolean option. Stores 'true' / deletes the option for false.
	 *
	 * @since 2.0.0
	 * @param string    $option_name wp_options key.
	 * @param mixed     $value       Value to evaluate as boolean.
	 * @param bool|null $autoload    Optional autoload flag.
	 * @return void
	 */
	public function set_bool( string $option_name, $value, $autoload = null ) {
		if ( filter_var( $value, FILTER_VALIDATE_BOOLEAN ) ) {
			update_option( $option_name, 'true', $autoload );
		} else {
			delete_option( $option_name );
		}
	}

	/**
	 * Get an integer option value.
	 *
	 * @since 2.0.0
	 * @param string $option_name wp_options key.
	 * @param int    $fallback    Fallback when the option is absent or zero.
	 * @return int
	 */
	public function get_int( string $option_name, int $fallback = 0 ): int {
		if ( empty( $fallback ) ) {
			$fallback = intval( $this->get_default_value( $option_name ) );
		}

		$value = intval( get_option( $option_name, $fallback ) );

		return $this->sanitise_value( $option_name, $value );
	}

	/**
	 * Persist an integer option.
	 *
	 * @since 2.0.0
	 * @param string    $option_name wp_options key.
	 * @param int       $value       Integer value to store.
	 * @param bool|null $autoload    Optional autoload flag.
	 * @return void
	 */
	public function set_int( string $option_name, int $value, $autoload = null ) {
		update_option( $option_name, $value, $autoload );
	}
}
