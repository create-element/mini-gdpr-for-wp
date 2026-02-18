<?php
/**
 * Main plugin orchestrator class.
 *
 * Bootstraps all plugin features: hooks, admin UI, WooCommerce integration,
 * Contact Form 7 support, AJAX handlers, and the cookie consent popup.
 *
 * @package Mini_Wp_Gdpr
 * @since   1.0.0
 */

namespace Mini_Wp_Gdpr;

defined( 'ABSPATH' ) || die();

/**
 * Core plugin class.
 *
 * Instantiated once by the bootstrap function in mini-wp-gdpr.php and stored
 * in the global $pp_mwg_plugin variable.
 *
 * @since 1.0.0
 */
class Plugin extends Component {

	// -----------------------------------------------------------------------
	// Properties
	// -----------------------------------------------------------------------

	/**
	 * Admin hooks handler.
	 *
	 * @var Admin_Hooks|null
	 */
	private $admin_hooks;

	/**
	 * Public hooks handler.
	 *
	 * @var Public_Hooks|null
	 */
	private $public_hooks;

	/**
	 * Script blocker instance (lazy-loaded).
	 *
	 * @var Script_Blocker|null
	 */
	private $script_blocker;

	/**
	 * User controller instance (lazy-loaded).
	 *
	 * @var User_Controller|null
	 */
	private $user_controller;

	/**
	 * CF7 helper instance (lazy-loaded).
	 *
	 * @var CF7_Helper|null
	 */
	private $cf7_helper;

	/**
	 * Settings controller instance.
	 *
	 * @var Settings
	 */
	private $settings;

	// -----------------------------------------------------------------------
	// Constructor
	// -----------------------------------------------------------------------

	/**
	 * Constructor.
	 *
	 * @param string $name    Plugin slug / text domain.
	 * @param string $version Plugin version string.
	 */
	public function __construct( string $name, string $version ) { // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found -- Kept for future extension and docblock clarity.
		parent::__construct( $name, $version );
	}

	// -----------------------------------------------------------------------
	// Bootstrap
	// -----------------------------------------------------------------------

	/**
	 * Registers all WordPress action and filter hooks.
	 *
	 * Called immediately after instantiation by pp_mwg_plugin_run().
	 *
	 * @return void
	 */
	public function run() {
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );

