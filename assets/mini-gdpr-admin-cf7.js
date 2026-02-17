/**
 * Mini WP GDPR - Admin Contact Form 7 integration handler.
 *
 * Populates the CF7 forms table, handles install-consent button clicks,
 * and refreshes the row status after each AJAX action.
 *
 * @since 2.0.0
 */
( function () {
	'use strict';

	/**
	 * CF7 integration page controller.
	 *
	 * One instance is created per [data-mwg-cf7-forms] container on the page.
	 *
	 * @since 2.0.0
	 */
	class MiniGdprCf7 {

		/**
		 * Constructor.
		 *
		 * @param {HTMLElement} container The [data-mwg-cf7-forms] wrapper element.
		 * @since 2.0.0
		 */
		constructor( container ) {
			this.container    = container;
			this.meta         = JSON.parse( container.getAttribute( 'data-mwg-cf7-forms' ) );
			this.spinner      = container.querySelector( '.pp-spinner' );
			this.tbody        = container.querySelector( 'tbody' );
			this._initTimeout = null;
		}

		/**
		 * Initialise: populate rows, bind delegated events, refresh status.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		init() {
			this.populateRows();
			this.bindEvents();
			this.refreshTable( this.meta.forms );
			this.hideSpinnerDelayed();
		}

		/**
		 * Build a table row for each form and append it to tbody.
		 *
		 * Shows the no-forms notice if the forms map is empty.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		populateRows() {
			const { forms, labels } = this.meta;
			let formCount = 0;

			for ( const key in forms ) {
				if ( ! Object.prototype.hasOwnProperty.call( forms, key ) ) {
					continue;
				}

				const form = forms[ key ];
				const row  = document.createElement( 'tr' );
				row.setAttribute( 'data-form-name', key );

				// Column 1: form title.
				const titleCell = document.createElement( 'td' );
				titleCell.className   = 'align-left';
				titleCell.textContent = form.title;
				row.appendChild( titleCell );

				// Column 2: consent-installed status icons.
				const statusCell   = document.createElement( 'td' );
				statusCell.className = 'align-centre';
				statusCell.innerHTML = '<span class="dashicons dashicons-yes" style="display:none;"></span><span class="dashicons dashicons-no" style="display:none;"></span>';
				row.appendChild( statusCell );

				// Column 3: install button.
				const actionCell  = document.createElement( 'td' );
				const installBtn  = document.createElement( 'button' );
				installBtn.type          = 'button';
				installBtn.className     = 'button install-consent';
				installBtn.disabled      = true;
				installBtn.dataset.formId = String( form.formId );
				installBtn.textContent   = labels.installConsentButton;
				actionCell.appendChild( installBtn );
				row.appendChild( actionCell );

				this.tbody.appendChild( row );
				++formCount;
			}

			if ( formCount === 0 ) {
				const noForms = this.container.querySelector( '.mwg-cf7-no-forms' );
				if ( noForms ) {
					noForms.style.display = '';
				}
			}
		}

		/**
		 * Bind a delegated click listener on tbody for all .install-consent buttons.
		 *
		 * Event delegation handles any buttons that may be added or re-enabled later
		 * without requiring re-binding.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		bindEvents() {
			this.tbody.addEventListener( 'click', ( e ) => {
				const btn = e.target.closest( '.install-consent' );
				if ( ! btn || btn.disabled ) {
					return;
				}
				e.preventDefault();
				this.installConsent( parseInt( btn.dataset.formId, 10 ) );
			} );
		}

		/**
		 * Update status icons and button states for each form in the map.
		 *
		 * @since 2.0.0
		 * @param {Object} formsData Map of form-name → { isConsentInstalled } from the server.
		 * @return {void}
		 */
		refreshTable( formsData ) {
			if ( ! formsData ) {
				return;
			}

			for ( const key in formsData ) {
				if ( ! Object.prototype.hasOwnProperty.call( formsData, key ) ) {
					continue;
				}

				const row = this.tbody.querySelector( `tr[data-form-name="${ key }"]` );
				if ( ! row ) {
					continue;
				}

				const iconYes    = row.querySelector( '.dashicons-yes' );
				const iconNo     = row.querySelector( '.dashicons-no' );
				const installBtn = row.querySelector( '.install-consent' );

				if ( formsData[ key ].isConsentInstalled ) {
					if ( iconNo )     { iconNo.style.display     = 'none'; }
					if ( iconYes )    { iconYes.style.display    = ''; }
					if ( installBtn ) { installBtn.disabled      = true; }
				} else {
					if ( iconYes )    { iconYes.style.display    = 'none'; }
					if ( iconNo )     { iconNo.style.display     = ''; }
					if ( installBtn ) { installBtn.disabled      = false; }
				}
			}
		}

		/**
		 * Send the install-consent AJAX request for the given form.
		 *
		 * Disables the button before the request and re-enables it on failure so the
		 * user can retry. On success the row's status is refreshed via refreshTable().
		 *
		 * @since 2.0.0
		 * @param {number} formId The CF7 form ID to install consent for.
		 * @return {void}
		 */
		async installConsent( formId ) {
			const installBtn = this.tbody.querySelector( `button[data-form-id="${ formId }"]` );

			if ( installBtn ) {
				installBtn.disabled = true;
			}

			this.showSpinner();

			const formData = new FormData();
			formData.append( 'action', this.meta.action );
			formData.append( 'nonce', this.meta.nonce );
			formData.append( 'formId', String( formId ) );

			try {
				const response = await fetch( ajaxurl, { // eslint-disable-line no-undef
					method: 'POST',
					body: formData,
				} );

				const data = await response.json();
				this.refreshTable( data.forms );
			} catch ( error ) {
				console.error( 'Mini GDPR: CF7 install consent request failed.', error );
				if ( installBtn ) {
					installBtn.disabled = false;
				}
			} finally {
				this.hideSpinner();
			}
		}

		/**
		 * Show the container's loading spinner.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		showSpinner() {
			if ( this.spinner ) {
				this.spinner.style.display = '';
			}
		}

		/**
		 * Hide the container's loading spinner immediately.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		hideSpinner() {
			if ( this.spinner ) {
				this.spinner.style.display = 'none';
			}
		}

		/**
		 * Hide the initial loading spinner after a 250 ms delay.
		 *
		 * Cancels any pending timer before scheduling a new one so multiple
		 * containers do not interfere with each other.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		hideSpinnerDelayed() {
			if ( this._initTimeout !== null ) {
				clearTimeout( this._initTimeout );
			}
			this._initTimeout = setTimeout( () => {
				this.hideSpinner();
				this._initTimeout = null;
			}, 250 );
		}
	}

	document.addEventListener( 'DOMContentLoaded', () => {
		document.querySelectorAll( '[data-mwg-cf7-forms]' ).forEach( ( container ) => {
			new MiniGdprCf7( container ).init();
		} );
	} );
} )();
