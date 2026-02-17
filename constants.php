<?php

namespace Mini_Wp_Gdpr;

defined( 'ABSPATH' ) || die();

const META_ACCEPTED_GDPR_WHEN_FIRST  = '_pwg_accepted_gdpr_when_first';
const META_ACCEPTED_GDPR_WHEN_RECENT = '_pwg_accepted_gdpr_when_recent';
const META_REJECTED_GDPR_WHEN        = '_pwg_rejected_gdpr_when';
const ACCEPT_GDPR_FORM_CONTROL_NAME  = 'confirm-accept-gdpr';

const EARLIEST_GDPR_YEAR         = 2017;
const ACCEPT_GDPR_ACTION         = 'acceptgdpr';
const REJECT_GDPR_ACTION         = 'rejectgdpr';
const INSTALL_CF7_CONSENT_ACTION = 'mwginstcf7';

const IS_RESET_ALL_CONSENT_ENABLED  = true; //false; //true;
const RESET_PRIVACY_POLICY_CONSENTS = 'resetuserprivacyconsents';

// const SETTINGS_PAGE_NAME = 'minigdpr';
// const SETTINGS_CAP = 'manage_options';
// const SAVE_SETTINGS_ACTION = 'save_minigdpr_settings';
// const SAVE_SETTINGS_NONCE = 'save_minigdpr_nnc';

const OPT_IS_WC_MYACCOUNT_INJECT_ENABLED = 'mwg_is_wc_account_inject_enabled';
const OPT_WHICH_WC_MYACCOUNT_ENDPOINT    = 'mwg_which_account_endpoint';
const DEFAULT_MYACCOUNT_INJECT_PRIORITY  = 5;

const OPT_IS_COOKIE_CONSENT_POPUP_ENABLED             = 'mwg_is_cookie_consent_popup_enabled';
const OPT_SHOW_CONSENT_POPUP_EVEN_IF_NO_SCRIPTS_FOUND = 'mwg_always_show_consent';

const OPT_IS_NEW_ORDER_TCSANDCS_CONSENT_ENABLED = 'mwg_consent_on_new_wc_order';

const OPT_IS_GA_TRACKING_ENABLED = 'mwg_is_ga_enabled';
const OPT_GA_TRACKING_CODE       = 'mwg_ga_tracking_code';
const GA_SCRIPT_HANDLE           = 'mgw-google-analytics';

const OPT_IS_FB_PIXEL_TRACKING_ENABLED = 'mwg_is_fbpx_enabled';
const OPT_IS_FB_PIXEL_NOSCRIPT_ENABLED = 'mwg_is_fbpx_noscript_enabled';
const OPT_FB_PIXEL_ID                  = 'mwg_fbpx_id';
const FB_PIXEL_SCRIPT_HANDLE           = 'mgw-facebook-pixel';

const OPT_IS_ADMIN_TRACKING_ENABLED  = 'mwg_is_admin_tracking_enabled';
const DEFAULT_DONT_TRACK_ADMIN_ROLES = [ 'administrator' ];

const COOKIE_NAME_BASE                = 'mgwcs';
const OPT_SCRIPT_CONSENT_DURATION     = 'mwg_consent_duration';
const DEFAULT_SCRIPT_CONSENT_DURATION = 365; //3;

const OPT_CONSENT_BOX_POSITION     = 'mwg_consent_box_position';
const DEFAULT_CONSENT_BOX_POSITION = 1;

const OPT_BLOCK_SCRIPTS_UNTIL_USER_CONSENTS = 'mwg_block_trackers_until_consent';

const OPT_COOKIE_AND_TRACKER_CONSENT_MESSAGE = 'mwg_tracker_consent_message';

const OPT_CONSENT_ACCEPT_TEXT   = 'mwg_consent_accept_text';
const DEF_CONSENT_ACCEPT_TEXT   = 'Accept';
const OPT_CONSENT_REJECT_TEXT   = 'mwg_consent_reject_text';
const DEF_CONSENT_REJECT_TEXT   = 'Reject';
const OPT_CONSENT_INFO_BTN_TEXT = 'mwg_consent_info_btn_text';
const DEF_CONSENT_INFO_BTN_TEXT = 'info...';

const CF7_POST_TYPE           = 'wpcf7_contact_form';
const CF7_CONSENT_TAG_NAME    = 'checkbox-privacy';
const CF7_YOUR_EMAIL_TAG_NAME = 'your-email';

const OPT_GA_CONSENT_MODE_ENABLED = 'mwg_ga_consent_mode_enabled';

const EXTERNAL_GA_TRACKER_PLUGINS = [ 'woocommerce-google-analytics-integration/woocommerce-google-analytics-integration.php' ];

const MS_CLARITY_SCRIPT_HANDLE           = 'msft-clarity';
const OPT_MS_CLARITY_ID                  = 'mwg_msft_clarity_id';
const OPT_IS_MS_CLARITY_TRACKING_ENABLED = 'mwg_is_msft_clarity_enabled';