		$this->settings = new Settings( $this->name, $this->version );
		add_action( 'admin_menu', array( $this->settings, 'initialise_admin_menu' ) );
	}

	/**
	 * Loads the plugin text domain for translations.
	 *
	 * @return void
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( $this->name, false, $this->name . '/languages' );
	}

	// -----------------------------------------------------------------------
	// Hook callbacks
	// -----------------------------------------------------------------------

	/**
	 * Fires on the WordPress 'init' action.
	 *
	 * Registers public hooks, script blocker, WooCommerce hooks, and AJAX
	 * handlers — but only when the plugin is enabled.
	 *
	 * @return void
	 */
	public function init() {
		if ( is_mini_gdpr_enabled() ) {
			$this->public_hooks = new Public_Hooks( $this->name, $this->version );

			if ( ! is_admin() ) {
				add_action( 'wp_enqueue_scripts', array( $this->public_hooks, 'inject_configured_trackers' ) );

				$script_blocker = $this->get_script_blocker();
				if ( $this->settings->get_bool( OPT_IS_COOKIE_CONSENT_POPUP_ENABLED ) ) {
					add_action( 'wp_enqueue_scripts', array( $script_blocker, 'capture_blocked_script_handles' ), 99 );
					add_filter( 'script_loader_tag', array( $script_blocker, 'script_loader_tag' ), 99, 3 );
				}
			}

			add_action( 'wpcf7_mail_sent', array( $this, 'wpcf7_mail_sent' ), 10, 1 );

			add_action( 'woocommerce_register_form', array( $this->public_hooks, 'add_to_woocommerce_form' ), 30 );
			add_action( 'woocommerce_register_post', array( $this, 'validate_registration' ), 10, 3 );
			add_action( 'woocommerce_created_customer', array( $this, 'save_new_customer_gdpr_status' ), 10, 2 );
			add_action( 'woocommerce_new_order', array( $this, 'woocommerce_new_order' ), 10, 2 );

			add_action( 'wp_ajax_' . ACCEPT_GDPR_ACTION, array( $this, 'accept_via_ajax' ) );
			add_action( 'wp_ajax_' . REJECT_GDPR_ACTION, array( $this, 'reject_via_ajax' ) );
			add_action( 'wp_ajax_' . INSTALL_CF7_CONSENT_ACTION, array( $this, 'install_cf7_form' ) );
			add_action( 'wp_ajax_' . RESET_PRIVACY_POLICY_CONSENTS, array( $this, 'reset_all_privacy_consents' ) );

			// phpcs:disable Generic.CodeAnalysis.EmptyStatement, Generic.CodeAnalysis.AssignmentInCondition, Squiz.PHP.DisallowMultipleAssignments -- Intentional SESE guard pattern.
			if ( is_admin() || wp_doing_ajax() ) {
				// No front-end WooCommerce My Account injection needed.
			} elseif ( ! $this->settings->get_bool( OPT_IS_WC_MYACCOUNT_INJECT_ENABLED ) ) {
				// My Account injection is disabled.
			} elseif ( empty( ( $endpoint = sanitize_title( $this->settings->get_string( OPT_WHICH_WC_MYACCOUNT_ENDPOINT ) ) ) ) ) {
				// No My Account endpoint configured.
			} elseif ( mwg_has_user_accepted_privacy_policy() ) {
				// User already accepted — no need to show the form again.
			} else {
				$priority = intval( apply_filters( 'pp_mwg_myaccount_priority', DEFAULT_MYACCOUNT_INJECT_PRIORITY ) );
				if ( 'dashboard' === $endpoint ) {
					$action = 'woocommerce_account_' . $endpoint;
				} else {
					$action = 'woocommerce_account_' . $endpoint . '_endpoint';
				}

				add_action( $action, array( $this->public_hooks, 'inject_into_wc_myaccount_endpoint' ), $priority );
			}
			// phpcs:enable Generic.CodeAnalysis.EmptyStatement, Generic.CodeAnalysis.AssignmentInCondition, Squiz.PHP.DisallowMultipleAssignments
		}
	}

	/**
	 * Fires on the WordPress 'admin_init' action.
	 *
	 * Registers all plugin options with the WordPress Settings API unconditionally,
	 * then — when the plugin is fully enabled — instantiates admin hooks, registers
	 * the user-list columns, and saves any submitted settings form.
	 *
	 * register_settings() runs on every admin_init so WordPress is always aware of
	 * which options this plugin owns, regardless of whether a Privacy Policy page
	 * has been configured yet.
	 *
	 * @return void
	 */
	public function admin_init() {
		$this->settings->register_settings();

		if ( is_mini_gdpr_enabled() ) {
			$this->admin_hooks = new Admin_Hooks( $this->name, $this->version );

			add_action( 'admin_enqueue_scripts', array( $this->admin_hooks, 'admin_enqueue_scripts' ), 10, 1 );

			add_filter( 'manage_users_columns', array( $this->admin_hooks, 'manage_users_columns' ), 10, 1 );
			add_filter( 'manage_users_custom_column', array( $this->admin_hooks, 'manage_users_custom_column' ), 10, 3 );

			$this->settings->maybe_save_settings();
		}
	}

	// -----------------------------------------------------------------------
	// Lazy-loaded sub-component accessors
	// -----------------------------------------------------------------------

	/**
	 * Returns (and lazily creates) the Script_Blocker instance.
	 *
	 * @return Script_Blocker
	 */
	public function get_script_blocker() {
		if ( is_null( $this->script_blocker ) ) {
			$this->script_blocker = new Script_Blocker( $this->name, $this->version );
		}

		return $this->script_blocker;
	}

	/**
	 * Returns (and lazily creates) the User_Controller instance.
	 *
	 * @return User_Controller
	 */
	public function get_user_controller() {
		if ( is_null( $this->user_controller ) ) {
			$this->user_controller = new User_Controller( $this->name, $this->version );
		}

		return $this->user_controller;
	}

	/**
	 * Returns (and lazily creates) the CF7_Helper instance.
	 *
	 * @return CF7_Helper
	 */
	public function get_cf7_helper() {
		if ( is_null( $this->cf7_helper ) ) {
			$this->cf7_helper = new CF7_Helper( $this->name, $this->version );
		}

		return $this->cf7_helper;
	}

	/**
	 * Returns the Settings controller.
	 *
	 * @return Settings
	 */
	public function get_settings_controller() {
		return $this->settings;
	}

	// -----------------------------------------------------------------------
	// WooCommerce callbacks
	// -----------------------------------------------------------------------

	/**
	 * Validates that the GDPR checkbox was accepted during WooCommerce registration.
	 *
	 * @param string   $username          The username.
	 * @param string   $email             The user email.
	 * @param \WP_Error $validation_errors WooCommerce validation errors object.
	 * @return void
	 */
	public function validate_registration( $username, $email, $validation_errors ) {
		$is_registration_validation_enabled = true;
		// Only enforce when a Terms & Conditions page is configured.
		if ( function_exists( 'wc_terms_and_conditions_page_id' ) ) {
			$tcs_and_cs_post_id                 = wc_terms_and_conditions_page_id();
			$is_registration_validation_enabled = $tcs_and_cs_post_id > 0;
		}
		$is_registration_validation_enabled = (bool) apply_filters( 'pp_mwg_enable_gdpr_registration_validation', $is_registration_validation_enabled );

		if ( $is_registration_validation_enabled && ! is_gdpr_accepted_in_post_data() ) {
			$validation_errors->add( 'accept_gdpr_error', __( 'Privacy Policy not accepted for GDPR', 'mini-wp-gdpr' ) );
		}
	}

	/**
	 * Saves the new customer's GDPR acceptance status after WooCommerce registration.
	 *
	 * @param int $customer_id Newly created WooCommerce customer user ID.
	 * @return void
	 */
	public function save_new_customer_gdpr_status( $customer_id ) {
		// phpcs:disable Generic.CodeAnalysis.AssignmentInCondition, Squiz.PHP.DisallowMultipleAssignments -- Intentional SESE guard pattern.
		if ( ! is_gdpr_accepted_in_post_data() ) {
			error_log( __FUNCTION__ . ' : GDPR not accepted' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		} elseif ( empty( $customer_id ) ) {
			error_log( __FUNCTION__ . ' : customer_id is invalid' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		} elseif ( false === ( $user = get_userdata( $customer_id ) ) ) {
			error_log( __FUNCTION__ . ' : customer_id ' . $customer_id . ' not found' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		} elseif ( empty( ( $user_controller = $this->get_user_controller() ) ) ) {
			error_log( __FUNCTION__ . ' : Failed to create the user controller.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		} else {
			$user_controller->accept_gdpr_terms_now( $customer_id );
		}
		// phpcs:enable Generic.CodeAnalysis.AssignmentInCondition, Squiz.PHP.DisallowMultipleAssignments
	}

	/**
	 * Records GDPR acceptance when a WooCommerce order is placed.
	 *
	 * @param int       $order_id The WooCommerce order ID.
	 * @param \WC_Order $order    The WooCommerce order object.
	 * @return void
	 */
	public function woocommerce_new_order( $order_id, $order ) {
		$settings = $this->get_settings_controller();

		// phpcs:disable Generic.CodeAnalysis.EmptyStatement, Generic.CodeAnalysis.AssignmentInCondition, Squiz.PHP.DisallowMultipleAssignments -- Intentional SESE guard pattern.
		if ( ! $settings->get_bool( OPT_IS_NEW_ORDER_TCSANDCS_CONSENT_ENABLED ) ) {
			// Feature disabled in settings.
		} elseif ( empty( ( $user = $order->get_user() ) ) ) {
			// Guest order — no user to update.
		} elseif ( empty( ( $user_controller = $this->get_user_controller() ) ) ) {
			error_log( __FUNCTION__ . ' : Failed to create the user controller.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		} else {
			$user_controller->accept_gdpr_terms_now( $user->ID );
		}
		// phpcs:enable Generic.CodeAnalysis.EmptyStatement, Generic.CodeAnalysis.AssignmentInCondition, Squiz.PHP.DisallowMultipleAssignments
	}

	// -----------------------------------------------------------------------
	// Contact Form 7 callback
	// -----------------------------------------------------------------------

	/**
	 * Records GDPR acceptance after a Contact Form 7 email is sent.
	 *
	 * @param \WPCF7_ContactForm $contact_form The CF7 contact form object.
	 * @return void
	 */
	public function wpcf7_mail_sent( $contact_form ) {
		$cf7_tag_name = apply_filters( 'pp_mwg_your_email_tag_name', CF7_YOUR_EMAIL_TAG_NAME );

		// CF7 nonce verification is handled by CF7 itself before this hook fires.
		// phpcs:disable WordPress.Security.NonceVerification.Missing, Generic.CodeAnalysis.EmptyStatement, Generic.CodeAnalysis.AssignmentInCondition, Squiz.PHP.DisallowMultipleAssignments -- Nonce is CF7-verified; SESE guard pattern.
		if ( ! is_gdpr_accepted_in_post_data() ) {
			// GDPR not accepted — nothing to record.
		} elseif ( empty( $cf7_tag_name ) ) {
			// No email tag configured.
		} elseif ( ! array_key_exists( $cf7_tag_name, $_POST ) ) {
			// Email tag not present in POST data.
		} elseif ( empty( ( $user_email = sanitize_email( wp_unslash( $_POST[ $cf7_tag_name ] ) ) ) ) ) {
			// Email address could not be sanitised.
		} elseif ( empty( ( $user = get_user_by( 'email', $user_email ) ) ) ) {
			// No WordPress user found for this email.
		} elseif ( empty( ( $user_controller = $this->get_user_controller() ) ) ) {
			error_log( __FUNCTION__ . ' : Failed to create the user controller.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		} else {
			$user_controller->accept_gdpr_terms_now( $user->ID );
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing, Generic.CodeAnalysis.EmptyStatement, Generic.CodeAnalysis.AssignmentInCondition, Squiz.PHP.DisallowMultipleAssignments
	}

	// -----------------------------------------------------------------------
	// AJAX handlers
	// -----------------------------------------------------------------------

	/**
	 * AJAX handler: accept GDPR terms for the current logged-in user.
	 *
	 * @return void
	 */
	public function accept_via_ajax() {
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) ), ACCEPT_GDPR_ACTION ) ) {
			die();
		}

		if ( ! is_user_logged_in() ) {
			die();
		}

		if ( ! pp_is_within_ajax_rate_limit( ACCEPT_GDPR_ACTION, RATE_LIMIT_CONSENT_MAX, RATE_LIMIT_CONSENT_WINDOW ) ) {
			wp_send_json( null, 429 );
		}

		if ( empty( ( $user_id = get_current_user_id() ) ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.Found, Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure -- Intentional SESE guard.
			error_log( __FUNCTION__ . ' : user_id is invalid' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			die();
		}

		$response      = null;
		$response_code = 400;

		// phpcs:disable Generic.CodeAnalysis.EmptyStatement, Generic.CodeAnalysis.AssignmentInCondition, Squiz.PHP.DisallowMultipleAssignments -- Intentional SESE guard pattern.
		if ( ! is_gdpr_accepted_in_post_data() ) {
			// GDPR checkbox not ticked.
		} elseif ( empty( ( $user_controller = $this->get_user_controller() ) ) ) {
			error_log( __FUNCTION__ . ' : Failed to create the user controller.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		} else {
			$user_controller->accept_gdpr_terms_now( $user_id );

			/**
			 * Fires when a logged-in user accepts GDPR consent via the My Account AJAX form.
			 *
			 * @since 2.0.0
			 * @param int $user_id The WordPress user ID of the consenting user.
			 */
			do_action( 'mwg_consent_accepted', $user_id ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- mwg_ is the plugin's public API prefix.

			$response      = array(
				'success' => '1',
				'message' => get_thankyou_text(),
			);
			$response_code = 200;
		}
		// phpcs:enable Generic.CodeAnalysis.EmptyStatement, Generic.CodeAnalysis.AssignmentInCondition, Squiz.PHP.DisallowMultipleAssignments

		wp_send_json( $response, $response_code );
	}

	/**
	 * AJAX handler: record that the current logged-in user rejected cookie consent.
	 *
	 * Called by the cookie consent popup's rejectConsent() JS method when the
	 * user is logged in. Stores the rejection timestamp in user meta so the
	 * server-side state mirrors the client-side localStorage/cookie storage.
	 *
	 * Fires the 'mwg_consent_rejected' action so third-party integrations can
	 * react to rejection events (e.g. clear WooCommerce tracking, log analytics).
	 *
	 * @return void
	 */
	public function reject_via_ajax() {
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) ), REJECT_GDPR_ACTION ) ) {
			die();
		}

		if ( ! is_user_logged_in() ) {
			die();
		}

		if ( ! pp_is_within_ajax_rate_limit( REJECT_GDPR_ACTION, RATE_LIMIT_CONSENT_MAX, RATE_LIMIT_CONSENT_WINDOW ) ) {
			wp_send_json( null, 429 );
		}

		$response      = null;
		$response_code = 400;

		// phpcs:disable Generic.CodeAnalysis.EmptyStatement, Generic.CodeAnalysis.AssignmentInCondition, Squiz.PHP.DisallowMultipleAssignments -- Intentional SESE guard pattern.
		if ( empty( ( $user_id = get_current_user_id() ) ) ) {
			error_log( __FUNCTION__ . ' : user_id is invalid' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		} elseif ( empty( ( $user_controller = $this->get_user_controller() ) ) ) {
			error_log( __FUNCTION__ . ' : Failed to create the user controller.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		} else {
			$user_controller->reject_gdpr_terms_now( $user_id );

			/**
			 * Fires when a logged-in user rejects cookie consent via the popup.
			 *
			 * @since 2.0.0
			 * @param int $user_id The WordPress user ID of the rejecting user.
			 */
			do_action( 'mwg_consent_rejected', $user_id ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- mwg_ is the plugin's public API prefix.

			$response      = array( 'success' => '1' );
			$response_code = 200;
		}
		// phpcs:enable Generic.CodeAnalysis.EmptyStatement, Generic.CodeAnalysis.AssignmentInCondition, Squiz.PHP.DisallowMultipleAssignments

		wp_send_json( $response, $response_code );
	}

	/**
	 * AJAX handler: install a GDPR consent checkbox into a Contact Form 7 form.
	 *
	 * @return void
	 */
	public function install_cf7_form() {
		pp_die_if_bad_nonce_or_cap( INSTALL_CF7_CONSENT_ACTION, $this->settings->get_settings_cap() );

		$response      = null;
		$response_code = 400;

		$cf7_helper = get_cf7_helper();

		// Nonce verified above by pp_die_if_bad_nonce_or_cap().
		// phpcs:disable WordPress.Security.NonceVerification.Missing, Generic.CodeAnalysis.EmptyStatement, Generic.CodeAnalysis.AssignmentInCondition, Squiz.PHP.DisallowMultipleAssignments -- Nonce verified above; SESE guard pattern.
		if ( ! array_key_exists( 'formId', $_POST ) ) {
			// formId not supplied.
		} elseif ( ! $cf7_helper->is_a_cf7_form( $form_id = absint( wp_unslash( $_POST['formId'] ) ) ) ) {
			// Supplied formId does not correspond to a CF7 form.
		// phpcs:enable WordPress.Security.NonceVerification.Missing, Generic.CodeAnalysis.EmptyStatement, Generic.CodeAnalysis.AssignmentInCondition, Squiz.PHP.DisallowMultipleAssignments
		} else {
			try {
				$cf7_helper->install_consent_box( $form_id );

				$response_code = 200;
				$response      = array(
					'forms'  => $cf7_helper->get_form_metas(),
					'formId' => $form_id,
				);
			} catch ( \Exception $e ) {
				error_log( __FUNCTION__ . ' : ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}
		}

		if ( ! is_array( $response ) ) {
			$response = array();
		}

		wp_send_json( $response, $response_code );
	}

	/**
	 * AJAX handler: reset all user GDPR consent records.
	 *
	 * Requires administrator capability.
	 *
	 * @return void
	 */
	public function reset_all_privacy_consents() {
		pp_die_if_bad_nonce_or_cap( RESET_PRIVACY_POLICY_CONSENTS, 'administrator' );

		if ( ! pp_is_within_ajax_rate_limit( RESET_PRIVACY_POLICY_CONSENTS, RATE_LIMIT_RESET_MAX, RATE_LIMIT_RESET_WINDOW ) ) {
			wp_send_json( null, 429 );
		}

		$response      = null;
		$response_code = 400;

		try {
			$user_controller = $this->get_user_controller();
			$users           = get_users();
			foreach ( $users as $user ) {
				$user_controller->clear_gdpr_accepted_status( $user->ID );
			}

			$response_code = 200;
			$response      = array(
				'message' => __( 'All user consents have been reset', 'mini-wp-gdpr' ),
			);
		} catch ( \Exception $e ) {
			error_log( $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}

		wp_send_json( $response, $response_code );
	}
}
