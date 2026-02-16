<?php

namespace Mini_Wp_Gdpr;

defined('ABSPATH') || die();

function get_user_controller()
{
    global $pp_mwg_plugin;
    return $pp_mwg_plugin->get_user_controller();
}

function get_settings_controller()
{
    global $pp_mwg_plugin;
    return $pp_mwg_plugin->get_settings_controller();
}

function get_script_blocker()
{
    global $pp_mwg_plugin;
    return $pp_mwg_plugin->get_script_blocker();
}

function get_cf7_helper()
{
    global $pp_mwg_plugin;
    return $pp_mwg_plugin->get_cf7_helper();
}

function is_cf7_installed()
{
    $cf7_helper = get_cf7_helper();
    return $cf7_helper->is_cf7_installed();
}

function get_date_time_now()
{
    global $mini_gdrp_now;

    if (is_null($mini_gdrp_now)) {
        $mini_gdrp_now = new \DateTime('now', wp_timezone());
    }

    return $mini_gdrp_now;
}

function get_date_time_now_h()
{
    global $mini_gdrp_now_h;

    if (!is_null($mini_gdrp_now_h)) {
        // ...
    } elseif (empty(($now = get_date_time_now()))) {
        // ...
    } else {
        $mini_gdrp_now_h = $now->format('Y-m-d H:i:s T');
    }

    return $mini_gdrp_now_h;
}

function is_mini_gdpr_enabled()
{
    global $is_mini_gdpr_enabled;

    if (is_null($is_mini_gdpr_enabled)) {
        $is_mini_gdpr_enabled = false;
    }

    if (empty(get_privacy_policy_url())) {
        // ...
    } else {
        $is_mini_gdpr_enabled = true;
    }

    return $is_mini_gdpr_enabled;
}

function enqueue_frontend_assets()
{
    global $is_mini_gdpr_frontend_enqueued;

    if (is_null($is_mini_gdpr_frontend_enqueued)) {
        wp_enqueue_style('mini-wp-gdpr', PP_MWG_ASSETS_URL . 'mini-gdpr.css', null, PP_MWG_VERSION, $media = 'all');

        wp_enqueue_script('mini-wp-gdpr', PP_MWG_ASSETS_URL . 'mini-gdpr.js', 'jquery', PP_MWG_VERSION, true);

        $params = [
            'termsNotAccepted' => __('Please accept the GDPR terms before proceeding.', 'mini-wp-gdpr'),
            'miniFormPlease' => get_accept_gdpr_checkbox_text()
        ];

        if (is_user_logged_in()) {
            $params['acceptAction'] = ACCEPT_GDPR_ACTION;
            $params['acceptNonce'] = wp_create_nonce(ACCEPT_GDPR_ACTION);
            $params['ajaxUrl'] = admin_url('admin-ajax.php');
        }

        wp_localize_script('mini-wp-gdpr', 'miniWpGdpr', $params);

        $is_mini_gdpr_enabled = true;
    }
}

function get_thankyou_text()
{
    return __('Thanks. That\'s the official GDPR stuff sorted.', 'mini-wp-gdpr');
}

function get_accept_gdpr_checkbox_outer_html()
{
    $control_name = ACCEPT_GDPR_FORM_CONTROL_NAME;

    $props = '';
    if (mwg_has_user_accepted_privacy_policy()) {
        $props .= ' checked';
    }

    $html = '<label class="checkbox">';
    $html .= sprintf(
        '<input class="input-checkbox mini-gdpr-checkbox" name="%s" id="%s" type="checkbox" value="1" %s /><span>%s</span>',
        esc_attr($control_name),
        esc_attr($control_name),
        $props,
        get_accept_gdpr_checkbox_text()
    );
    $html .= '</label>'; // .woocommerce-form__label woocommerce-form__label-for-checkbox

    return $html;
}

function get_accept_gdpr_checkbox_text()
{
    return sprintf(
        __('I agree to the handling of my personal data by %s, as per the <a href="%s">Privacy Policy</a>', 'mini-wp-gdpr'),
        esc_html(get_bloginfo('name')),
        esc_url(get_privacy_policy_url())
    );
}

