<?php

/**
 * Script blocker — captures and defers tracker scripts until consent is given.
 *
 * @package Mini_Wp_Gdpr
 * @since   1.0.0
 */

namespace Mini_Wp_Gdpr;

defined( 'ABSPATH' ) || die();

/**
 * Intercepts registered tracker scripts, injects the consent popup, and
 * optionally suppresses script output until the visitor accepts.
 *
 * @since 1.0.0
 */
class Script_Blocker extends Component {

	// -------------------------------------------------------------------------
	// Properties
	// -------------------------------------------------------------------------

	/**
	 * Scripts that have been captured (matched against blockable patterns).
	 *
	 * @var array
	 */
	private $blocked_scripts = [];

	/**
	 * Handles of scripts eligible for blocking, as returned by the filter.
	 * Null until initialised by get_blockable_scripts().
	 *
	 * @var array|null
	 */
	private $blockable_script_handles;

	/**
	 * Full metadata for each blockable script, keyed by sanitised handle.
	 * Null until initialised by get_blockable_scripts().
	 *
	 * @var array|null
	 */
	private $blockable_scripts;

	/**
	 * Whether the "block scripts until consent" option is enabled.
	 *
	 * Set during capture_blocked_script_handles().
	 *
	 * @var bool
	 */
	private $is_block_until_consent_enabled;

	/**
	 * Whether tracker scripts should be blocked due to the current user's role.
	 *
	 * False for guests or when admin tracking is enabled. Otherwise an array
	 * from array_intersect() — truthy when the user has a no-track role.
	 *
	 * @var bool|array
	 */
	private $are_trackers_blocked_by_role;

	// -------------------------------------------------------------------------
	// Constructor
	// -------------------------------------------------------------------------

	/**
	 * Constructor.
	 *
	 * @param string $name    Plugin slug.
	 * @param string $version Plugin version.
	 */
	public function __construct( string $name, string $version ) { // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found -- Kept for future extension and docblock clarity.
		parent::__construct( $name, $version );
	}

	// -------------------------------------------------------------------------
	// Public API
	// -------------------------------------------------------------------------

