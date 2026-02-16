<?php

namespace Mini_Wp_Gdpr;

defined('ABSPATH') || die();

class User_Controller extends Component
{
    private $now;

    public function __construct(string $name, string $version)
    {
        parent::__construct($name, $version);

        $this->now = get_date_time_now();
    }

    public function accept_gdpr_terms_now(int $user_id = 0)
    {
        if ($user_id <= 0) {
            $user_id = get_current_user_id();
        }

        if (!empty(($now_h = get_date_time_now_h()))) {
            if (empty(get_user_meta($user_id, META_ACCEPTED_GDPR_WHEN_FIRST, true))) {
                update_user_meta($user_id, META_ACCEPTED_GDPR_WHEN_FIRST, $now_h);
            }

            update_user_meta($user_id, META_ACCEPTED_GDPR_WHEN_RECENT, $now_h);
        }
    }

    public function clear_gdpr_accepted_status(int $user_id = 0)
    {
        if ($user_id <= 0) {
            $user_id = get_current_user_id();
        }

        delete_user_meta($user_id, META_ACCEPTED_GDPR_WHEN_FIRST);
        delete_user_meta($user_id, META_ACCEPTED_GDPR_WHEN_RECENT);
    }

    public function has_user_accepted_gdpr(int $user_id)
    {
        return !empty($this->when_did_user_accept_gdpr($user_id));
    }

    public function when_did_user_accept_gdpr(int $user_id, string $format = '')
    {
        $when = null;

        if (empty($format)) {
            $format = 'Y-m-d H:i:s T';
        }

        if ($user_id <= 0) {
            // ...
        } elseif (empty(($when_raw = get_user_meta($user_id, META_ACCEPTED_GDPR_WHEN_RECENT, true)))) {
            // ...
        } else {
            try {
                $when_datetime = new \DateTime($when_raw);
                if (intval($when_datetime->format('Y')) >= EARLIEST_GDPR_YEAR) {
                    $when = $when_datetime->format($format);
                }
            } catch (\Exception $e) {
                error_log(__FUNCTION__ . ' : Bad date: ' . $when_raw);
                $when = null;
            }
        }

        return $when;
    }
}
