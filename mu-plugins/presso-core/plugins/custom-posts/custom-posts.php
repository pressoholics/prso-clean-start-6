<?php
/**
 * custom-posts.php
 *
 * Register Custom post types and Taxonomies for use with current theme
 *
 * Use http://generatewp.com/ to generate code :)
 *
 * @author    Ben Moody
 */

add_action( 'init', 'prso_init_custom_post_tax_filter_class', 1 );
function prso_init_custom_post_tax_filter_class() {

	//Init vars
	$file_path = plugin_dir_path( __FILE__ ) . 'class.custom-post-tax-filter.php';

	prso_include_file( $file_path );
}

/**
 * Init theme posts types
 *
 * Include the post type files for you theme here to set them up
 *
 * See the template file in theme-post-types folder for example on how to use
 * this
 *
 * @author    Ben Moody
 */
add_action( 'plugins_loaded', 'prso_init_theme_post_types' );
function prso_init_theme_post_types() {

	//Init vars
	$file_path = plugin_dir_path( __FILE__ ) . 'theme-post-types';

	prso_include_all_files( $file_path );

}

/**
 * Init theme taxonomies
 *
 * Include the taxonomies files for you theme here to set them up
 *
 * See the template file in theme-taxonomies folder for example on how to use
 * this
 *
 * @author    Ben Moody
 */
add_action( 'plugins_loaded', 'prso_init_theme_taxonomies' );
function prso_init_theme_taxonomies() {

	//Init vars
	$file_path = plugin_dir_path( __FILE__ ) . 'theme-taxonomies';

	prso_include_all_files( $file_path );

}

/**
 * Init theme custom fields -- PRO VERSION
 *
 * Include adv custom field export
 *
 * @author    Ben Moody
 */
add_action( 'init', 'prso_init_acf_pro_custom_fields', 1 );
function prso_init_acf_pro_custom_fields() {

	//Include hardcoded fields
	prso_acf_custom_fields();

	//Disable backend user options
	//add_filter('acf/settings/show_admin', '__return_false');

}

/**
 * prso_acf_custom_fields
 *
 * @CALLED BY ACTION 'init'
 *
 * Include hardcoded fields file
 *
 * @access public
 * @author Ben Moody
 */
function prso_acf_custom_fields() {

	$file_path = plugin_dir_path( __FILE__ ) . 'custom-fields.php';

	prso_include_file( $file_path );

}
