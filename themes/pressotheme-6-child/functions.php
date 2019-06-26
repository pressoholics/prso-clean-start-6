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

//Remove empty p tags from content
remove_filter('the_content', 'wpautop');

//Remove gravity forms CSS
add_filter('pre_option_rg_gforms_disable_css', '__return_true');

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
	
	//Enqueue react script of archive pages
	if (
		is_home() ||
		is_category() ||
		is_tag() ||
		is_search()
	) {
		wp_enqueue_script( 'prso-theme-react' );
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
* prso_conditional_enqueue_scripts
*
* @CALLED BY ACTION 'get_footer'
*
* Allow scripts to be enqueued based on content conditionals
*
* @access public
* @author Ben Moody
*/
add_action( 'get_footer', 'prso_conditional_enqueue_scripts', 100 );
function prso_conditional_enqueue_scripts() {

	if ( is_admin() ) {
		return;
	}

	//Maybe load owl carousel
	if ( true === prso_page_has_carousel() ) {

		wp_enqueue_script( 'fbm-vendor',
			'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js',
			array( 'prso-theme-app' ),
			'2.3.4',
			true
		);

	}

}

/**
 * prso_page_has_carousel
 *
 * Helper to store a state reflecting if a carousel component has been loaded
 * in a page. Call this function and pass any string/bool as the new_state and
 * then whenever this function is called after it will return true, else if
 * will default to false
 *
 * @param string $new_state
 *
 * @return bool
 * @access public
 * @author Ben Moody
 */
function prso_page_has_carousel( $new_state = null ) {

	static $state;

	if ( ! empty( $new_state ) ) {
		$state = $new_state;
	}

	if ( empty( $state ) ) {
		return false;
	}

	return true;
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
		'nonce'        => wp_create_nonce( 'wp_rest' ),

		//false/filters config array
		'filters'      => false,
		
		//If we need to select any filters on page load. taxonomy => term_id pair
		'selectedFilters' => false,

		//true/false
		'search'       => true,

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
			's',
			'categories',
			'tags',
		),
		
		'queryParams' => array(),
	);

	//If is posts page
	if ( is_home() ) {
		$data_array['reactConfig']['filters'] = array(
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
		);
	}
	
	//Is category archive
	if( is_category() ) {
		$data_array['reactConfig']['filters'] = array(
			'categories' => array(
				//select/radio/checkbox
				'type'         => 'select',
				'defaultValue' => 'Select Category',
				'terms'        => array_values( get_terms( 'category' ) ),
			),
		);
		
		$selected_filter = react_get_queried_object_id();
		if( false !== $selected_filter ) {
			$data_array['reactConfig']['selectedFilters'] = array( 'categories' => $selected_filter );
		}
	}
	
	//Is tag archive
	if( is_tag() ) {
		$data_array['reactConfig']['filters'] = array(
			'tags'       => array(
				//select/radio/checkbox
				'type'         => 'select',
				'defaultValue' => 'Select Term',
				'terms'        => array_values( get_terms( 'post_tag' ) ),
			),
		);
		
		$selected_filter = react_get_queried_object_id();
		if( false !== $selected_filter ) {
			$data_array['reactConfig']['selectedFilters'] = array( 'tags' => $selected_filter );
		}
	}

	//Handle search requests page
	if ( is_search() ) {
		$data_array['reactConfig']['restEndpoint'] = rest_url( 'wp/v2/search' );

		//REST Search endpoint doesn't support filters out of the box
		$data_array['reactConfig']['search']  = false;
		$data_array['reactConfig']['filters'] = false;
	}

	/**
	 * prso_theme_localize__react_config
	 *
	 * Filter the local config object for theme react apps
	 *
	 * @since 6.5
	 *
	 * @see prso_theme_localize()
	 *
	 * @param array $react_config
	 */
	$data_array['reactConfig'] = apply_filters( 'prso_theme_localize__react_config', $data_array['reactConfig'] );

	wp_localize_script( $handle, $obj_name, $data_array );
}

/**
 * prso_preload_webfonts
 *
 * @CALLED BY ACTION 'wp_head'
 *
 * Preload any webfonts. NOTE that really only woff ext should be preloaded as
 *     browsers which support preloading also support WOFF
 *
 * https://developers.google.com/web/fundamentals/performance/optimizing-content-efficiency/webfont-optimization
 *
 * @access public
 * @author Ben Moody
 */
add_action( 'wp_head', 'prso_preload_webfonts', 1 );
function prso_preload_webfonts() {

	//vars
	$defaults = [ 'ext' => 'woff' ];
	$fonts    = [
		[ 'path' => 'GTWalsheimPro-Bold/GTWalsheimPro-Bold' ],
		[ 'path' => 'GTWalsheimPro-Bold/GTWalsheimPro-Regular' ],
	];

	if ( empty( $fonts ) ) {
		return;
	}

	?>
	<!-- preload fonts -->
	<?php

	foreach ( $fonts as $font_params ) {

		$font_params = wp_parse_args( $font_params, $defaults );
		$font_path   = $font_params['path'];

		$font_params['ext'] = explode( ',', $font_params['ext'] );

		foreach ( $font_params['ext'] as $font_file_ext ) {
			?>
			<link rel="preload"
				  href="<?php echo get_stylesheet_directory_uri(); ?>/dist/assets/webfonts/<?php echo esc_attr( $font_path ); ?>.<?php echo esc_attr( $font_file_ext ); ?>"
				  as="font"
				  crossorigin="anonymous">
			<?php
		}
	}

	?>
	<!-- END preload fonts -->
	<?php

}