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
	 *
	 * @var array
	 */
	private $blockable_script_handles;

	/**
	 * Full metadata for each blockable script, keyed by sanitised handle.
	 *
	 * @var array
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
			do_action( 'mwg_init_blockable_scripts' );

			$this->blockable_script_handles = apply_filters(
				'mwg_blockable_script_handles',
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

					$definition = apply_filters( 'mwg_tracker_' . $sanitised_handle, $defaults );
					$definition = wp_parse_args( $definition, $defaults );

					if ( ! empty( $definition['pattern'] ) && ! empty( $definition['description'] ) ) {
						$definition['is-captured']                  = false;
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

		// phpcs:disable Generic.CodeAnalysis.AssignmentInCondition.Found, Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure -- Intentional SESE guard pattern.
		if ( ! is_user_logged_in() ) {
			// Guest user — role-based blocking does not apply.
		} elseif ( $settings->get_bool( OPT_IS_ADMIN_TRACKING_ENABLED ) ) {
			// Admin tracking is enabled — track all roles.
		} elseif ( empty( ( $user = wp_get_current_user() ) ) ) {
			// Cannot retrieve current user object.
		} elseif ( empty( ( $current_user_roles = $user->roles ) ) ) {
			// Current user has no roles assigned.
		} elseif ( empty( ( $dont_track_roles = array_filter( apply_filters( 'mwg_dont_track_roles', DEFAULT_DONT_TRACK_ADMIN_ROLES ) ) ) ) ) {
			// No roles are configured for tracking exclusion.
		} else {
			$this->are_trackers_blocked_by_role = array_intersect( $current_user_roles, $dont_track_roles );
		}
		// phpcs:enable Generic.CodeAnalysis.AssignmentInCondition.Found, Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure

		$additional_blocked_scripts = apply_filters( 'mwg_additional_blocked_scripts', [] );

		$scripts = wp_scripts();

		foreach ( $scripts->registered as $script ) {
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

						$blocked_script['is-captured']              = true;
						$this->blocked_scripts[ $script->handle ] = $blocked_script;

						break;
					}
				}
			}
		}

		if ( count( $this->blocked_scripts ) > 0 || $is_always_show_enabled ) {
			$class_names = [ 'mgw-cnt', 'mgw-box' ];
			$class_names = array_merge( $class_names, get_consent_box_styles() );
			$class_names = array_filter( apply_filters( 'mwg_consent_box_classes', $class_names ) );

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

			$consent_message = esc_html( $settings->get_string( OPT_COOKIE_AND_TRACKER_CONSENT_MESSAGE ) );

			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_enqueue_script( 'mini-gdpr-cookie-consent', PP_MWG_ASSETS_URL . "mini-gdpr-cookie-popup$suffix.js", null, $this->version, false );
			wp_localize_script(
				'mini-gdpr-cookie-consent',
				'mgwcsData',
				[
					'cn'     => $cookie_name,
					'cd'     => $consent_duration,
					'msg'    => $consent_message,
					'cls'    => $class_names,
					'ok'     => __( 'Accept', 'mini-wp-gdpr' ),
					'mre'    => __( 'info...', 'mini-wp-gdpr' ),
					'nfo1'   => __( 'Along with some cookies, we use these scripts', 'mini-wp-gdpr' ),
					'nfo2'   => __( "We don't use any tracking scripts, but we do use some cookies.", 'mini-wp-gdpr' ),
					'nfo3'   => $info_text_3,
					'meta'   => $this->blocked_scripts,
					'always' => $is_always_show_enabled ? 1 : 0,
					'blkon'  => $this->is_block_until_consent_enabled ? 1 : 0,
				]
			);

			wp_enqueue_style( 'mini-gdpr-cookie-consent', PP_MWG_ASSETS_URL . 'mini-gdpr-cookie-popup.css', null, $this->version );
		}
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

		return $tag;
	}
}
