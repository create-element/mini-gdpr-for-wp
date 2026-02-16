<?php

namespace Mini_Wp_Gdpr;

defined('ABSPATH') || die();

class CF7_Helper extends Component
{
    private $now;

    public function __construct(string $name, string $version)
    {
        parent::__construct($name, $version);
    }

    public function is_cf7_installed()
    {
        return defined('WPCF7_PLUGIN') && !empty(defined('WPCF7_PLUGIN')) && function_exists('wpcf7_contact_form');
    }

    public function is_privacy_consent_checkbox_installed(int $form_id)
    {
        return $this->is_consent_box_in_form_content($form_id) && $this->is_consent_box_in_email_body($form_id);
    }

    public function is_consent_box_in_form_content(int $form_id)
    {
        $is_found = false;

        // $content = get_the_content($form_id);
        // error_log($content);

        if (!$this->is_cf7_installed()) {
            // ...
        } elseif (empty(($contact_form = wpcf7_contact_form($form_id)))) {
            // ...
        } else {
            $tags = $contact_form->scan_form_tags();
            foreach ($tags as $tag) {
                if ($tag['name'] == CF7_CONSENT_TAG_NAME) {
                    $is_found = true;
                    break;
                }
            }
        }

        return $is_found;
    }

    public function is_consent_box_in_email_body(int $form_id)
    {
        $is_found = false;

        // $content = get_the_content($form_id);
        // error_log($content);

        if (!$this->is_cf7_installed()) {
            // ...
        } elseif (empty(($contact_form = wpcf7_contact_form($form_id)))) {
            // ...
        } elseif (!is_array($mail = $contact_form->prop('mail'))) {
            // ...
        } elseif (!array_key_exists('body', $mail)) {
            // ...
        } else {
            $body = strval($mail['body']);
            $is_found = strpos($body, '[' . CF7_CONSENT_TAG_NAME . ']') !== false;
        }

        return $is_found;
    }

    public function install_consent_box(int $form_id)
    {
        if (!$this->is_cf7_installed()) {
            // ...
        } elseif (empty(($contact_form = wpcf7_contact_form($form_id)))) {
            // ...
        } else {
            if (!$this->is_consent_box_in_form_content($form_id)) {
                $properties = $contact_form->get_properties();
                if (!array_key_exists('form', $properties)) {
                    $properties['form'] = '';
                }

                $checkbox_tag = sprintf(
                    '[checkbox* %s use_label_element "%s"]',
                    CF7_CONSENT_TAG_NAME,
                    __('I agree to the storage and handling of my data by this website, as specified in the privacy policy', 'mini-wp-gdpr')
                );

                if (strpos($properties['form'], '[submit') !== false) {
                    $properties['form'] = str_replace('[submit', $checkbox_tag . "\n\n[submit", $properties['form']);
                } else {
                    $properties['form'] .= "\n\n";
                    $properties['form'] .= $checkbox_tag;
                }

                $contact_form->set_properties($properties);
                $contact_form->save();
            }

            if (!$this->is_consent_box_in_email_body($form_id)) {
                $properties = $contact_form->get_properties();
                if (!array_key_exists('mail', $properties)) {
                    $properties['mail'] = [];
                }

                if (!array_key_exists('body', $properties['mail'])) {
                    $properties['mail']['body'] = [];
                }

                $properties['mail']['body'] = '[' . CF7_CONSENT_TAG_NAME . "]\n\n" . $properties['mail']['body'];

                $contact_form->set_properties($properties);
                $contact_form->save();
            }
        }
    }

    public function is_a_cf7_form(int $post_id)
    {
        return get_post_type($post_id) == 'wpcf7_contact_form';
    }

    public function get_form_metas()
    {
        $metas = [];

        $args = [
            'post_type' => CF7_POST_TYPE,
            'post_status' => 'publish'
        ];

        if (!empty(($posts = get_posts($args)))) {
            foreach ($posts as $post) {
                $form_id = $post->ID;

                $is_installed = $this->is_privacy_consent_checkbox_installed($form_id);

                $form_title = '';
                if (empty(($form_title = get_the_title($post)))) {
                    $form_title = __('Untitled Form', 'mini-wp-gdpr');
                }

                $form_name = 'form_' . $form_id;
                $metas[$form_name] = [
                    'title' => $form_title,
                    'isConsentInstalled' => $is_installed,
                    'formId' => $form_id
                ];
            }
        }

        return $metas;
    }
}