	/**
	 * Return (and lazily build) the full metadata array for all blockable scripts.
	 *
	 * Fires 'mwg_init_blockable_scripts', reads handles from the
	 * 'mwg_blockable_script_handles' filter, then filters each one through
	 * 'mwg_tracker_{handle}' to obtain its definition.
	 *
	 * @return array Associative array of blockable script definitions, keyed by sanitised handle.
	 */
	public function get_blockable_scripts() {
		if ( ! is_array( $this->blockable_scripts ) ) {
			do_action( 'mwg_init_blockable_scripts' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- mwg_ is the established plugin hook prefix; WPCS rejects it as too short (< 4 chars).

			$this->blockable_script_handles = apply_filters(
				'mwg_blockable_script_handles', // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- mwg_ is the established plugin hook prefix; WPCS rejects it as too short (< 4 chars).
				[ GA_SCRIPT_HANDLE, FB_PIXEL_SCRIPT_HANDLE ]
			);

			if ( ! is_array( $this->blockable_script_handles ) ) {
				$this->blockable_script_handles = [];
			}

			$this->blockable_script_handles = array_filter( array_unique( $this->blockable_script_handles ) );

			foreach ( $this->blockable_script_handles as $blockable_script_handle ) {
				$sanitised_handle = sanitize_title( $blockable_script_handle );

				if ( ! empty( $sanitised_handle ) ) {
					$defaults = [
						'pattern'     => '',
						'field'       => 'src',
						'description' => '',
						'html'        => '',
						'after'       => '',
						'can-defer'   => true,
					];

					$definition = apply_filters( 'mwg_tracker_' . $sanitised_handle, $defaults ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- See mwg_ prefix note above.
					$definition = wp_parse_args( $definition, $defaults );

					if ( ! empty( $definition['pattern'] ) && ! empty( $definition['description'] ) ) {
						$definition['is-captured']                    = false;
						$this->blockable_scripts[ $sanitised_handle ] = $definition;
					}
				}
			}
		}

		if ( ! is_array( $this->blockable_scripts ) ) {
			$this->blockable_scripts = [];
		}

		return $this->blockable_scripts;
	}

	/**
	 * Match registered scripts against blockable patterns and enqueue the consent popup.
	 *
	 * Hooked to 'wp_enqueue_scripts' at priority 99. Iterates all registered WP
	 * scripts, captures any that match a blockable pattern, then enqueues the
	 * consent popup CSS/JS when at least one script is captured (or always-show
	 * is enabled).
	 *
	 * @return void
	 */
	public function capture_blocked_script_handles() {
		$settings               = get_settings_controller();
		$is_always_show_enabled = $settings->get_bool( OPT_SHOW_CONSENT_POPUP_EVEN_IF_NO_SCRIPTS_FOUND );
		$blockable_scripts      = $this->get_blockable_scripts();

		// Determine whether tracker scripts should be suppressed based on user role.
		$this->are_trackers_blocked_by_role = false;

		// phpcs:disable Generic.CodeAnalysis.AssignmentInCondition.Found, Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure, Generic.CodeAnalysis.EmptyStatement.DetectedIf, Generic.CodeAnalysis.EmptyStatement.DetectedElseif -- Intentional SESE guard pattern; empty bodies are comments, not missing logic.
		if ( ! is_user_logged_in() ) {
			// Guest user — role-based blocking does not apply.
		} elseif ( $settings->get_bool( OPT_IS_ADMIN_TRACKING_ENABLED ) ) {
			// Admin tracking is enabled — track all roles.
		} elseif ( empty( ( $user = wp_get_current_user() ) ) ) {
			// Cannot retrieve current user object.
		} elseif ( empty( ( $current_user_roles = $user->roles ) ) ) {
			// Current user has no roles assigned.
		} elseif ( empty( ( $dont_track_roles = array_filter( apply_filters( 'mwg_dont_track_roles', DEFAULT_DONT_TRACK_ADMIN_ROLES ) ) ) ) ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- mwg_ prefix; see note above.
			// No roles are configured for tracking exclusion.
		} else {
			$this->are_trackers_blocked_by_role = array_intersect( $current_user_roles, $dont_track_roles );
		}
		// phpcs:enable Generic.CodeAnalysis.AssignmentInCondition.Found, Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure, Generic.CodeAnalysis.EmptyStatement.DetectedIf, Generic.CodeAnalysis.EmptyStatement.DetectedElseif

		$additional_blocked_scripts = apply_filters( 'mwg_additional_blocked_scripts', [] ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- mwg_ prefix; see note above.

		$scripts = wp_scripts();

		foreach ( $scripts->registered as $script ) {
			// phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf -- Intentional SESE guard; empty body means skip.
			if ( empty( $script->handle ) ) {
				// Handle is empty — skip this entry.
			} else {
				foreach ( $blockable_scripts as $blockable_script_handle => $blockable_script ) {
					$data = '';

					switch ( $blockable_script['field'] ) {
						case 'outerhtml':
							if ( is_array( $script->extra ) && array_key_exists( 'after', $script->extra ) && is_array( $script->extra['after'] ) ) {
								foreach ( $script->extra['after'] as $inline_snippet ) {
									if ( is_string( $inline_snippet ) ) {
										$data .= $inline_snippet . "\n";
									}
								}
							}
							break;

						default:
						case 'src':
							$data = $script->src;
							break;
					}

					// phpcs:disable Generic.CodeAnalysis.EmptyStatement.DetectedIf, Generic.CodeAnalysis.EmptyStatement.DetectedElseif -- Intentional SESE guard; empty bodies mean skip.
					if ( empty( $data ) ) {
						// No data to match against — skip.
					} elseif ( empty( $blockable_script['pattern'] ) ) {
						// No pattern configured for this blockable script — skip.
					} elseif ( ! preg_match( $blockable_script['pattern'], $data ) ) {
						// Pattern does not match this script — skip.
					} else {
						$blocked_script = $blockable_script;

						// get_inline_script_data() replaces the deprecated print_inline_script() (removed in WP 6.3).
						$blocked_script['extra'] = $scripts->get_inline_script_data( $script->handle, 'extra' );
						$blocked_script['after'] = $scripts->get_inline_script_data( $script->handle, 'after' );
						$blocked_script['src']   = $script->src;

						$blocked_script['is-captured']            = true;
						$this->blocked_scripts[ $script->handle ] = $blocked_script;

						break;
					}
					// phpcs:enable Generic.CodeAnalysis.EmptyStatement.DetectedIf, Generic.CodeAnalysis.EmptyStatement.DetectedElseif
				}
			}
		}

		if ( count( $this->blocked_scripts ) > 0 || $is_always_show_enabled ) {
			$class_names = [ 'mgw-cnt', 'mgw-box' ];
			$class_names = array_merge( $class_names, get_consent_box_styles() );
			$class_names = array_filter( apply_filters( 'mwg_consent_box_classes', $class_names ) ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- mwg_ prefix; see note above.

			// TODO: Consider incrementing this when the Privacy Policy post is saved/updated.
			$cookie_sequence = 0;
			$cookie_name     = sprintf( '%s_%d_', COOKIE_NAME_BASE, $cookie_sequence );

			$consent_duration = $settings->get_int( OPT_SCRIPT_CONSENT_DURATION, DEFAULT_SCRIPT_CONSENT_DURATION );
			if ( $consent_duration <= 0 ) {
				$consent_duration = DEFAULT_SCRIPT_CONSENT_DURATION;
			}

			$this->is_block_until_consent_enabled = $settings->get_bool( OPT_BLOCK_SCRIPTS_UNTIL_USER_CONSENTS, false );

			$info_text_3 = '';
			if ( $this->are_trackers_blocked_by_role ) {
				$info_text_3 = __( "Tracking scripts are blocked because you're logged-in as an administrator", 'mini-wp-gdpr' );
			}

			// wp_kses_post() is used (not esc_html()) because the consent message may contain
			// admin-configured HTML tags like <strong> and <em>. The value is inserted via
			// innerHTML in the consent popup JS, so allowed HTML must be preserved.
			// The message is already sanitised with wp_kses_post() on save, so this is safe.
			$consent_message = wp_kses_post( $settings->get_string( OPT_COOKIE_AND_TRACKER_CONSENT_MESSAGE ) );

			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_enqueue_script( 'mini-gdpr-cookie-consent', PP_MWG_ASSETS_URL . "mini-gdpr-cookie-popup$suffix.js", [], $this->version, false );
			$rejection_cookie_name = sprintf( '%s_rej_%d_', COOKIE_NAME_BASE, $cookie_sequence );

			$localize_data = [
				'cn'     => $cookie_name,
				'rcn'    => $rejection_cookie_name,
				'cd'     => $consent_duration,
				'msg'    => $consent_message,
				'cls'    => $class_names,
				'ok'     => $this->get_popup_button_text( OPT_CONSENT_ACCEPT_TEXT, DEF_CONSENT_ACCEPT_TEXT ),
				'rjt'    => $this->get_popup_button_text( OPT_CONSENT_REJECT_TEXT, DEF_CONSENT_REJECT_TEXT ),
				'mre'    => $this->get_popup_button_text( OPT_CONSENT_INFO_BTN_TEXT, DEF_CONSENT_INFO_BTN_TEXT ),
				'nfo1'   => __( 'Along with some cookies, we use these scripts', 'mini-wp-gdpr' ),
				'nfo2'   => __( "We don't use any tracking scripts, but we do use some cookies.", 'mini-wp-gdpr' ),
				'nfo3'   => $info_text_3,
				'meta'   => $this->blocked_scripts,
				'always' => $is_always_show_enabled ? 1 : 0,
				'blkon'  => $this->is_block_until_consent_enabled ? 1 : 0,
			];

			// For logged-in users, pass AJAX credentials so rejections can be recorded server-side.
			if ( is_user_logged_in() ) {
				$localize_data['ajaxUrl']      = admin_url( 'admin-ajax.php' );
				$localize_data['rejectAction'] = REJECT_GDPR_ACTION;
				$localize_data['rejectNonce']  = wp_create_nonce( REJECT_GDPR_ACTION );
			}

			// Facebook Pixel delay-loading: pass pixel ID to JS so the consent popup
			// can load fbevents.js dynamically after the user accepts. Only included
			// when FB Pixel is enabled, a pixel ID is configured, and the current
			// user is not excluded from tracking by their role.
			if ( $settings->get_bool( OPT_IS_FB_PIXEL_TRACKING_ENABLED ) && ! $this->are_trackers_blocked_by_role ) {
				$raw_fb_id = $settings->get_string( OPT_FB_PIXEL_ID );

				if ( ! empty( $raw_fb_id ) ) {
					$localize_data['fbpxId'] = esc_js( sanitize_text_field( $raw_fb_id ) );
				}
			}

			// Google Analytics delay-loading: pass tracking code to JS so the consent
			// popup can load gtag.js dynamically after the user accepts. Only included
			// when GA is enabled, a valid tracking code is configured, and the current
			// user is not excluded from tracking by their role.
			if ( $settings->get_bool( OPT_IS_GA_TRACKING_ENABLED ) && ! $this->are_trackers_blocked_by_role ) {
				$raw_ga_code = $settings->get_string( OPT_GA_TRACKING_CODE );

				if ( ! empty( $raw_ga_code ) && preg_match( '/^(G|UA|YT|MO)-[a-zA-Z0-9-]+$/', $raw_ga_code ) ) {
					$localize_data['gaId'] = esc_js( sanitize_text_field( $raw_ga_code ) );
				}
			}

			// Microsoft Clarity delay-loading: pass Clarity ID to JS so the consent
			// popup can load clarity.ms/tag/<ID> dynamically after the user accepts.
			// Only included when Clarity is enabled, an ID is configured, and the
			// current user is not excluded from tracking by their role.
			if ( $settings->get_bool( OPT_IS_MS_CLARITY_TRACKING_ENABLED ) && ! $this->are_trackers_blocked_by_role ) {
				$raw_clarity_id = $settings->get_string( OPT_MS_CLARITY_ID );

				if ( ! empty( $raw_clarity_id ) ) {
					$localize_data['clarityId'] = esc_js( sanitize_text_field( $raw_clarity_id ) );
				}
			}

			// Custom registered trackers: pass SDK URLs to JS for generic delay-loading.
			// Populated from the mwg_register_tracker filter via Tracker_Registry.
			// Only included when the current user is not excluded from tracking by role.
			$custom_trackers = Tracker_Registry::get_js_data( $this->are_trackers_blocked_by_role );

			if ( ! empty( $custom_trackers ) ) {
				$localize_data['trackers'] = $custom_trackers;
			}

			wp_localize_script( 'mini-gdpr-cookie-consent', 'mgwcsData', $localize_data );

			wp_enqueue_style( 'mini-gdpr-cookie-consent', PP_MWG_ASSETS_URL . 'mini-gdpr-cookie-popup.css', [], $this->version );
		}
	}

	// -------------------------------------------------------------------------
	// Private helpers
	// -------------------------------------------------------------------------

	/**
	 * Return the localised popup button label for the given option key.
	 *
	 * Reads the admin-configured value from settings; if none is stored, falls
	 * back to the translatable default string.
	 *
	 * @param string $option_key  Option key (OPT_CONSENT_*_TEXT constant).
	 * @param string $default_str Default button label (DEF_CONSENT_* constant).
	 * @return string Escaped button label ready for JS localisation.
	 */
	private function get_popup_button_text( string $option_key, string $default_str ) {
		$settings = get_settings_controller();
		$stored   = $settings->get_string( $option_key );
		$text     = ! empty( $stored ) ? $stored : $default_str;

		return esc_html( $text );
	}

	/**
	 * Suppress a script tag when blocking is active and the script is captured.
	 *
	 * Hooked to 'script_loader_tag' at priority 99. Returns null to suppress the
	 * tag when all conditions are met: blocking is enabled, the handle is in the
	 * blocked-scripts list, and the script is marked as deferrable.
	 *
	 * @param string|null $tag    The full <script> tag HTML, or null if already suppressed.
	 * @param string      $handle The registered script handle.
	 * @param string      $src    The script URL.
	 * @return string|null Unchanged tag, or null to suppress output.
	 */
	public function script_loader_tag( $tag, $handle, $src ) {
		// phpcs:disable Generic.CodeAnalysis.EmptyStatement.DetectedIf, Generic.CodeAnalysis.EmptyStatement.DetectedElseif -- Intentional SESE guard; empty bodies mean pass through unchanged.
		if ( ! $this->is_block_until_consent_enabled && ! $this->are_trackers_blocked_by_role ) {
			// Blocking is not active — pass through unchanged.
		} elseif ( empty( $handle ) ) {
			// Empty handle — cannot match — pass through.
		} elseif ( ! array_key_exists( $handle, $this->blocked_scripts ) ) {
			// Handle is not in the captured list — pass through.
		} elseif ( ! $this->blocked_scripts[ $handle ]['can-defer'] ) {
			// Script is marked as non-deferrable — pass through.
		} else {
			$tag = null;
		}
		// phpcs:enable Generic.CodeAnalysis.EmptyStatement.DetectedIf, Generic.CodeAnalysis.EmptyStatement.DetectedElseif

		return $tag;
	}
}
