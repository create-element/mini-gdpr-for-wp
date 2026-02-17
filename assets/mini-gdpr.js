/**
 * Mini WP GDPR - Front-end consent form handler.
 *
 * Validates GDPR checkbox on form submission and fires the accept AJAX action
 * when a consent checkbox is checked on a WooCommerce or custom consent form.
 *
 * @since 2.0.0
 */
( function () {
	'use strict';

	/**
	 * Front-end consent form controller.
	 *
	 * @since 2.0.0
	 */
	class MiniGdprForms {

		/**
		 * Constructor.
		 *
		 * @param {Object} config Localised plugin data (miniWpGdpr).
		 * @since 2.0.0
		 */
		constructor( config ) {
			this.config = config;
			this.init();
		}

		/**
		 * Bind event listeners to all GDPR-protected forms on the page.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		init() {
			document.querySelectorAll( 'form .mini-gdpr-checkbox' ).forEach( ( checkbox ) => {
				const form = checkbox.closest( 'form' );
				if ( form ) {
					form.addEventListener( 'submit', ( e ) => this.handleFormSubmit( e, checkbox ) );
				}
			} );

			if ( this.config.acceptAction ) {
				document.querySelectorAll( '.mini-gdpr-form .mini-gdpr-checkbox' ).forEach( ( checkbox ) => {
					checkbox.addEventListener( 'change', ( e ) => this.handleCheckboxChange( e ) );
				} );
			}
		}

		/**
		 * Block form submission if the GDPR checkbox is unchecked.
		 *
		 * @since 2.0.0
		 * @param {Event}       e        Form submit event.
		 * @param {HTMLElement} checkbox The GDPR checkbox element.
		 * @return {void}
		 */
		handleFormSubmit( e, checkbox ) {
			if ( ! checkbox.checked ) {
				e.preventDefault();
				window.alert( this.config.termsNotAccepted );
			}
		}

		/**
		 * Send the accept AJAX call when the consent checkbox is checked.
		 *
		 * @since 2.0.0
		 * @param {Event} e Checkbox change event.
		 * @return {void}
		 */
		async handleCheckboxChange( e ) {
			const checkbox = e.target;

			if ( ! checkbox.checked ) {
				return;
			}

			const container = checkbox.closest( '.mini-gdpr-form' );
			checkbox.disabled = true;

			const formData = new FormData();
			formData.append( 'action', this.config.acceptAction );
			formData.append( 'nonce', this.config.acceptNonce );
			formData.append( 'terms', '1' );

			try {
				const response = await fetch( this.config.ajaxUrl, {
					method: 'POST',
					body: formData,
				} );

				const data = await response.json();
				this.handleAcceptResponse( data, container );
			} catch ( error ) {
				console.error( 'Mini GDPR: accept request failed.', error );
				checkbox.disabled = false;
			}
		}

		/**
		 * Update the UI after a successful accept AJAX response.
		 *
		 * Fades out the consent form container and fades in a confirmation message.
		 *
		 * @since 2.0.0
		 * @param {Object}      data      Parsed JSON response from the server.
		 * @param {HTMLElement} container The .mini-gdpr-form container element.
		 * @return {void}
		 */
		handleAcceptResponse( data, container ) {
			if ( data.success !== '1' ) {
				return;
			}

			const message = data.message || 'OK';

			container.style.transition = 'opacity 0.4s';
			container.style.opacity = '0';

			container.addEventListener(
				'transitionend',
				() => {
					const messageEl = document.createElement( 'p' );
					messageEl.textContent = message;
					messageEl.style.opacity = '0';
					messageEl.style.transition = 'opacity 0.4s';
					container.insertAdjacentElement( 'afterend', messageEl );

					requestAnimationFrame( () => {
						requestAnimationFrame( () => {
							messageEl.style.opacity = '1';
						} );
					} );
				},
				{ once: true }
			);
		}
	}

	document.addEventListener( 'DOMContentLoaded', () => {
		if ( typeof miniWpGdpr !== 'undefined' ) {
			new MiniGdprForms( miniWpGdpr );
		}
	} );
} )();
