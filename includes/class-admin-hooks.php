<?php

namespace Mini_Wp_Gdpr;

defined('ABSPATH') || die();

class Admin_Hooks extends Component
{
    private $admin_hooks;
    private $public_hooks;

    public function __construct(string $name, string $version)
    {
        parent::__construct($name, $version);
    }

    public function admin_enqueue_scripts($current_page)
    {
        $are_assets_required = false;

        $settings = get_settings_controller();

        if (current_user_can($settings->get_settings_cap())) {
            // $are_assets_required |= ($current_page == 'settings_page_' . SETTINGS_PAGE_NAME);
            $are_assets_required |= $current_page == 'settings_page_' . $settings->get_settings_page_name();
            // More checkes.
        }

        if ($are_assets_required) {
            pp_enqueue_admin_assets();

            wp_enqueue_script($this->name, PP_MWG_ASSETS_URL . 'mini-gdpr-admin.js', ['jquery'], $this->version, false);

            if (is_cf7_installed()) {
                wp_enqueue_script($this->name . '-cf7', PP_MWG_ASSETS_URL . 'mini-gdpr-admin-cf7.js', ['jquery'], $this->version, false);
            }
        }
    }

    public function manage_users_columns($columns)
    {
        $columns['gdpr-status'] = __('Privacy Consent', 'mini-wp-gdpr');

        return $columns;
    }

    public function manage_users_custom_column($val, $column_name, $user_id)
    {
        switch ($column_name) {
            case 'gdpr-status':
                $user_controller = get_user_controller();

                if (!empty(($when = $user_controller->when_did_user_accept_gdpr($user_id, get_option('date_format', 'Y-m-d H:i:s'))))) {
                    $val = sprintf('<div class="user-gdpr user-gdpr-when-accepted" title="%s">%s</div>', $user_controller->when_did_user_accept_gdpr($user_id), $when);
                }
                break;

            default:
                break;
        }

        return $val;
    }
}
