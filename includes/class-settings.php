<?php

namespace Mini_Wp_Gdpr;

defined('ABSPATH') || die();

class Settings extends Settings_Core
{
    public function __construct(string $name, string $version)
    {
        parent::__construct($name, $version);

        add_action('admin_menu', [$this, 'initialise_admin_menu']);
    }

    public function initialise_admin_menu()
    {
        add_options_page(__('Mini WP GDPR', 'mini-wp-gdpr'), __('Mini WP GDPR', 'mini-wp-gdpr'), $this->settings_cap, $this->get_settings_page_name(), [
            $this,
            'render_settings_page'
        ]);
    }

    public function render_settings_page()
    {
        if (!current_user_can($this->settings_cap)) {
            printf('<p>%s</p>', esc_html__('Not authorized', 'mini-wp-gdpr'));
        } elseif (!is_mini_gdpr_enabled()) {
            printf(
                '<p>%s</p><p><a href="%s" class="button">%s</a></p>',
                esc_html__('You need to create a Privacy Policy page first.', 'mini-wp-gdpr'),
                esc_url(admin_url('options-privacy.php')),
                esc_html__('Create a Privacy Policy', 'mini-wp-gdpr')
            );
        } else {
            $this->open_wrap();

            printf('<h1>%s%s</h1>', esc_html(get_admin_page_title()), pp_get_header_logo_html('https://power-plugins.com/plugin/mini-wp-gdpr/'));

            $this->open_form();

            $settings = $this;

            include PP_MWG_ADMIN_TEMPLATES_DIR . 'cookie-consent-settings.php';

            if (is_woocommerce_available()) {
                echo '<hr />';
                include PP_MWG_ADMIN_TEMPLATES_DIR . 'woocommerce-settings.php';
            }

            if (is_cf7_installed()) {
                echo '<hr />';
                include PP_MWG_ADMIN_TEMPLATES_DIR . 'contact-form-7-settings.php';
            }

            echo '<hr />';
            include PP_MWG_ADMIN_TEMPLATES_DIR . 'trackers-settings.php';

            submit_button(esc_html__('Save Changes', 'mini-wp-gdpr'));

            $this->close_form();

            if (IS_RESET_ALL_CONSENT_ENABLED && current_user_can('administrator')) {
                echo '<hr />';

                printf(
                    '<p class="pp-form-row"><span class="dashicons dashicons-warning"></span> %s</p>',
                    esc_html__('This will reset all consent given by all the registered users of the site.', 'mini-wp-gdpr')
                );

                // printf(
                // 	'<p><button class="button" data-mwg-action="%s" data-mwg-nonce="%s" data-mwg-confirm="%s">%s</button><img src="%s" class="mwg-spinner" style="display: none"/></a>',
                // 	RESET_PRIVACY_POLICY_CONSENTS,
                // 	wp_create_nonce(RESET_PRIVACY_POLICY_CONSENTS),
                // 	esc_attr__('Really reset all user consents now?', 'mini-wp-gdpr'),
                // 	esc_html__('Reset Now', 'mini-wp-gdpr'),
                // 	esc_url(PP_MWG_ASSETS_URL . 'spinner.svg')
                // );
                $args = [
                    'action' => RESET_PRIVACY_POLICY_CONSENTS,
                    'nonce' => wp_create_nonce(RESET_PRIVACY_POLICY_CONSENTS),
                    'confirmMessage' => __('Really reset all user consents now?', 'mini-wp-gdpr')
                ];

                $props = sprintf('data-reset-all-consents="%s"', esc_attr(json_encode($args)));

                echo pp_get_button_with_spinner_html(__('Reset Now', 'mini-wp-gdpr'), '', $props);
            }

            $this->close_wrap();
        }
    }

