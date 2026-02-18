<?php

/**
 * Generic tracker registration framework.
 *
 * Provides a filter-based developer API — `mwg_register_tracker` — that allows
 * themes and plugins to register custom third-party trackers without modifying
 * the core plugin. Each registered tracker is automatically wired into the
 * existing blockable-handle and metadata filter system, and its SDK URL is
 * passed to the consent popup JavaScript for delay-loading after consent.
 *
 * Usage:
 *
 *   add_filter( 'mwg_register_tracker', function ( $trackers ) {
 *       $trackers['hotjar'] = [
 *           'handle'      => 'hotjar-analytics',
 *           'description' => 'Hotjar',
 *           'sdk_url'     => 'https://static.hotjar.com/c/hotjar-12345.js?sv=6',
 *           'pattern'     => '/hotjar-[0-9]+\.js/',
 *       ];
 *       return $trackers;
 *   } );
 *
 * After consent, the consent popup JS calls loadCustomTrackers(), which reads
 * mgwcsData.trackers and dynamically injects each registered SDK URL.
 *
 * @package Mini_Wp_Gdpr
 * @since   2.0.0
 */

namespace Mini_Wp_Gdpr;

defined( 'ABSPATH' ) || die();

/**
 * Manages custom tracker registrations.
 *
 * All methods are static — this class acts as a registry (no instantiation needed).
 *
 * @since 2.0.0
 */
class Tracker_Registry {

	// -------------------------------------------------------------------------
	// Properties
	// -------------------------------------------------------------------------

	/**
	 * Cached tracker registrations (populated on first call to get_registered()).
	 *
	 * @var array|null
	 */
	private static $registered = null;

	// -------------------------------------------------------------------------
	// Initialisation
	// -------------------------------------------------------------------------

	/**
	 * Attach hooks. Call once during plugin bootstrap (before wp_enqueue_scripts).
	 *
	 * Registers:
	 *   - mwg_blockable_script_handles filter → add_blockable_handles()
	 *   - mwg_init_blockable_scripts action  → register_tracker_filters()
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function init() {
		add_filter(
			'mwg_blockable_script_handles', // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- mwg_ is the established plugin hook prefix; WPCS rejects it as too short.
			[ __CLASS__, 'add_blockable_handles' ]
		);

		add_action(
			'mwg_init_blockable_scripts', // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- mwg_ prefix; see note above.
			[ __CLASS__, 'register_tracker_filters' ]
		);
	}

	// -------------------------------------------------------------------------
	// Public API
	// -------------------------------------------------------------------------

	/**
	 * Retrieve all registered custom trackers.
	 *
	 * Fires the 'mwg_register_tracker' filter on first call and caches the
	 * result. Subsequent calls return the cached value.
	 *
	 * Filter signature:
	 *   apply_filters( 'mwg_register_tracker', array $trackers )
	 *
	 * Each tracker is an associative array with the following keys:
	 *   - 'handle'      (string, required) — WordPress script handle to register as blockable.
	 *   - 'description' (string, required) — Human-readable tracker name for the info overlay.
	 *   - 'sdk_url'     (string, required) — Full URL of the tracker SDK to load after consent.
	 *   - 'pattern'     (string, optional) — Regex pattern for Script_Blocker to detect the tracker.
	 *   - 'field'       (string, optional) — Match field: 'src' (default) or 'outerhtml'.
	 *   - 'can_defer'   (bool,   optional) — Whether the script tag can be suppressed pre-consent.
	 *
	 * @since 2.0.0
	 * @return array Associative array of tracker registrations, keyed by tracker slug.
	 */
	public static function get_registered() {
		if ( null === self::$registered ) {
			$registered       = apply_filters( 'mwg_register_tracker', [] ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- mwg_ prefix; see note above.
			self::$registered = is_array( $registered ) ? $registered : [];
		}

		return self::$registered;
	}

	/**
	 * Return JavaScript-ready tracker data for mgwcsData.trackers.
	 *
	 * Returns only trackers that have an sdk_url configured. When the visitor is
	 * excluded from tracking by their role ($are_trackers_blocked_by_role is truthy),
	 * returns an empty array so no custom trackers are loaded for that visitor.
	 *
	 * @since 2.0.0
	 * @param bool|array $are_trackers_blocked_by_role Whether trackers are blocked by user role.
	 * @return array Associative array keyed by sanitised handle, each entry has 'sdkUrl'.
	 */
	public static function get_js_data( $are_trackers_blocked_by_role ) {
		$js_data = [];

		if ( ! $are_trackers_blocked_by_role ) {
			foreach ( self::get_registered() as $tracker ) {
				$handle  = sanitize_title( isset( $tracker['handle'] ) ? (string) $tracker['handle'] : '' );
				$sdk_url = isset( $tracker['sdk_url'] ) ? esc_url_raw( (string) $tracker['sdk_url'] ) : '';

				if ( ! empty( $handle ) && ! empty( $sdk_url ) ) {
					$js_data[ $handle ] = [
						'sdkUrl' => $sdk_url,
					];
				}
			}
		}

		return $js_data;
	}

	// -------------------------------------------------------------------------
	// Hook callbacks
	// -------------------------------------------------------------------------

	/**
	 * Add registered tracker handles to the blockable-handles list.
	 *
	 * Hooked to 'mwg_blockable_script_handles'.
	 *
	 * @since 2.0.0
	 * @param array $handles Existing blockable script handles.
	 * @return array Updated handles array including all registered custom tracker handles.
	 */
	public static function add_blockable_handles( array $handles ) {
		foreach ( self::get_registered() as $tracker ) {
			$handle = isset( $tracker['handle'] ) ? (string) $tracker['handle'] : '';

			if ( ! empty( $handle ) ) {
				$handles[] = $handle;
			}
		}

		return $handles;
	}

	/**
	 * Register the per-tracker metadata filter for each custom tracker.
	 *
	 * Hooked to 'mwg_init_blockable_scripts', which fires inside
	 * Script_Blocker::get_blockable_scripts() — just before the
	 * 'mwg_blockable_script_handles' filter is applied and metadata is read.
	 * This ensures the 'mwg_tracker_{handle}' filter is in place when
	 * get_blockable_scripts() queries each handle's definition.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function register_tracker_filters() {
		foreach ( self::get_registered() as $tracker ) {
			$handle = sanitize_title( isset( $tracker['handle'] ) ? (string) $tracker['handle'] : '' );

			if ( ! empty( $handle ) ) {
				$tracker_data = $tracker;

				add_filter(
					'mwg_tracker_' . $handle, // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- mwg_ prefix; see note above.
					function () use ( $tracker_data ) {
						return [
							'pattern'     => isset( $tracker_data['pattern'] ) ? (string) $tracker_data['pattern'] : '',
							'field'       => isset( $tracker_data['field'] ) ? (string) $tracker_data['field'] : 'src',
							'description' => isset( $tracker_data['description'] ) ? esc_html( (string) $tracker_data['description'] ) : esc_html( (string) $tracker_data['handle'] ),
							'can-defer'   => ! empty( $tracker_data['can_defer'] ),
						];
					}
				);
			}
		}
	}
}

// Initialise the registry — attach hooks at load time, just like the tracker files.
Tracker_Registry::init();
