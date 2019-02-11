<?php
/******************************************************************
 *    Text Domain
 *
 *****************************************************************/
define( 'PRSOTHEMEFRAMEWORK__DOMAIN', 'prso-child-theme-domain' );
load_theme_textdomain( PRSOTHEMEFRAMEWORK__DOMAIN, get_stylesheet_directory() . '/languages' );

//Check for required mu-plugin
add_action( 'init', 'prso_is_framework_loaded', 1 );
function prso_is_framework_loaded() {

	if ( ! defined( 'PRSOPLUGINFRAMEWORK__LOADED' ) ) {
		wp_die( 'Required presso-core mu-plugin is missing' );
	}

}

//Import helpers
add_action( 'init', 'prso_import_theme_helpers', 2 );
function prso_import_theme_helpers() {
	prso_include_file( dirname( __FILE__ ) . '/prso_framework/helpers.php' );
}


/**
 * ADD CUSTOM THEME FUNCTIONS HERE -----
 *
 */

/******************************************************************
 *    Theme Scripts / Styles
 *****************************************************************/

/**
 * prso_child_enqueue_scripts
 *
 * @CALLED BY ACTION 'wp_enqueue_scripts'
 *
 * Enqueue any theme SCRIPTS here
 *
 * @access    public
 * @author    Ben Moody
 */
add_action( 'wp_enqueue_scripts', 'prso_child_enqueue_scripts', 100 );
function prso_child_enqueue_scripts() {

	if ( is_admin() ) {
		return;
	}

	/** example
	 * wp_enqueue_script('fbm-vendor',
	 * get_stylesheet_directory_uri() . '/' . FRONTEND_FOLDER . '/' . SCRIPT_VENDOR_DESKTOP_BUNDLE,
	 * array(),
	 * $wp_version,
	 * true
	 * );
	 **/

	//Remove gutenberg default styles
	wp_deregister_style( 'wp-block-library' );

}

/**
 * prso_theme_localize
 *
 * Add all localized script vars here.
 *
 * @access    public
 * @author    Ben Moody
 */
add_action( 'wp_print_scripts', 'prso_theme_localize', 100 );
function prso_theme_localize() {

	//Init vars
	$handle     = 'prso-theme-app';
	$obj_name   = 'prsoThemeLocalVars';
	$data_array = array();

	/** Cache data for localization **/

	//Set react default config
	$data_array['reactConfig'] = array(
		'restEndpoint' => rest_url( 'wp/v2/posts' ),

		//false/filters config array
		'filters'      => array(
			'categories' => array(
				//select/radio/checkbox
				'type'         => 'select',
				'defaultValue' => 'Select Category',
				'terms'        => array_values( get_terms( 'category' ) ),
			),
			'tags'       => array(
				//select/radio/checkbox
				'type'         => 'select',
				'defaultValue' => 'Select Term',
				'terms'        => array_values( get_terms( 'post_tag' ) ),
			),
		),

		//true/false
		'search'       => true,

		'nonce'                  => wp_create_nonce( 'wp_rest' ),
		'perPage'                => get_option( 'posts_per_page' ),
		'paginationType'         => 'button',

		//Translation strings
		'i18n'                   => array(
			'noResultsText'     => esc_html_x( 'No results found', 'text', PRSOTHEMEFRAMEWORK__DOMAIN ),
			'loadMore'          => esc_html_x( 'Load More', 'button text', PRSOTHEMEFRAMEWORK__DOMAIN ),
			'searchPlaceholder' => esc_html_x( 'Keyword Search', 'input placeholder', PRSOTHEMEFRAMEWORK__DOMAIN ),
			'resetButton'       => esc_html_x( 'Reset', 'input placeholder', PRSOTHEMEFRAMEWORK__DOMAIN ),
		),

		//Array of user browser provided URL params we expect to get when the app loads
		'requestParamsWhitelist' => array(
			'page',
			'per_page',
			'search',
		),
	);

	$data_array['reactConfig'] = apply_filters( 'prso_theme_localize__react_config', $data_array['reactConfig'] );

	wp_localize_script( $handle, $obj_name, $data_array );
}