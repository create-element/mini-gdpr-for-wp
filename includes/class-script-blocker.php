<?php

namespace Mini_Wp_Gdpr;

defined('ABSPATH') || die();

class Script_Blocker extends Component
{
    public function __construct(string $name, string $version)
    {
        parent::__construct($name, $version);

        $this->blocked_scripts = [];
    }

    private $blocked_scripts;

    private $blockable_script_handles;

    private $blockable_scripts;

    public function get_blockable_scripts()
    {
        if (!is_array($this->blockable_scripts)) {
            do_action('mwg_init_blockable_scripts');

            $this->blockable_script_handles = apply_filters('mwg_blockable_script_handles', [GA_SCRIPT_HANDLE, FB_PIXEL_SCRIPT_HANDLE]);

            if (!is_array($this->blockable_script_handles)) {
                $this->blockable_script_handles = [];
            }

            $this->blockable_script_handles = array_filter(array_unique($this->blockable_script_handles));

            foreach ($this->blockable_script_handles as $blockable_script_handle) {
                // error_log($blockable_script_handle);

                if (empty(($sanitised_handle = sanitize_title($blockable_script_handle)))) {
                    // ...
                } else {
                    $defaults = [
                        'pattern' => '',
                        'field' => 'src',
                        'description' => '',
                        'html' => '',
                        'after' => '',
                        'can-defer' => true
                    ];

                    $definition = apply_filters('mwg_tracker_' . $sanitised_handle, $defaults);
                    $definition = wp_parse_args($definition, $defaults);

                    if (!empty($definition['pattern']) && !empty($definition['description'])) {
                        $definition['is-captured'] = false;

                        $this->blockable_scripts[$sanitised_handle] = $definition;
                    }
                }
            }
        }

        if (!is_array($this->blockable_scripts)) {
            $this->blockable_scripts = [];
        }

        return $this->blockable_scripts;
    }

    private $is_block_until_consent_enabled;
    private $are_trackers_blocked_by_role;

    public function capture_blocked_script_handles()
    {
        $settings = get_settings_controller();
        $is_always_show_enabled = $settings->get_bool(OPT_SHOW_CONSENT_POPUP_EVEN_IF_NO_SCRIPTS_FOUND);

        $blockable_scripts = $this->get_blockable_scripts();

        $this->are_trackers_blocked_by_role = false;
        if (!is_user_logged_in()) {
            // ...
        } elseif ($settings->get_bool(OPT_IS_ADMIN_TRACKING_ENABLED)) {
            // ...
        } elseif (empty(($user = wp_get_current_user()))) {
            // ...
        } elseif (empty(($current_user_roles = $user->roles))) {
            // ...
        } elseif (empty(($dont_track_roles = array_filter(apply_filters('mwg_dont_track_roles', DEFAULT_DONT_TRACK_ADMIN_ROLES))))) {
            // ...
        } else {
            $this->are_trackers_blocked_by_role = array_intersect($current_user_roles, $dont_track_roles);
        }

        $additional_blocked_scripts = apply_filters('mwg_additional_blocked_scripts', []);

        $scripts = wp_scripts();
        foreach ($scripts->registered as $script) {
            if (empty($script->handle)) {
                // ...
            } else {
                foreach ($blockable_scripts as $blockable_script_handle => $blockable_script) {
                    // error_log($script->handle);

                    $data = '';

                    switch ($blockable_script['field']) {
                        case 'outerhtml':
                            if (is_array($script->extra) && array_key_exists('after', $script->extra) && is_array($script->extra['after'])) {
                                foreach ($script->extra['after'] as $inline_snippet) {
                                    if (is_string($inline_snippet)) {
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

                    if (empty($data)) {
                        // ...
                    } elseif (empty($blockable_script['pattern'])) {
                        // ...
                    } elseif (!preg_match($blockable_script['pattern'], $data)) {
                        // ...
                    } else {
                        // error_log('Captured: ' . $script->handle);
                        $is_captured = true;

                        $blocked_script = $blockable_script;

                        // WP_Scripts::print_inline_script() is deprecated as of WordPress 6.3.0
                        // $blocked_script['extra'] = $scripts->print_inline_script($script->handle, 'extra', false);
                        // $blocked_script['after'] = $scripts->print_inline_script($script->handle, 'after', false);
                        $blocked_script['extra'] = $scripts->get_inline_script_data($script->handle, 'extra');
                        $blocked_script['after'] = $scripts->get_inline_script_data($script->handle, 'after');

                        $blocked_script['src'] = $script->src;
                        $blocked_script['is-captured'] = true;

                        $this->blocked_scripts[$script->handle] = $blocked_script; //s[$script->handle];

                        break;
                    }
                }
            }
        }

        if (count($this->blocked_scripts) > 0 || $is_always_show_enabled) {
            $class_names = ['mgw-cnt', 'mgw-box'];
            $class_names = array_merge($class_names, get_consent_box_styles());
            $class_names = array_filter(apply_filters('mwg_consent_box_classes', $class_names));

            // TODO: Consider incrementing this when the Privacy Policy post is saved/updated.
            $cookie_sequence = 0;
            $cookie_name = sprintf('%s_%d_', COOKIE_NAME_BASE, $cookie_sequence);

            if (($consent_duration = $settings->get_int(OPT_SCRIPT_CONSENT_DURATION, DEFAULT_SCRIPT_CONSENT_DURATION)) <= 0) {
                $consent_duration = DEFAULT_SCRIPT_CONSENT_DURATION;
            }

            $this->is_block_until_consent_enabled = $settings->get_bool(OPT_BLOCK_SCRIPTS_UNTIL_USER_CONSENTS, false);

            $info_text_3 = '';
            if ($this->are_trackers_blocked_by_role) {
                $info_text_3 = __('Tracking scripts are blocked because you\'re logged-in as an administrator', 'mini-wp-gdpr');
            }

            $consent_message = esc_html($settings->get_string(OPT_COOKIE_AND_TRACKER_CONSENT_MESSAGE));

            // Enqueue frontend.
            wp_enqueue_script('mini-gdpr-cookie-consent', PP_MWG_ASSETS_URL . 'mini-gdpr-cookie-popup.js', null, $this->version);
            wp_localize_script('mini-gdpr-cookie-consent', 'mgwcsData', [
                'cn' => $cookie_name,
                'cd' => $consent_duration,
                'msg' => $consent_message,
                'cls' => $class_names,
                'ok' => __('Accept', 'mini-wp-gdpr'),
                'mre' => __('info...', 'mini-wp-gdpr'),
                'nfo1' => __('Along with some cookies, we use these scripts', 'mini-wp-gdpr'),
                'nfo2' => __('We don\'t use any tracking scripts, but we do use some cookies.', 'mini-wp-gdpr'),
                'nfo3' => $info_text_3,
                'meta' => $this->blocked_scripts,
                'always' => $is_always_show_enabled ? 1 : 0,
                'blkon' => $this->is_block_until_consent_enabled ? 1 : 0
            ]);

            wp_enqueue_style('mini-gdpr-cookie-consent', PP_MWG_ASSETS_URL . 'mini-gdpr-cookie-popup.css', null, $this->version);
        }
    }

    public function script_loader_tag($tag, $handle, $src)
    {
        if (!$this->is_block_until_consent_enabled && !$this->are_trackers_blocked_by_role) {
            // ...
        } elseif (empty($handle)) {
            // ...
        } elseif (!array_key_exists($handle, $this->blocked_scripts)) {
            // ...
        } elseif (!$this->blocked_scripts[$handle]['can-defer']) {
            // ...
        } else {
            $tag = null;
        }

        return $tag;
    }
}