function is_gdpr_accepted_in_post_data()
{
    $control_names = [ACCEPT_GDPR_FORM_CONTROL_NAME, 'terms'];

    $is_gdpr_accepted = false;

    foreach ($control_names as $control_name) {
        if (array_key_exists($control_name, $_POST) && filter_var($_POST[$control_name], FILTER_VALIDATE_BOOLEAN)) {
            $is_gdpr_accepted = true;
            break;
        }
    }

    // Check for CF7
    if ($is_gdpr_accepted) {
        // ...
    } elseif (!array_key_exists(CF7_CONSENT_TAG_NAME, $_POST)) {
        // ...
    } elseif (!is_array($_POST[CF7_CONSENT_TAG_NAME])) {
        // ...
    } else {
        $is_gdpr_accepted = count($_POST[CF7_CONSENT_TAG_NAME]) == 1;
    }

    return $is_gdpr_accepted;
}

function get_all_script_block_domains()
{
    return array_unique(array_merge(get_script_block_lists_blacklist(), get_script_block_lists_whitelist()));
}

function get_script_block_regex_from_domain(string $domain)
{
    return sprintf('/%s/', str_replace('.', '\\.', $domain));
}

function is_script_blocker_enabled()
{
    $is_enabled = false;

    $settings = get_plugin_controller();

    if (empty(get_script_block_lists_blacklist()) && empty(get_script_block_lists_whitelist())) {
        // ...
    } elseif (!$settings->get_bool(OPT_IS_COOKIE_CONSENT_POPUP_ENABLED)) {
        // ...
    } else {
        $is_enabled = true;
    }

    return $is_enabled;
}

function get_consent_box_positions()
{
    return [
        0 => __('Bottom Left', 'mini-wp-gdpr'),
        1 => __('Bottom Centre', 'mini-wp-gdpr'),
        2 => __('Bottom Right', 'mini-wp-gdpr'),
        3 => __('Middle Left', 'mini-wp-gdpr'),
        4 => __('Middle Centre', 'mini-wp-gdpr'),
        5 => __('Middle Right', 'mini-wp-gdpr'),
        6 => __('Top Left', 'mini-wp-gdpr'),
        7 => __('Top Centre', 'mini-wp-gdpr'),
        8 => __('Top Right', 'mini-wp-gdpr')
    ];
}

function get_consent_box_styles(int $position = -1)
{
    $styles = null;

    if ($position < 0) {
        $settings = get_settings_controller();
        $position = $settings->get_int(OPT_CONSENT_BOX_POSITION, DEFAULT_CONSENT_BOX_POSITION);
    }

    switch ($position) {
        case 6:
            $styles = ['mgw-lft', 'mgw-top'];
            break;

        case 7:
            $styles = ['mgw-hcn', 'mgw-top'];
            break;

        case 8:
            $styles = ['mgw-rgt', 'mgw-top'];
            break;

        case 3:
            $styles = ['mgw-lft', 'mgw-vcn'];
            break;

        case 4:
            $styles = ['mgw-hcn', 'mgw-vcn'];
            break;

        case 5:
            $styles = ['mgw-rgt', 'mgw-vcn'];
            break;

        case 0:
            $styles = ['mgw-lft', 'mgw-btm'];
            break;

        case 1:
            $styles = ['mgw-hcn', 'mgw-btm'];
            break;

        case 2:
            $styles = ['mgw-rgt', 'mgw-btm'];
            break;
    }

    if (!is_array($styles)) {
        $styles = ['mgw-rgt', 'mgw-vcn'];
    }

    return $styles;
}

function is_external_ga_injector_plugin_installed()
{
    $is_installed = false;

    if (!function_exists('is_plugin_active')) {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    $plugins = EXTERNAL_GA_TRACKER_PLUGINS;
    foreach ($plugins as $plugin) {
        if (is_plugin_active($plugin)) {
            $is_installed = true;
            break;
        }
    }

    return $is_installed;
}
