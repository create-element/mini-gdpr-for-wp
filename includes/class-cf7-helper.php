<?php

/**
 * Contact Form 7 integration helper.
 *
 * @package Mini_Wp_Gdpr
 * @since   1.0.0
 */

namespace Mini_Wp_Gdpr;

defined( 'ABSPATH' ) || die();

/**
 * Provides utilities for adding a GDPR consent checkbox to Contact Form 7 forms.
 *
 * Handles detection of existing consent boxes, installation of new consent
 * checkboxes into form content and email bodies, and retrieval of form metadata
 * for the admin settings page.
 *
 * @since 1.0.0
 */
class CF7_Helper extends Component {

	/**
	 * Constructor.
	 *
	 * @param string $name    Plugin slug.
	 * @param string $version Plugin version.
	 */
	public function __construct( string $name, string $version ) { // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found -- Kept for future extension and docblock clarity.
		parent::__construct( $name, $version );
	}

	/**
	 * Check whether Contact Form 7 is installed and active.
	 *
	 * @return bool
	 */
	public function is_cf7_installed() {
		return defined( 'WPCF7_PLUGIN' ) && ! empty( defined( 'WPCF7_PLUGIN' ) ) && function_exists( 'wpcf7_contact_form' );
	}

	/**
	 * Check whether the GDPR consent checkbox is installed in both the form content
	 * and the email body for the given CF7 form.
	 *
	 * @param int $form_id CF7 form post ID.
	 * @return bool
	 */
	public function is_privacy_consent_checkbox_installed( int $form_id ) {
		return $this->is_consent_box_in_form_content( $form_id ) && $this->is_consent_box_in_email_body( $form_id );
	}

	/**
	 * Check whether the GDPR consent checkbox tag is present in the CF7 form content.
	 *
	 * @param int $form_id CF7 form post ID.
	 * @return bool
	 */
	public function is_consent_box_in_form_content( int $form_id ) {
		$is_found = false;

		if ( $this->is_cf7_installed() ) {
			$contact_form = wpcf7_contact_form( $form_id );

			if ( ! empty( $contact_form ) ) {
				$tags = $contact_form->scan_form_tags();

				foreach ( $tags as $tag ) {
					if ( $tag['name'] === CF7_CONSENT_TAG_NAME ) {
						$is_found = true;
						break;
					}
				}
			}
		}

		return $is_found;
	}

	/**
	 * Check whether the GDPR consent field placeholder is present in the CF7 email body.
	 *
	 * @param int $form_id CF7 form post ID.
	 * @return bool
	 */
	public function is_consent_box_in_email_body( int $form_id ) {
		$is_found = false;

		// phpcs:disable Generic.CodeAnalysis.AssignmentInCondition.Found, Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure -- Intentional SESE guard pattern.
		if ( ! $this->is_cf7_installed() ) {
			// CF7 is not installed.
		} elseif ( empty( ( $contact_form = wpcf7_contact_form( $form_id ) ) ) ) {
			// Form not found.
		} elseif ( ! is_array( ( $mail = $contact_form->prop( 'mail' ) ) ) ) {
			// Mail config is not an array.
		} elseif ( ! array_key_exists( 'body', $mail ) ) {
			// Mail config has no body key.
		} else {
			$body     = strval( $mail['body'] );
			$is_found = strpos( $body, '[' . CF7_CONSENT_TAG_NAME . ']' ) !== false;
		}
		// phpcs:enable Generic.CodeAnalysis.AssignmentInCondition.Found, Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure

		return $is_found;
	}

	/**
	 * Add the GDPR consent checkbox to a CF7 form's content and email body.
	 *
	 * Idempotent: each insertion is skipped if the consent box is already present.
	 * Throws an exception if CF7 is not installed or the form is not found.
	 *
	 * @param int $form_id CF7 form post ID.
	 * @return void
	 */
	public function install_consent_box( int $form_id ) {
		// phpcs:disable Generic.CodeAnalysis.AssignmentInCondition.Found, Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure -- Intentional SESE guard pattern.
		if ( ! $this->is_cf7_installed() ) {
			// CF7 is not installed — nothing to do.
		} elseif ( empty( ( $contact_form = wpcf7_contact_form( $form_id ) ) ) ) {
			// Form not found.
		} else {
			if ( ! $this->is_consent_box_in_form_content( $form_id ) ) {
				$properties = $contact_form->get_properties();

				if ( ! array_key_exists( 'form', $properties ) ) {
					$properties['form'] = '';
				}

				$checkbox_tag = sprintf(
					'[checkbox* %s use_label_element "%s"]',
					CF7_CONSENT_TAG_NAME,
					__( 'I agree to the storage and handling of my data by this website, as specified in the privacy policy', 'mini-wp-gdpr' )
				);

				if ( strpos( $properties['form'], '[submit' ) !== false ) {
					$properties['form'] = str_replace( '[submit', $checkbox_tag . "\n\n[submit", $properties['form'] );
				} else {
					$properties['form'] .= "\n\n" . $checkbox_tag;
				}

				$contact_form->set_properties( $properties );
				$contact_form->save();
			}

			if ( ! $this->is_consent_box_in_email_body( $form_id ) ) {
				$properties = $contact_form->get_properties();

				if ( ! array_key_exists( 'mail', $properties ) ) {
					$properties['mail'] = [];
				}

				if ( ! array_key_exists( 'body', $properties['mail'] ) ) {
					$properties['mail']['body'] = [];
				}

				$properties['mail']['body'] = '[' . CF7_CONSENT_TAG_NAME . "]\n\n" . $properties['mail']['body'];

				$contact_form->set_properties( $properties );
				$contact_form->save();
			}
		}
		// phpcs:enable Generic.CodeAnalysis.AssignmentInCondition.Found, Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure
	}

	/**
	 * Check whether a given post ID refers to a Contact Form 7 form.
	 *
	 * @param int $post_id WordPress post ID.
	 * @return bool
	 */
	public function is_a_cf7_form( int $post_id ) {
		return get_post_type( $post_id ) === 'wpcf7_contact_form';
	}

	/**
	 * Return metadata for all published CF7 forms, including consent installation status.
	 *
	 * @return array Associative array keyed by 'form_{id}', each value containing
	 *               'title', 'isConsentInstalled', and 'formId'.
	 */
	public function get_form_metas() {
		$metas = [];

		$args = [
			'post_type'   => CF7_POST_TYPE,
			'post_status' => 'publish',
		];

		$posts = get_posts( $args );

		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				$form_id      = $post->ID;
				$is_installed = $this->is_privacy_consent_checkbox_installed( $form_id );
				$form_title   = get_the_title( $post );

				if ( empty( $form_title ) ) {
					$form_title = __( 'Untitled Form', 'mini-wp-gdpr' );
				}

				$form_name          = 'form_' . $form_id;
				$metas[ $form_name ] = [
					'title'            => $form_title,
					'isConsentInstalled' => $is_installed,
					'formId'           => $form_id,
				];
			}
		}

		return $metas;
	}
}
