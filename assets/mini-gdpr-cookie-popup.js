/**
 * Mini GDPR Cookie Popup
 *
 * Handles cookie consent popup display, consent storage, deferred script
 * injection, the "more info" overlay listing blocked trackers, and the
 * manage-preferences button that allows users to revisit their decision.
 *
 * Public API (available after DOMContentLoaded):
 *   window.mgwRejectScripts()          — programmatically reject tracking.
 *   window.mgwShowCookiePreferences()  — clear stored decision and re-show popup.
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

			// Google Analytics: load gtag.js after consent. loadGoogleAnalytics() fires
			// gtag('consent','update',{...granted}) before loading the SDK so the full
			// dataLayer queue (consent.default=denied → consent.update=granted → js →
			// config) is processed in the correct order.
			this.loadGoogleAnalytics();

			// Facebook Pixel: load fbevents.js after consent. The fbq stub already
			// has fbq('init') and fbq('track','PageView') queued; the SDK replays
			// them automatically when fbevents.js loads.
			this.loadFacebookPixel();

			// Microsoft Clarity: load clarity.ms/tag/<ID> after consent. The
			// clarity stub already has window.clarity set up as a queue; the
			// Clarity SDK processes window.clarity.q on load.
			this.loadMicrosoftClarity();

			popup.classList.add( 'mgw-fin' );
			setTimeout( () => {
				popup.remove();
				this.showManagePreferencesLink();
			}, 500 );
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
		 * When called with no popup visible (e.g. via the mgwRejectScripts() public
		 * API on a page where the user has already decided), the rejection is stored
		 * and the manage-preferences button is shown without any popup transition.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		rejectConsent() {
			const popup = document.getElementById( 'mgwcsCntr' );

			// Remove focus trap if the popup is currently visible.
			if ( popup && this._popupKeydown ) {
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

			// For logged-in users: notify the server so rejection is stored in user meta.
			if ( this.data.ajaxUrl && this.data.rejectAction && this.data.rejectNonce ) {
				const body = new URLSearchParams( {
					action: this.data.rejectAction,
					nonce:  this.data.rejectNonce,
				} );
				fetch( this.data.ajaxUrl, { method: 'POST', body } ).catch( () => {} );
			}

			if ( popup ) {
				popup.classList.add( 'mgw-fin' );
				setTimeout( () => {
					popup.remove();
					this.showManagePreferencesLink();
				}, 500 );
			} else {
				this.showManagePreferencesLink();
			}
		}

		/**
		 * Dynamically load Facebook Pixel (fbevents.js) after the user has consented.
		 *
		 * The fbq stub (window.fbq function + queue) and the queued fbq('init') and
		 * fbq('track', 'PageView') calls are already present on the page (output by
		 * the PHP tracker stub on every page load). This method:
		 *
		 *   1. Calls fbq('consent','grant') to add a grant signal to the queue BEFORE
		 *      fbevents.js loads. When the SDK processes the queue it sees:
		 *      consent=revoke → consent=grant → init → PageView — so the pixel
		 *      initialises in full tracking mode after the user has explicitly accepted.
		 *   2. Dynamically loads fbevents.js, which replays the queued calls in order.
		 *
		 * The fbq stub outputs fbq('consent','revoke') as the very first queued call
		 * (defensive GDPR guard — prevents tracking if fbevents.js loads unexpectedly).
		 * The grant call queued here overrides that revoke so the pixel initialises
		 * with explicit consent.
		 *
		 * This method is a no-op when mgwcsData.fbpxId is absent (pixel not configured,
		 * pixel disabled in settings, or current user excluded by role).
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		loadFacebookPixel() {
			if ( ! this.data.fbpxId ) {
				return;
			}

			// FB Pixel Consent API: queue a consent grant BEFORE fbevents.js loads so
			// the SDK initialises in fully-granted state when it processes the queue.
			// Guard: fbq is always defined when fbpxId is set (stub is in <head>), but
			// the typeof check makes the intent explicit and prevents errors if the stub
			// was somehow removed by a third-party script manager.
			if ( typeof fbq === 'function' ) {
				fbq( 'consent', 'grant' );
			}

			const script = document.createElement( 'script' );
			script.src   = 'https://connect.facebook.net/en_US/fbevents.js';
			script.async = true;
			document.head.appendChild( script );
		}

		/**
		 * Dynamically load Google Analytics (gtag.js) after the user has consented.
		 *
		 * The dataLayer queue already contains (output by the PHP tracker stubs on every
		 * page load):
		 *   - gtag('consent','default',{...denied})  — when Consent Mode v2 is enabled
		 *   - gtag('js', new Date())                 — page-load timestamp
		 *   - gtag('config', 'ID')                   — tracker configuration
		 *
		 * Before loading gtag.js, this method fires gtag('consent','update',{...granted})
		 * so the queue contains a granted signal regardless of whether this is a new
		 * consent (called from consentToScripts()) or a returning visitor (called from
		 * init()). When gtag.js loads it processes the full queue — consent.default=denied
		 * → consent.update=granted → js → config — and initialises GA in the
		 * fully-granted state.
		 *
		 * Without this update, returning visitors would have their GA data attributed
		 * to the denied consent state (because consent.default=denied is always output
		 * unconditionally in <head> when Consent Mode v2 is enabled, and init() does
		 * not otherwise signal the previously-stored consent decision to gtag.js).
		 *
		 * This mirrors the pattern used by loadFacebookPixel(), which also fires its
		 * consent signal (fbq('consent','grant')) immediately before loading the SDK.
		 *
		 * This method is a no-op when mgwcsData.gaId is absent (GA not configured,
		 * GA disabled in settings, or current user excluded by role).
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		loadGoogleAnalytics() {
			if ( ! this.data.gaId ) {
				return;
			}

			// Google Consent Mode v2: queue a consent grant BEFORE gtag.js loads so
			// the SDK initialises in the fully-granted state when it processes the queue.
			// This fires for both new consents and returning visitors — the consent.default
			// in <head> is always 'denied', so an explicit update is always required.
			// Guard: gtag is always defined when gaId is set (stub is in <head>), but
			// the typeof check makes the intent explicit and prevents errors if the stub
			// was somehow removed by a third-party script manager.
			if ( typeof gtag === 'function' ) {
				gtag( 'consent', 'update', {
					analytics_storage: 'granted',
					ad_storage: 'granted',
					ad_user_data: 'granted',
					ad_personalization: 'granted',
				} );
			}

			const script = document.createElement( 'script' );
			script.src   = 'https://www.googletagmanager.com/gtag/js?id=' + this.data.gaId;
			script.async = true;
			document.head.appendChild( script );
		}

		/**
		 * Dynamically load Microsoft Clarity (clarity.ms/tag/<ID>) after the user has consented.
		 *
		 * The clarity stub (window.clarity queue function) is already present on the page
		 * (output by the PHP tracker stub on every page load). Loading the Clarity SDK here
		 * causes it to discover window.clarity.q and replay any queued calls, completing
		 * initialisation without losing any events that were queued before consent.
		 *
		 * Microsoft Clarity does not have a consent API comparable to Google Consent Mode v2
		 * or Facebook Pixel's fbq('consent','grant'). The GDPR-compliant approach for Clarity
		 * is simply to block the clarity.ms SDK from loading until consent is given — which
		 * is exactly what this method enforces. No pre-consent signals need to be queued.
		 *
		 * A <link rel="preconnect"> hint for clarity.ms is output in wp_head (priority 1)
		 * by the PHP tracker file, pre-establishing the TCP/TLS connection so that the
		 * clarity.ms request triggered here resolves faster.
		 *
		 * This method is a no-op when mgwcsData.clarityId is absent (Clarity not configured,
		 * Clarity disabled in settings, or current user excluded by role).
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		loadMicrosoftClarity() {
			if ( ! this.data.clarityId ) {
				return;
			}

			const script = document.createElement( 'script' );
			script.src   = 'https://www.clarity.ms/tag/' + this.data.clarityId;
			script.async = true;
			document.head.appendChild( script );
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
		 * Render a small persistent button that allows the user to revisit their
		 * cookie preference at any time after the popup has been dismissed.
		 *
		 * The button is rendered only once — calling this method when it already
		 * exists is a no-op. Clicking it calls changePreferences(), which clears
		 * the stored decision and re-shows the consent popup.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		showManagePreferencesLink() {
			if ( document.getElementById( 'mgwMngBtn' ) ) {
				return;
			}

			const btn = document.createElement( 'button' );
			btn.id        = 'mgwMngBtn';
			btn.textContent = '\uD83C\uDF6A'; // 🍪 cookie emoji.
			btn.setAttribute( 'aria-label', 'Manage cookie preferences' );
			btn.setAttribute( 'title', 'Manage cookie preferences' );
			btn.addEventListener( 'click', () => this.changePreferences() );
			document.body.appendChild( btn );
		}

		/**
		 * Clear the stored consent/rejection decision and re-show the consent popup.
		 *
		 * Removes both the consent and rejection storage entries so the user can
		 * make a fresh choice. Resets the in-flight accept guard so Accept works
		 * correctly when the popup is shown again.
		 *
		 * Called by the manage-preferences button and exposed publicly as the
		 * window.mgwShowCookiePreferences() function.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		changePreferences() {
			// Remove the manage-preferences button.
			const manageBtn = document.getElementById( 'mgwMngBtn' );
			if ( manageBtn ) {
				manageBtn.remove();
			}

			// Reset the in-flight guard so Accept works again after preferences reset.
			this._accepting = false;

			// Clear stored decisions (localStorage or cookie fallback).
			if ( typeof localStorage !== 'undefined' ) {
				localStorage.removeItem( this.data.cn );
				localStorage.removeItem( this.data.rcn );
			} else {
				document.cookie = `${ this.data.cn }=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/`;
				document.cookie = `${ this.data.rcn }=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/`;
			}

			// Re-show the consent popup.
			this.showPopup();
		}

		/**
		 * Initialise: inject deferred scripts if already consented, show the
		 * manage-preferences button if already decided, or show the popup if the
		 * user hasn't yet made a choice.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		init() {
			if ( this.hasConsented() ) {
				this.insertBlockedScripts();
				// Google Analytics: load gtag.js for returning visitors who already
				// consented. The dataLayer queue (gtag('js') + gtag('config')) is
				// replayed by gtag.js on load, initialising GA tracking normally.
				this.loadGoogleAnalytics();
				// Facebook Pixel: load fbevents.js for returning visitors who already
				// consented. The queued fbq('init') + fbq('track','PageView') events
				// are replayed automatically when fbevents.js loads.
				this.loadFacebookPixel();
				// Microsoft Clarity: load clarity.ms for returning visitors who already
				// consented. The clarity stub queue is replayed by the Clarity SDK on load.
				this.loadMicrosoftClarity();
				this.showManagePreferencesLink();
			} else if ( this.hasRejected() ) {
				this.showManagePreferencesLink();
			} else {
				this.showPopup();
			}
		}
	}

	document.addEventListener( 'DOMContentLoaded', () => {
		if ( typeof mgwcsData !== 'undefined' ) {
			const popupInstance = new MiniGdprPopup( mgwcsData );
			popupInstance.init();

			/**
			 * Public API — callable by theme and plugin developers.
			 *
			 * mgwRejectScripts()         — Store a rejection decision and dismiss the
			 *                              consent popup if it is currently visible.
			 *                              Has no effect on scripts already loaded.
			 *
			 * mgwShowCookiePreferences() — Clear the stored consent/rejection decision
			 *                              and re-display the consent popup, allowing
			 *                              the user to make a fresh choice.
			 */
			window.mgwRejectScripts         = () => popupInstance.rejectConsent();
			window.mgwShowCookiePreferences = () => popupInstance.changePreferences();
		}
	} );
} )();
