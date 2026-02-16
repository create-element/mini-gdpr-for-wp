<?php

namespace Mini_Wp_Gdpr;

defined('ABSPATH') || die();

class Plugin extends Component
{
    private $admin_hooks;
    private $public_hooks;

    public function __construct(string $name, string $version)
    {
        parent::__construct($name, $version);
    }

    public function run()
    {
        add_action('plugins_loaded', [$this, 'load_plugin_textdomain']);

        add_action('init', [$this, 'init']);
        add_action('admin_init', [$this, 'admin_init']);

        $this->settings = new Settings($this->name, $this->version);
        add_action('admin_menu', [$this->settings, 'initialise_admin_menu']);
    }

    public function load_plugin_textdomain()
    {
        load_plugin_textdomain($this->name, false, $this->name . '/languages');
    }

    public function init()
    {
        if (is_mini_gdpr_enabled()) {
            $this->public_hooks = new Public_Hooks($this->name, $this->version);

            if (!is_admin()) {
                add_action('wp_enqueue_scripts', [$this->public_hooks, 'inject_configured_trackers']);
                add_filter('script_loader_tag', [$this->public_hooks, 'adjust_injected_tracker_tags'], 90, 3);

                $script_blocker = $this->get_script_blocker();
                if ($this->settings->get_bool(OPT_IS_COOKIE_CONSENT_POPUP_ENABLED)) {
                    add_action('wp_enqueue_scripts', [$script_blocker, 'capture_blocked_script_handles'], 99);
                    add_filter('script_loader_tag', [$script_blocker, 'script_loader_tag'], 99, 3);
                }
            }

            add_action('wpcf7_mail_sent', [$this, 'wpcf7_mail_sent'], 10, 1);

            add_action('woocommerce_register_form', [$this->public_hooks, 'add_to_woocommerce_form'], 30);
            add_action('woocommerce_register_post', [$this, 'validate_registration'], 10, 3);
            add_action('woocommerce_created_customer', [$this, 'save_new_customer_gdpr_status'], 10, 2);
            add_action('woocommerce_new_order', [$this, 'woocommerce_new_order'], 10, 2);

            add_action('wp_ajax_' . ACCEPT_GDPR_ACTION, [$this, 'accept_via_ajax']);
            add_action('wp_ajax_' . INSTALL_CF7_CONSENT_ACTION, [$this, 'install_cf7_form']);
            add_action('wp_ajax_' . RESET_PRIVACY_POLICY_CONSENTS, [$this, 'reset_all_privacy_consents']);

            if (is_admin() || wp_doing_ajax()) {
                // ...
            } elseif (!$this->settings->get_bool(OPT_IS_WC_MYACCOUNT_INJECT_ENABLED)) {
                // ...
            } elseif (empty(($endpoint = sanitize_title($this->settings->get_string(OPT_WHICH_WC_MYACCOUNT_ENDPOINT))))) {
                // ...
            } elseif (mwg_has_user_accepted_privacy_policy()) {
                // ...
            } else {
                $priority = intval(apply_filters('mwg_myaccount_priority', DEFAULT_MYACCOUNT_INJECT_PRIORITY));
                if ($endpoint == 'dashboard') {
                    $action = 'woocommerce_account_' . $endpoint;
                } else {
                    $action = 'woocommerce_account_' . $endpoint . '_endpoint';
                }

                add_action($action, [$this->public_hooks, 'inject_into_wc_myaccount_endpoint'], $priority);
            }
        }
    }

    public function admin_init()
    {
        if (is_mini_gdpr_enabled()) {
            $this->admin_hooks = new Admin_Hooks($this->name, $this->version);

            add_action('admin_enqueue_scripts', [$this->admin_hooks, 'admin_enqueue_scripts'], 10, 1);

            add_filter('manage_users_columns', [$this->admin_hooks, 'manage_users_columns'], 10, 1);
            add_filter('manage_users_custom_column', [$this->admin_hooks, 'manage_users_custom_column'], 10, 3);

            // Save settings?
            $this->settings->maybe_save_settings();
        }
    }

    private $script_blocker;
    public function get_script_blocker()
    {
        if (is_null($this->script_blocker)) {
            $this->script_blocker = new Script_Blocker($this->name, $this->version);
        }

        return $this->script_blocker;
    }

    private $user_controller;
    public function get_user_controller()
    {
        if (is_null($this->user_controller)) {
            $this->user_controller = new User_Controller($this->name, $this->version);
        }

        return $this->user_controller;
    }

    private $cf7_helper;
    public function get_cf7_helper()
    {
        if (is_null($this->cf7_helper)) {
            $this->cf7_helper = new CF7_Helper($this->name, $this->version);
        }

        return $this->cf7_helper;
    }

    private $settings;
    public function get_settings_controller()
    {
        return $this->settings;
    }

