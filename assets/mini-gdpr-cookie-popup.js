/**
 * Mini GDPR Cookie Popup
 *
 * Handles cookie consent popup display, consent storage, deferred script
 * injection, and the "more info" overlay listing blocked trackers.
 *
 * @since 2.0.0
 */
( function () {
	'use strict';

	/**
	 * Cookie consent popup controller.
	 *
	 * @since 2.0.0
	 */
	class MiniGdprPopup {

		/**
		 * Constructor.
		 *
		 * @param {Object} data Localised plugin data (mgwcsData).
		 * @since 2.0.0
		 */
		constructor( data ) {
			this.data            = data;
			this.data.blkon      = data.blkon  === '1';
			this.data.always     = data.always === '1';
			this._accepting      = false;       // In-flight guard: prevents accept double-fire.
			this._overlayKeydown = null;        // Stored keyboard handler for the overlay.
			this._popupKeydown   = null;        // Stored Tab-trap handler for the consent popup.
		}

		/**
		 * Determine whether a consent/rejection timestamp stored under the given
		 * storage key is still within the allowed duration window.
		 *
		 * Checks localStorage first; falls back to document.cookie. Shared by
		 * hasConsented() and hasRejected().
		 *
		 * @since 2.0.0
		 * @param {string} storageKey localStorage / cookie key to check.
		 * @return {boolean} True if a stored date exists and has not expired.
		 */
		hasStoredDecision( storageKey ) {
			if ( typeof localStorage !== 'undefined' ) {
				const storedDate = Date.parse( localStorage.getItem( storageKey ) );
				if ( storedDate ) {
					const now      = new Date();
					const age      = Math.round( ( now - storedDate ) / 1000.0 );
					const maxAge   = parseInt( this.data.cd, 10 ) * 86400; // 1 day = 86400 s
					return age < maxAge;
				}
				return false;
			}

			return document.cookie.split( ';' ).some( ( pair ) => {
				return pair.split( '=' )[ 0 ].trim().startsWith( storageKey );
			} );
		}

		/**
		 * Determine whether the user has previously consented within the allowed window.
		 *
		 * @since 2.0.0
		 * @return {boolean} True if consent exists and has not expired.
		 */
		hasConsented() {
			return this.hasStoredDecision( this.data.cn );
		}

		/**
		 * Determine whether the user has previously rejected within the allowed window.
		 *
		 * @since 2.0.0
		 * @return {boolean} True if a rejection decision exists and has not expired.
		 */
		hasRejected() {
			return this.hasStoredDecision( this.data.rcn );
		}

		/**
		 * Render and append the consent popup to the page.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		showPopup() {
			const popup = document.createElement( 'div' );
			popup.id    = 'mgwcsCntr';
			popup.setAttribute( 'role', 'dialog' );
			popup.setAttribute( 'aria-modal', 'true' );
			popup.setAttribute( 'aria-label', 'Cookie consent' );
			popup.setAttribute( 'aria-live', 'polite' );
			popup.setAttribute( 'aria-describedby', 'mgwcs-msg' );

			popup.innerHTML = `<p id="mgwcs-msg">${ this.data.msg }</p><div class="btn-box"><button class="reject" aria-label="Reject cookies">${ this.data.rjt }</button><button class="more-info" aria-label="More information about cookies">${ this.data.mre }</button><button class="accept" aria-label="Accept cookies">${ this.data.ok }</button></div>`;

			popup.querySelector( 'button.reject' ).addEventListener( 'click', () => this.rejectConsent() );
			popup.querySelector( 'button.accept' ).addEventListener( 'click', () => this.consentToScripts() );
			popup.querySelector( 'button.more-info' ).addEventListener( 'click', () => this.showBlockedScripts() );

			if ( Array.isArray( this.data.cls ) ) {
				popup.className = this.data.cls.join( ' ' );
			}

			document.body.appendChild( popup );

			// Focus trap: cycle Tab/Shift+Tab within the popup's buttons.
			this._popupKeydown = ( e ) => {
				if ( e.key !== 'Tab' ) {
					return;
				}

				const buttons  = Array.from( popup.querySelectorAll( 'button' ) );
				const firstBtn = buttons[ 0 ];
				const lastBtn  = buttons[ buttons.length - 1 ];

				if ( e.shiftKey ) {
					if ( document.activeElement === firstBtn ) {
						e.preventDefault();
						lastBtn.focus();
					}
				} else {
					if ( document.activeElement === lastBtn ) {
						e.preventDefault();
						firstBtn.focus();
					}
				}
			};
			document.addEventListener( 'keydown', this._popupKeydown );

			popup.querySelector( 'button.accept' ).focus();
		}

		/**
		 * Store the user's consent and inject previously blocked scripts.
		 *
		 * Guarded against double-fire: a second call while the first is still running
		 * (e.g. from rapid button taps) is silently ignored.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		consentToScripts() {
			if ( this._accepting ) {
				return;
			}

			const popup = document.getElementById( 'mgwcsCntr' );
			if ( ! popup ) {
				return;
			}

			this._accepting = true;

			// Remove focus trap before the popup is removed from the DOM.
			if ( this._popupKeydown ) {
				document.removeEventListener( 'keydown', this._popupKeydown );
				this._popupKeydown = null;
			}

			if ( typeof localStorage !== 'undefined' ) {
				localStorage.setItem( this.data.cn, new Date() );
			} else {
				const expiresWhen = new Date();
				expiresWhen.setDate( expiresWhen.getDate() + parseInt( this.data.cd, 10 ) );
				document.cookie = `${ this.data.cn }=true; expires=${ expiresWhen.toUTCString() }; Secure`;
			}

			this.insertBlockedScripts();

			popup.classList.add( 'mgw-fin' );
			setTimeout( () => popup.remove(), 500 );
		}

		/**
		 * Store the user's rejection and dismiss the consent popup without injecting
		 * any tracker scripts.
		 *
		 * Saves the rejection timestamp under the rejection storage key so the popup
		 * does not reappear within the configured consent duration window. The
		 * _popupKeydown focus trap is removed before the popup element is detached
		 * from the DOM.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		rejectConsent() {
			const popup = document.getElementById( 'mgwcsCntr' );
			if ( ! popup ) {
				return;
			}

			// Remove focus trap before the popup is removed from the DOM.
			if ( this._popupKeydown ) {
				document.removeEventListener( 'keydown', this._popupKeydown );
				this._popupKeydown = null;
			}

			if ( typeof localStorage !== 'undefined' ) {
				localStorage.setItem( this.data.rcn, new Date() );
			} else {
				const expiresWhen = new Date();
				expiresWhen.setDate( expiresWhen.getDate() + parseInt( this.data.cd, 10 ) );
				document.cookie = `${ this.data.rcn }=true; expires=${ expiresWhen.toUTCString() }; Secure`;
			}

			popup.classList.add( 'mgw-fin' );
			setTimeout( () => popup.remove(), 500 );
		}

		/**
		 * Inject deferred tracker scripts into the document head.
		 *
		 * Only runs when script blocking is enabled. Iterates over mgwcsData.meta
		 * and creates <script> elements for each deferrable tracker.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		insertBlockedScripts() {
			if ( ! this.data.blkon ) {
				return;
			}

			for ( const handle in this.data.meta ) {
				if ( ! Object.prototype.hasOwnProperty.call( this.data.meta, handle ) ) {
					continue;
				}

				const meta = this.data.meta[ handle ];

				if ( ! meta[ 'can-defer' ] ) {
					continue;
				}

				const scriptEl = document.createElement( 'script' );
				scriptEl.setAttribute( 'src', meta.src );
				document.head.appendChild( scriptEl );

				if ( meta.after ) {
					const inlineScript = document.createElement( 'script' );
					inlineScript.appendChild( document.createTextNode( meta.after ) );
					document.head.appendChild( inlineScript );
				}
			}
		}

		/**
		 * Show the "more info" overlay listing all blocked trackers.
		 *
		 * Binds a document-level keyboard listener (Escape to close, Tab to trap
		 * focus within the panel) that is stored on the instance so it can be
		 * precisely removed when the overlay is closed.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		showBlockedScripts() {
			const overlay = document.createElement( 'div' );
			overlay.id        = 'mgwcsOvly';
			overlay.className = 'mgw-ovl';
			overlay.setAttribute( 'role', 'dialog' );
			overlay.setAttribute( 'aria-modal', 'true' );
			overlay.setAttribute( 'aria-label', 'Cookie information' );

			// Clicking the backdrop (but not the inner panel) closes the overlay.
			overlay.addEventListener( 'click', ( e ) => {
				if ( e.target === overlay ) {
					this.closeBlockedScripts();
				}
			} );

			const panel = document.createElement( 'div' );
			panel.className  = 'mgw-nfo';
			panel.setAttribute( 'tabindex', '-1' );
			overlay.appendChild( panel );

			if ( Object.keys( this.data.meta ).length ) {
				const blurb = document.createElement( 'p' );
				blurb.innerHTML = this.data.nfo1;
				if ( this.data.nfo3 ) {
					blurb.innerHTML += `<br />${ this.data.nfo3 }`;
				}
				panel.appendChild( blurb );

				const scriptList = document.createElement( 'ul' );
				for ( const handle in this.data.meta ) {
					if ( ! Object.prototype.hasOwnProperty.call( this.data.meta, handle ) ) {
						continue;
					}
					const listItem = document.createElement( 'li' );
					listItem.innerHTML = this.data.meta[ handle ].description;
					scriptList.appendChild( listItem );
				}

				const wrapper = document.createElement( 'div' );
				wrapper.className = 'plglst';
				wrapper.appendChild( scriptList );
				panel.appendChild( wrapper );
			} else {
				const blurb = document.createElement( 'p' );
				blurb.innerHTML = this.data.nfo2;
				panel.appendChild( blurb );
			}

			const closeBtn = document.createElement( 'button' );
			closeBtn.innerText = 'Close';
			closeBtn.setAttribute( 'aria-label', 'Close cookie information' );
			closeBtn.addEventListener( 'click', () => this.closeBlockedScripts() );
			panel.appendChild( closeBtn );

			// Keyboard handler: Escape closes; Tab traps focus within the panel.
			this._overlayKeydown = ( e ) => {
				if ( e.key === 'Escape' ) {
					this.closeBlockedScripts();
					return;
				}

				if ( e.key === 'Tab' ) {
					const focusable = Array.from(
						panel.querySelectorAll( 'button, a[href], input, select, textarea, [tabindex]:not([tabindex="-1"])' )
					);

					if ( 0 === focusable.length ) {
						e.preventDefault();
						return;
					}

					const firstEl = focusable[ 0 ];
					const lastEl  = focusable[ focusable.length - 1 ];

					if ( e.shiftKey ) {
						if ( document.activeElement === firstEl ) {
							e.preventDefault();
							lastEl.focus();
						}
					} else {
						if ( document.activeElement === lastEl ) {
							e.preventDefault();
							firstEl.focus();
						}
					}
				}
			};
			document.addEventListener( 'keydown', this._overlayKeydown );

			document.body.appendChild( overlay );
			closeBtn.focus();
		}

		/**
		 * Remove the blocked-scripts overlay from the DOM.
		 *
		 * Removes the document-level keyboard listener registered in
		 * showBlockedScripts() to prevent memory leaks and ghost handlers.
		 * Returns focus to the "More info" button in the consent popup so
		 * keyboard users can continue navigating.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		closeBlockedScripts() {
			if ( this._overlayKeydown ) {
				document.removeEventListener( 'keydown', this._overlayKeydown );
				this._overlayKeydown = null;
			}

			const overlay = document.getElementById( 'mgwcsOvly' );
			if ( overlay ) {
				overlay.remove();
			}

			// Return focus to the popup so keyboard users can proceed.
			const moreInfoBtn = document.querySelector( '#mgwcsCntr button.more-info' );
			if ( moreInfoBtn ) {
				moreInfoBtn.focus();
			}
		}

		/**
		 * Initialise: inject deferred scripts if already consented, skip entirely
		 * if already rejected, or show the popup if the user hasn't yet decided.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		init() {
			if ( this.hasConsented() ) {
				this.insertBlockedScripts();
			} else if ( ! this.hasRejected() ) {
				this.showPopup();
			}
		}
	}

	document.addEventListener( 'DOMContentLoaded', () => {
		if ( typeof mgwcsData !== 'undefined' ) {
			new MiniGdprPopup( mgwcsData ).init();
		}
	} );
} )();
