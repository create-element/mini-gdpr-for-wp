<?php

defined('ABSPATH') || die();

function mwg_has_user_accepted_privacy_policy(int $user_id = 0)
{
    if ($user_id <= 0) {
        $user_id = get_current_user_id();
    }

    $user_controller = Mini_Wp_Gdpr\get_user_controller();
    return $user_controller->has_user_accepted_gdpr($user_id);
}

function mwg_when_did_user_accept_privacy_policy(int $user_id = 0, string $format = '')
{
    if ($user_id <= 0) {
        $user_id = get_current_user_id();
    }

    if (empty($format)) {
        $format = get_option('date_format', '');
    }

    $user_controller = Mini_Wp_Gdpr\get_user_controller();
    return $user_controller->when_did_user_accept_gdpr($user_id, $format);
}

function mwg_get_mini_accept_terms_form_for_current_user()
{
    include PP_MWG_PUBLIC_TEMPLATES_DIR . 'mini-accept-form.php';
}

// function mwg_is_admin_tracking_enabled() {
// 	$settings = Mini_Wp_Gdpr\get_settings_controller();
// 	return $settings->get_bool(OPT_IS_ADMIN_TRACKING_ENABLED);
// }
