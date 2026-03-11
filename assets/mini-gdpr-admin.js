/**
 * Mini WP GDPR - Admin settings page handler.
 *
 * Handles tabbed navigation and the Reset All Consents button interaction
 * on the plugin settings page.
 *
 * @since 2.0.0
 */
( function () {
	'use strict';

	/**
	 * Admin settings page controller.
	 *
	 * @since 2.0.0
	 */
	class MiniGdprAdmin {

		/**
		 * Constructor — bind event listeners.
		 *
		 * @since 2.0.0
		 */
		constructor() {
			this.tabs   = document.querySelectorAll( '.nav-tab[data-tab]' );
			this.panels = document.querySelectorAll( '.mwg-tab-panel' );
			this.init();
		}

		/**
		 * Initialise tab navigation and reset-consents handlers.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		init() {
			// Tab navigation.
			if ( this.tabs.length > 0 ) {
				const activeTab = window.location.hash.substring( 1 ) || 'consent';
				this.activateTab( activeTab );

				this.tabs.forEach( ( tab ) => {
					tab.addEventListener( 'click', ( e ) => {
						e.preventDefault();
						const tabName = tab.dataset.tab;
						window.location.hash = tabName;
						this.activateTab( tabName );
					} );
				} );

				window.addEventListener( 'hashchange', () => {
					const tabName = window.location.hash.substring( 1 ) || 'consent';
					this.activateTab( tabName );
				} );
			}

			// Reset all consents button.
			document.querySelectorAll( '[data-reset-all-consents]' ).forEach( ( button ) => {
				button.addEventListener( 'click', ( e ) => this.handleResetAllConsents( e, button ) );
			} );
		}

		/**
		 * Activate a tab by name, showing its panel and hiding others.
		 *
		 * @since 2.0.0
		 * @param {string} tabName The tab identifier (matches data-tab attribute).
		 * @return {void}
		 */
		activateTab( tabName ) {
			this.tabs.forEach( ( t ) => t.classList.remove( 'nav-tab-active' ) );

			const activeNavTab = document.querySelector( `[data-tab="${ tabName }"]` );
			if ( activeNavTab ) {
				activeNavTab.classList.add( 'nav-tab-active' );
			}

			this.panels.forEach( ( panel ) => {
				panel.classList.remove( 'mwg-tab-panel--active' );
			} );

			const activePanel = document.getElementById( `${ tabName }-panel` );
			if ( activePanel ) {
				activePanel.classList.add( 'mwg-tab-panel--active' );
			}
		}

		/**
		 * Handle the Reset All Consents button click.
		 *
		 * Reads action/nonce from the button's data-reset-all-consents attribute,
		 * shows an optional confirmation dialog, then sends the AJAX reset request.
		 *
		 * @since 2.0.0
		 * @param {Event}       e      Click event.
		 * @param {HTMLElement} button The clicked button element.
		 * @return {void}
		 */
		async handleResetAllConsents( e, button ) {
			e.preventDefault();

			const args = JSON.parse( button.getAttribute( 'data-reset-all-consents' ) );
			const container = button.parentElement;
			const spinner = container ? container.querySelector( 'img' ) : null;

			if ( args.confirmMessage && ! window.confirm( args.confirmMessage ) ) {
				return;
			}

			this.showSpinner( spinner );

			try {
				const formData = new FormData();
				formData.append( 'action', args.action );
				formData.append( 'nonce', args.nonce );

				// ajaxurl is a WordPress global available on all admin pages.
				const response = await fetch( ajaxurl, { // eslint-disable-line no-undef
					method: 'POST',
					body: formData,
				} );

				const data = await response.json();

				if ( data.message ) {
					window.alert( data.message );
				}
			} catch ( error ) {
				console.error( 'Mini GDPR: reset consents request failed.', error );
			} finally {
				this.hideSpinner( spinner );
				button.disabled = false;
			}
		}

		/**
		 * Make the loading spinner element visible.
		 *
		 * @since 2.0.0
		 * @param {HTMLElement|null} spinner The spinner element, or null if not present.
		 * @return {void}
		 */
		showSpinner( spinner ) {
			if ( spinner ) {
				spinner.style.opacity = '1';
				spinner.style.display = '';
			}
		}

		/**
		 * Hide the loading spinner with a fade-out transition.
		 *
		 * @since 2.0.0
		 * @param {HTMLElement|null} spinner The spinner element, or null if not present.
		 * @return {void}
		 */
		hideSpinner( spinner ) {
			if ( spinner ) {
				spinner.style.transition = 'opacity 0.4s';
				spinner.style.opacity = '0';
				setTimeout( () => {
					spinner.style.display = 'none';
				}, 400 );
			}
		}
	}

	document.addEventListener( 'DOMContentLoaded', () => {
		new MiniGdprAdmin();
	} );
} )();