    public function validate_registration($username, $email, $validation_errors)
    {
        $is_registration_validation_enabled = true;
        // 20231107 PF: Only check for registration validation if the Ts&Cs
        // page is actually available.
        if (function_exists('wc_terms_and_conditions_page_id')) {
            $tcs_and_cs_post_id = wc_terms_and_conditions_page_id();
            $is_registration_validation_enabled = $tcs_and_cs_post_id > 0;
        }
        $is_registration_validation_enabled = (bool) apply_filters('enable_gdpr_registration_validation', $is_registration_validation_enabled);

        if ($is_registration_validation_enabled && !is_gdpr_accepted_in_post_data()) {
            $validation_errors->add('accept_gdpr_error', __('Privacy Policy not accepted for GDPR', 'mini-wp-gdpr'));
        }
    }

    public function save_new_customer_gdpr_status($customer_id)
    {
        if (!is_gdpr_accepted_in_post_data()) {
            error_log(__FUNCTION__ . ' : GDPR not accepted');
        } elseif (empty($customer_id)) {
            error_log(__FUNCTION__ . ' : customer_id is invalid');
        } elseif (($user = get_userdata($customer_id)) === false) {
            error_log(__FUNCTION__ . ' : customer_id ' . $customer_id . ' not found');
        } elseif (empty(($user_controller = $this->get_user_controller()))) {
            error_log(__FUNCTION__ . ' : Failed to create the user controller.');
        } else {
            $user_controller->accept_gdpr_terms_now($customer_id);
        }
    }

    public function wpcf7_mail_sent($contact_form)
    {
        $cf7_tag_name = apply_filters('mwg_your_email_tag_name', CF7_YOUR_EMAIL_TAG_NAME);

        if (!is_gdpr_accepted_in_post_data()) {
            // ...
        } elseif (empty($cf7_tag_name)) {
            // ...
        } elseif (!array_key_exists($cf7_tag_name, $_POST)) {
            // ...
        } elseif (empty(($user_email = sanitize_email($_POST[$cf7_tag_name])))) {
            // ...
        } elseif (empty(($user = get_user_by('email', $user_email)))) {
            // ...
        } elseif (empty(($user_controller = $this->get_user_controller()))) {
            error_log(__FUNCTION__ . ' : Failed to create the user controller.');
        } else {
            $user_controller->accept_gdpr_terms_now($user->ID);
        }
    }

    public function woocommerce_new_order($order_id, $order)
    {
        $settings = $this->get_settings_controller();

        if (!$settings->get_bool(OPT_IS_NEW_ORDER_TCSANDCS_CONSENT_ENABLED)) {
            // ...
        } elseif (empty(($user = $order->get_user()))) {
            // ...
        } elseif (empty(($user_controller = $this->get_user_controller()))) {
            error_log(__FUNCTION__ . ' : Failed to create the user controller.');
        } else {
            $user_controller->accept_gdpr_terms_now($user->ID);
        }
    }

    public function accept_via_ajax()
    {
        if (!wp_verify_nonce($_POST['nonce'], ACCEPT_GDPR_ACTION)) {
            die();
        }

        if (!is_user_logged_in()) {
            die();
        }

        if (empty(($user_id = get_current_user_id()))) {
            error_log(__FUNCTION__ . ' : user_id is invalid');
            die();
        }

        $response = null;
        $response_code = 400;

        if (!is_gdpr_accepted_in_post_data()) {
            // ...
        } elseif (empty(($user_controller = $this->get_user_controller()))) {
            error_log(__FUNCTION__ . ' : Failed to create the user controller.');
        } else {
            $user_controller->accept_gdpr_terms_now($user_id);

            $response = [
                'success' => '1',
                'message' => get_thankyou_text()
            ];
            $response_code = 200;
        }

        wp_send_json($response, $response_code);
    }

    public function install_cf7_form()
    {
        pp_die_if_bad_nonce_or_cap(INSTALL_CF7_CONSENT_ACTION, $this->settings->get_settings_cap());

        // if (!wp_verify_nonce($_POST['nonce'], INSTALL_CF7_CONSENT_ACTION)) {
        // 	die();
        // }

        // if (!current_user_can(SETTINGS_CAP)) {
        // 	die();
        // }

        $response = null;
        $response_code = 400;

        $cf7_helper = get_cf7_helper();

        if (!array_key_exists('formId', $_POST)) {
            // ...
        } elseif (!$cf7_helper->is_a_cf7_form($form_id = intval($_POST['formId']))) {
            // ...
        } else {
            try {
                $cf7_helper->install_consent_box($form_id);

                $response_code = 200;
                $response = [
                    'forms' => $cf7_helper->get_form_metas(),
                    'formId' => $form_id
                ];
            } catch (\Exception $e) {
                error_log(__FUNCTION__ . ' : ' . $e->getMessage());
            }
        }

        if (!is_array($response)) {
            $response = [];
        }

        wp_send_json($response, $response_code);
    }

    public function reset_all_privacy_consents()
    {
        pp_die_if_bad_nonce_or_cap(RESET_PRIVACY_POLICY_CONSENTS, 'administrator');

        $response = null;
        $response_code = 400;

        try {
            $user_controller = $this->get_user_controller();
            $users = get_users();
            foreach ($users as $user) {
                $user_controller->clear_gdpr_accepted_status($user->ID);
            }

            $response_code = 200;
            $response = [
                'message' => __('All user consents have been reset', 'mini-wp-gdpr')
            ];
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }

        wp_send_json($response, $response_code);
    }
}
