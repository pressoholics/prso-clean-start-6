<?php
/**
* Prsoi18n
*
* Handle i18n translation
*
* @access public
* @author Ben Moody
*/
class Prsoi18n {

	const STRING_CACHE_KEY = 'prso-i18n-cache';

	private $cached_strings;

	function __construct() {

		add_action('init', array($this, 'init_i18n'));

	}

	/**
	* init_i18n
	*
	* @CALLED BY ACTION 'init'
	*
	* @access public
	* @author Ben Moody
	*/
	public function init_i18n() {

		$this->cached_strings = $this->get_i18n_string_transient();

		add_filter( 'gettext_with_context', array($this, 'i18n_string_translation'), 900, 4 );

	}

	/**
	 * i18n_string_translation
	 *
	 * @CALLED BY FILTER 'gettext_with_context'
	 *
	 * Hook into WP i18n system and use context to try and find an acf option with the same key, replace if valid option value found.
	 *
	 * @access public
	 * @author Ben Moody
	 */
	public function i18n_string_translation( $translation, $text, $context, $domain ) {

		if( $domain !== PRSOTHEMEFRAMEWORK__DOMAIN ) {
			return $translation;
		}

		if (strpos($context, 'prso-i18n') === false) {
			return $translation;
		}

		$acf_string = $this->add_to_i18n_string_cache( $context );

		if( !$acf_string ) {
			return $translation;
		}

		return $acf_string;
	}

	/**
	* add_to_i18n_string_cache
	*
	* @CALLED BY $this->i18n_string_translation()
	*
	* Starts the i18n string cache process. If the transient cache is empty
	 * it will start the cache array, getting the first ACF option value passed to it via context and stashing it in the cache array
	 * it then updates the cache transient with the new array.
	 *
	 * If we find the string in the cache via it's context just return it
	 *
	 * if the string is not in the cache, get it's ACF value, put it in the cache array (memory) then stash in the transient
	*
	* @param string $context
	* @return mixed string/bool (false on error)
	* @access private
	* @author Ben Moody
	*/
	private function add_to_i18n_string_cache( $context ) {

		$cached_strings = $this->cached_strings;

		if( false === $cached_strings ) {

			//Try and fetch translation based on context
			$acf_string = get_field( $context, 'option' );

			if( !$acf_string ) {
				return false;
			}

			//start cache
			$cached_strings = array(
				$context => $acf_string
			);

			//Cache in transient
			$this->set_i18n_string_transient( $cached_strings );

			return $acf_string;

		} elseif( is_array($cached_strings) && isset( $cached_strings[ $context ] ) ) {

			return $cached_strings[ $context ];

		} elseif( is_array($cached_strings) ) {

			//Try and fetch translation based on context
			$acf_string = get_field( $context, 'option' );

			if( !$acf_string ) {
				return false;
			}

			//Add to cache
			$cached_strings[ $context ] = $acf_string;

			//Cache in transient
			$this->set_i18n_string_transient( $cached_strings );

			return $acf_string;

		}

		return;
	}

	private function set_i18n_string_transient( $value ) {

		$this->cached_strings = $value;

		set_transient( self::STRING_CACHE_KEY, $value );

	}

	private function get_i18n_string_transient() {

		return get_transient( self::STRING_CACHE_KEY );
	}
	
}
new Prsoi18n();