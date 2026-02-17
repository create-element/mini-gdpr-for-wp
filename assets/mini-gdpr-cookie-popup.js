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
			this.data        = data;
			this.data.blkon  = data.blkon  === '1';
			this.data.always = data.always === '1';
		}

		/**
		 * Determine whether the user has previously consented within the allowed window.
		 *
		 * Checks localStorage first; falls back to document.cookie.
		 *
		 * @since 2.0.0
		 * @return {boolean} True if consent exists and has not expired.
		 */
		hasConsented() {
			if ( typeof localStorage !== 'undefined' ) {
				const consentDate = Date.parse( localStorage.getItem( this.data.cn ) );
				if ( consentDate ) {
					const now        = new Date();
					const consentAge = Math.round( ( now - consentDate ) / 1000.0 );
					const maxAge     = parseInt( this.data.cd, 10 ) * 86400; // 1 day = 86400 s
					return consentAge < maxAge;
				}
				return false;
			}

			return document.cookie.split( ';' ).some( ( pair ) => {
				return pair.split( '=' )[ 0 ].trim().startsWith( this.data.cn );
			} );
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

			popup.innerHTML = `<p>${ this.data.msg }</p><div class="btn-box"><button class="accept">${ this.data.ok }</button><button class="more-info">${ this.data.mre }</button></div>`;

			popup.querySelector( 'button.accept' ).addEventListener( 'click', () => this.consentToScripts() );
			popup.querySelector( 'button.more-info' ).addEventListener( 'click', () => this.showBlockedScripts() );

			if ( Array.isArray( this.data.cls ) ) {
				popup.className = this.data.cls.join( ' ' );
			}

			document.body.appendChild( popup );
			popup.querySelector( 'button.accept' ).focus();
		}

		/**
		 * Store the user's consent and inject previously blocked scripts.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		consentToScripts() {
			const popup = document.getElementById( 'mgwcsCntr' );
			if ( ! popup ) {
				return;
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
			panel.className = 'mgw-nfo';
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
			closeBtn.addEventListener( 'click', () => this.closeBlockedScripts() );
			panel.appendChild( closeBtn );

			document.body.appendChild( overlay );
			closeBtn.focus();
		}

		/**
		 * Remove the blocked-scripts overlay from the DOM.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		closeBlockedScripts() {
			const overlay = document.getElementById( 'mgwcsOvly' );
			if ( overlay ) {
				overlay.remove();
			}
		}

		/**
		 * Initialise: inject deferred scripts immediately if already consented,
		 * or show the popup if the user hasn't yet decided.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		init() {
			if ( this.hasConsented() ) {
				this.insertBlockedScripts();
			} else {
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