    public function save_settings()
    {
        $this->set_bool(OPT_IS_WC_MYACCOUNT_INJECT_ENABLED, array_key_exists(OPT_IS_WC_MYACCOUNT_INJECT_ENABLED, $_POST));
        $this->set_bool(OPT_IS_COOKIE_CONSENT_POPUP_ENABLED, array_key_exists(OPT_IS_COOKIE_CONSENT_POPUP_ENABLED, $_POST));
        $this->set_bool(OPT_SHOW_CONSENT_POPUP_EVEN_IF_NO_SCRIPTS_FOUND, array_key_exists(OPT_SHOW_CONSENT_POPUP_EVEN_IF_NO_SCRIPTS_FOUND, $_POST));
        $this->set_bool(OPT_IS_GA_TRACKING_ENABLED, array_key_exists(OPT_IS_GA_TRACKING_ENABLED, $_POST));
        $this->set_bool(OPT_IS_ADMIN_TRACKING_ENABLED, array_key_exists(OPT_IS_ADMIN_TRACKING_ENABLED, $_POST));
        $this->set_bool(OPT_IS_NEW_ORDER_TCSANDCS_CONSENT_ENABLED, array_key_exists(OPT_IS_NEW_ORDER_TCSANDCS_CONSENT_ENABLED, $_POST));
        $this->set_bool(OPT_IS_FB_PIXEL_TRACKING_ENABLED, array_key_exists(OPT_IS_FB_PIXEL_TRACKING_ENABLED, $_POST));
        $this->set_bool(OPT_IS_FB_PIXEL_NOSCRIPT_ENABLED, array_key_exists(OPT_IS_FB_PIXEL_NOSCRIPT_ENABLED, $_POST));
        $this->set_bool(OPT_IS_MS_CLARITY_TRACKING_ENABLED, array_key_exists(OPT_IS_MS_CLARITY_TRACKING_ENABLED, $_POST));
        $this->set_bool(OPT_BLOCK_SCRIPTS_UNTIL_USER_CONSENTS, array_key_exists(OPT_BLOCK_SCRIPTS_UNTIL_USER_CONSENTS, $_POST));

        if (array_key_exists(OPT_WHICH_WC_MYACCOUNT_ENDPOINT, $_POST)) {
            $this->set_string(OPT_WHICH_WC_MYACCOUNT_ENDPOINT, sanitize_title(wp_unslash($_POST[OPT_WHICH_WC_MYACCOUNT_ENDPOINT])));
        }

        if (array_key_exists(OPT_GA_TRACKING_CODE, $_POST)) {
            $this->set_string(OPT_GA_TRACKING_CODE, sanitize_text_field(wp_unslash($_POST[OPT_GA_TRACKING_CODE])));
        } else {
            $this->set_string(OPT_GA_TRACKING_CODE, '');
        }

        if (array_key_exists(OPT_SCRIPT_CONSENT_DURATION, $_POST)) {
            $this->set_int(OPT_SCRIPT_CONSENT_DURATION, absint($_POST[OPT_SCRIPT_CONSENT_DURATION]));
        }

        if (array_key_exists(OPT_CONSENT_BOX_POSITION, $_POST)) {
            $this->set_int(OPT_CONSENT_BOX_POSITION, absint($_POST[OPT_CONSENT_BOX_POSITION]));
        }

        if (array_key_exists(OPT_COOKIE_AND_TRACKER_CONSENT_MESSAGE, $_POST)) {
            $this->set_string(OPT_COOKIE_AND_TRACKER_CONSENT_MESSAGE, wp_kses_post(wp_unslash($_POST[OPT_COOKIE_AND_TRACKER_CONSENT_MESSAGE])));
        } else {
            $this->set_string(OPT_COOKIE_AND_TRACKER_CONSENT_MESSAGE, '');
        }

        if (array_key_exists(OPT_FB_PIXEL_ID, $_POST)) {
            $this->set_string(OPT_FB_PIXEL_ID, wp_kses_post(wp_unslash($_POST[OPT_FB_PIXEL_ID])));
        } else {
            $this->set_string(OPT_FB_PIXEL_ID, '');
        }

        if (array_key_exists(OPT_MS_CLARITY_ID, $_POST)) {
            $this->set_string(OPT_MS_CLARITY_ID, wp_kses_post(wp_unslash($_POST[OPT_MS_CLARITY_ID])));
        } else {
            $this->set_string(OPT_MS_CLARITY_ID, '');
        }
    }

    public function get_default_value(string $option_name)
    {
        $value = null;

        switch ($option_name) {
            case OPT_COOKIE_AND_TRACKER_CONSENT_MESSAGE:
                $value = sprintf(__('%s uses cookies and analytics to create a better user experience. Are you OK with this?', 'mini-wp-gdpr'), get_bloginfo('name'));
                break;

            default:
                // ...
                break;
        }

        return $value;
    }

    public function sanitise_value(string $option_name, $value)
    {
        if ($option_name == OPT_IS_GA_TRACKING_ENABLED && is_external_ga_injector_plugin_installed()) {
            $value = false;
        }

        return $value;
    }
}
