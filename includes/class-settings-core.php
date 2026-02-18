<?php

/**
 * Settings core abstract class.
 *
 * @package Mini_Wp_Gdpr
 * @since   2.0.0
 */

namespace Mini_Wp_Gdpr;

defined( 'ABSPATH' ) || die();

/**
 * Abstract base class for plugin settings management.
 *
 * Handles nonce-protected settings forms, typed option get/set helpers,
 * and admin page scaffolding. Replaces the pp-core.php Settings_Core class
 * removed in M3.
 *
 * @since 2.0.0
 */
abstract class Settings_Core extends Component {

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

	/**
	 * Constructor.
	 *
	 * Derives action/nonce strings from the plugin slug so they are
	 * unique per plugin. Matches the strings used by pp-core.php to
	 * avoid invalidating any in-flight form sessions during the upgrade.
	 *
	 * @param string $name    Plugin slug.
	 * @param string $version Plugin version.
	 */
	public function __construct( string $name, string $version ) {
		parent::__construct( $name, $version );

		$this->settings_action    = 'svestngsact' . $name;
		$this->settings_nonce     = 'svestngsnce' . $name;
		$this->settings_cap       = 'manage_options';
		$this->settings_page_name = $name;
	}

	// -------------------------------------------------------------------------
	// Abstract method — must be implemented by the concrete Settings class.
	// -------------------------------------------------------------------------

	/**
	 * Persist form values to wp_options.
	 *
	 * Called automatically by maybe_save_settings() after nonce and
	 * capability checks pass.
	 *
	 * @since 2.0.0
	 */
	abstract public function save_settings();

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
	 * Override the required capability.
	 *
	 * @since 2.0.0
	 * @param string $value WordPress capability string.
	 * @return void
	 */
	public function set_settings_cap( string $value ) {
		$this->settings_cap = $value;
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
	// Admin page scaffolding helpers.
	// -------------------------------------------------------------------------

	/**
	 * Open the settings page wrapper div.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function open_wrap() {
		echo '<div class="wrap pp-wrap">';
	}

	/**
	 * Open the settings form with a nonce field.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function open_form() {
		echo '<form method="post">';
		wp_nonce_field( $this->settings_action, $this->settings_nonce );
	}

	/**
	 * Close the settings form.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function close_form() {
		echo '</form>';
	}

	/**
	 * Close the settings page wrapper div.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function close_wrap() {
		echo '</div>';
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

	// -------------------------------------------------------------------------
	// Default value and sanitisation hooks (override in subclass).
	// -------------------------------------------------------------------------

	/**
	 * Return the default value for an option.
	 *
	 * Override in subclass to provide option-specific defaults.
	 *
	 * @since 2.0.0
	 * @param string $option_name wp_options key.
	 * @return mixed
	 */
	public function get_default_value( string $option_name ) {
		return null;
	}

	/**
	 * Sanitise a raw option value before returning it.
	 *
	 * Override in subclass to enforce business rules on specific options
	 * (e.g. lock a setting when a conflicting plugin is active).
	 *
	 * @since 2.0.0
	 * @param string $option_name wp_options key.
	 * @param mixed  $value       Raw value from get_option().
	 * @return mixed
	 */
	public function sanitise_value( string $option_name, $value ) {
		return $value;
	}

	// -------------------------------------------------------------------------
	// Typed option getters and setters.
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
	 * @param string      $option_name wp_options key.
	 * @param string      $value       Value to store.
	 * @param bool|null   $autoload    Optional autoload flag passed to update_option().
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

	/**
	 * Get a float option value.
	 *
	 * @since 2.0.0
	 * @param string $option_name wp_options key.
	 * @param float  $fallback    Fallback when the option is absent or zero.
	 * @return float
	 */
	public function get_float( string $option_name, float $fallback = 0.0 ): float {
		if ( empty( $fallback ) ) {
			$fallback = floatval( $this->get_default_value( $option_name ) );
		}

		$value = floatval( get_option( $option_name, $fallback ) );

		return $this->sanitise_value( $option_name, $value );
	}

	/**
	 * Persist a float option.
	 *
	 * @since 2.0.0
	 * @param string    $option_name wp_options key.
	 * @param float     $value       Float value to store.
	 * @param bool|null $autoload    Optional autoload flag.
	 * @return void
	 */
	public function set_float( string $option_name, float $value, $autoload = null ) {
		update_option( $option_name, $value, $autoload );
	}

	/**
	 * Get an array option value (stored as a serialised PHP array).
	 *
	 * @since 2.0.0
	 * @param string $option_name wp_options key.
	 * @param array  $fallback    Fallback when the option is absent.
	 * @return array
	 */
	public function get_array( string $option_name, array $fallback = array() ): array {
		if ( empty( $fallback ) ) {
			$candidate = $this->get_default_value( $option_name );
			if ( is_array( $candidate ) ) {
				$fallback = $candidate;
			}
		}

		$value = (array) get_option( $option_name, $fallback );

		return $this->sanitise_value( $option_name, $value );
	}

	/**
	 * Persist an array option. Deletes the option when array is empty.
	 *
	 * @since 2.0.0
	 * @param string    $option_name wp_options key.
	 * @param array     $value       Array to store.
	 * @param bool|null $autoload    Optional autoload flag.
	 * @return void
	 */
	public function set_array( string $option_name, array $value = array(), $autoload = null ) {
		if ( ! empty( $value ) ) {
			update_option( $option_name, $value, $autoload );
		} else {
			delete_option( $option_name );
		}
	}

	/**
	 * Get a hex colour option value.
	 *
	 * @since 2.0.0
	 * @param string $option_name wp_options key.
	 * @param string $fallback    Fallback hex colour (e.g. '#000000').
	 * @return string Sanitised hex colour in #RGB or #RRGGBB format.
	 */
	public function get_colour_hex( string $option_name, string $fallback = '' ): string {
		if ( empty( $fallback ) ) {
			$fallback = strval( $this->get_default_value( $option_name ) );
		}

		$raw   = get_option( $option_name, $fallback );
		$value = '';

		if ( ! empty( $raw ) ) {
			$value = sanitize_hex_color( $raw );
		}

		if ( strlen( $value ) !== 4 && strlen( $value ) !== 7 ) {
			$value = '#000000';
		}

		return $this->sanitise_value( $option_name, $value );
	}

	/**
	 * Persist a hex colour option. Deletes the option for invalid values.
	 *
	 * @since 2.0.0
	 * @param string    $option_name wp_options key.
	 * @param string    $colour      Hex colour string.
	 * @param bool|null $autoload    Optional autoload flag.
	 * @return void
	 */
	public function set_colour_hex( string $option_name, string $colour, $autoload = null ) {
		if ( ! empty( $colour ) ) {
			$colour = sanitize_hex_color( $colour );
		}

		if ( strlen( $colour ) !== 4 && strlen( $colour ) !== 7 ) {
			delete_option( $option_name );
		} else {
			update_option( $option_name, $colour, $autoload );
		}
	}
}
