<?php

namespace Mini_Wp_Gdpr;

defined('ABSPATH') || die();

class Public_Hooks extends Component
{
    private $admin_hooks;
    private $public_hooks;

    public function __construct(string $name, string $version)
    {
        parent::__construct($name, $version);
    }

    public function inject_configured_trackers()
    {
        $script_blocker = get_script_blocker();
        $blockable_scripts = $script_blocker->get_blockable_scripts();

        $blockable_scripts = (array) apply_filters('mwg_injectable_tracker_metas', $blockable_scripts);

        foreach ($blockable_scripts as $handle => $blockable_script) {
            $is_enabled = (bool) apply_filters('mwg_is_tracker_enabled', true, $handle);

            if ($is_enabled) {
                do_action('mwg_inject_tracker_' . $handle);
            }
        }
    }

    public function adjust_injected_tracker_tags($tag, $handle, $src)
    {
        if ($handle == GA_SCRIPT_HANDLE && !empty($src) && !empty($tag)) {
            $settings = get_settings_controller();

            // The code has already been validated when the placeholder was injected.
            $tracker_code = $settings->get_string(OPT_GA_TRACKING_CODE);

            $tag = sprintf(
                '<script src="https://www.googletagmanager.com/gtag/js?id=%s" id="%s-js" async></script>
<script id="%s-js-after">
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag("js", new Date());
gtag("config", "%s");
</script>
',
                $tracker_code,
                $handle,
                $handle,
                $tracker_code
            );
        }

        return $tag;
    }

    public function add_to_woocommerce_form()
    {
        enqueue_frontend_assets();

        $control_name = ACCEPT_GDPR_FORM_CONTROL_NAME;

        echo '<p class="form-row form-row-mini-gdpr">';
        echo get_accept_gdpr_checkbox_outer_html();
        echo '</p>';
    }

    public function inject_into_wc_myaccount_endpoint()
    {
        // echo get_accept_gdpr_checkbox_outer_html();
        echo mwg_get_mini_accept_terms_form_for_current_user();
    }
}
