var MultilingualPress = window.MultilingualPress || {};
var MultilingualPressRedirectorSettings = window.MultilingualPressRedirectorSettings || {};

MultilingualPress.RedirectorSettings = MultilingualPressRedirectorSettings;
delete window.MultilingualPressRedirectorSettings;

( function ( $, M, navigator, localStorage, window ) {

	'use strict';

	/**
	 * The MultilingualPress Redirector module.
	 */
	M.Redirector = function ( languagesStorageKey, timestampStorageKey ) {

		var settings;
		var allUserLanguages;
		var noredirect;
		var contentLanguage;
		var noRedirectRegex;
		var init = false;

		/**
		 * Checks if the stored timestamp is valid.
		 *
		 * @return {Boolean} Whether or not the stored timestamp is valid.
		 */
		var checkTimestamp = function () {
			var timestamp = Number( localStorage.getItem( timestampStorageKey ) );

			return Date.now() <= timestamp + Number( settings.storageLifetime );
		};

		/**
		 * Starts the continuously running timestamp update used to determine the age of stored languages.
		 */
		var startTimestampUpdate = function () {
			var timeout = Number( settings.updateTimestampInterval );
			var updateTimestamp;
			if ( timeout > 0 ) {
				updateTimestamp = function () {
					localStorage.setItem( timestampStorageKey, Date.now() );
				};
				updateTimestamp();
				setInterval( updateTimestamp, timeout );
			}
		};

		/**
		 * Returns the given language in the noralized, locale-like form.
		 *
		 * @param {String}
		 * @return {String}
		 */
		var normalizeLanguage = function ( language ) {

			return language.replace( /-/, '_' );
		};

		/**
		 * Returns the regionless languages of the user that have not been defined before.
		 *
		 * @returns {String[]} The regionless languages of the user not defined before.
		 */
		var userLanguages = function () {

			var browserLanguages;

			if ( $.isArray( allUserLanguages ) ) {
				return allUserLanguages;
			}

			browserLanguages = navigator.languages || [navigator.language] || [navigator.browserLanguage] || [];

			allUserLanguages = browserLanguages.reduce( function ( carry, language ) {

				var index = language.indexOf( '-' );
				var newLanguage;
				if ( index > 0 ) {
					newLanguage = language.substr( 0, index );
					if ( ! ( newLanguage in carry ) ) {
						carry.push( newLanguage );
					}
				}

				return carry;
			} );

			return allUserLanguages;
		};

		/**
		 * Returns the stored languages.
		 *
		 * @returns {String[]}
		 */
		var storedLanguages = function () {

			var languages;

			if ( ! checkTimestamp() ) {
				localStorage.removeItem( languagesStorageKey );

				return [];
			}

			languages = localStorage.getItem( languagesStorageKey );

			return languages ? languages.split( ' ' ) : [];
		};

		/**
		 * Checks if the current site language is stored to not get redirected from.
		 *
		 * @returns {Boolean}
		 */
		var isCurrentLanguageStored = function () {

			return this.getStoredLanguages().includes( this.normalizeLanguage( settings.currentLanguage ) );
		};

		/**
		 * Stores the given language.
		 *
		 * @param {String} language
		 */
		var storeLanguage = function ( language ) {

			var languages = storedLanguages();
			var normalizedLanguage = normalizeLanguage( language );

			if ( normalizedLanguage in languages ) {
				return;
			}

			languages.push( normalizedLanguage );

			localStorage.setItem( languagesStorageKey, languages.join( ' ' ) );
		};

		/**
		 * Returns the noredirect language included in the request, if any.
		 *
		 * @returns {String} Language.
		 */
		var noredirectLanguage = function () {

			var matches;

			if ( typeof noredirect === 'string' ) {
				return noredirect;
			}

			noredirect = '';
			matches = noRedirectRegex.exec( window.location.href );
			if ( matches[ 2 ] ) {
				noredirect = decodeURIComponent( matches[ 2 ].replace( /\+/g, ' ' ) );
			}

			return noredirect;
		};

		/**
		 * Returns the best-matching content language for the given user language.
		 *
		 * @param {String} userLanguage
		 * @return {String}
		 */
		var matchLanguage = function ( userLanguage ) {

			var matched = '';

			if ( settings.urls[ userLanguage ] ) {
				return userLanguage;
			}

			if ( userLanguage.indexOf( '-' ) === -1 ) {
				$.each( settings.urls, function ( langName ) {

					if ( langName.indexOf( userLanguage + '-' ) === 0 ) {
						matched = langName;

						return false;
					}

					return true;
				} );
			}

			return matched;
		};

		/**
		 * Returns the best-matching content language, if any.
		 *
		 * @return {String} The best-matching content language.
		 */
		var findContentLanguage = function () {

			if ( typeof contentLanguage === 'string' ) {
				return contentLanguage;
			}

			contentLanguage = '';
			$.each( userLanguages(), function ( i, val ) {
				var matched = matchLanguage( val );
				if ( matched ) {
					contentLanguage = matched;

					return false;
				}

				return true;
			} );

			return contentLanguage;
		};

		/**
		 * Redirects to the URL according to the given language.
		 *
		 * @param {String} language
		 */
		var doRedirect = function ( language ) {

			var url;
			storeLanguage( language );

			if ( language === settings.currentLanguage ) {
				return;
			}

			url = settings.urls[ language ].replace( /\?.*$/, '' );

			window.location.href = url + '?' + settings.noredirectKey + '=' + normalizeLanguage( language );
		};

		/**
		 * Inizialize the class if necessary.
		 */
		var initialize = function () {
			if ( ! init ) {
				settings = M.RedirectorSettings || {
					currentLanguage: '',
					noredirectKey: '',
					storageLifetime: 300000,
					updateTimestampInterval: 60000,
					urls: {}
				};
				startTimestampUpdate();
				noRedirectRegex = new RegExp( '[?&]' + settings.noredirectKey + '(=([^&#]*)|&|#|$)' );
				init = true;
			}
		};

		/**
		 * Perform the redirect if needed.
		 */
		this.redirect = function () {

			initialize();

			if (
				! settings.noredirectKey
				|| ! settings.urls
				|| isCurrentLanguageStored()
				|| ! userLanguages()
			) {
				return;
			}

			if ( noredirectLanguage() ) {
				storeLanguage( noredirectLanguage() );

				return;
			}

			if ( findContentLanguage() ) {
				doRedirect( findContentLanguage() );
			}
		};
	};

}( jQuery, MultilingualPress, navigator, localStorage, window ) );
( function ( $, M ) {

	'use strict';

	$( function () {

		var redirector = new M.Redirector( 'mlpNoredirectStorage', 'mlpNoredirectStorageTimestamp' );
		redirector.redirect();
	} );

}( jQuery, MultilingualPress ) );
