<?php
/*
Author: Benjamin Moody
URL: htp://www.BenjaminMoody.com
Version: 6.5.3
*/

/******************************************************************
 * 	Version
 *
 *****************************************************************/
 define( 'PRSOTHEMEFRAMEWORK__VERSION', '6.5.3' );

/******************************************************************
 * 	Text Domain
 *
 *****************************************************************/
 if( !defined('PRSOTHEMEFRAMEWORK__DOMAIN') ) {
	 define( 'PRSOTHEMEFRAMEWORK__DOMAIN', 'prso-theme-domain' );
	 load_theme_textdomain( PRSOTHEMEFRAMEWORK__DOMAIN, get_stylesheet_directory() . '/languages' );
 }

/**
* ADD CUSTOM THEME FUNCTIONS HERE -----
*
*/

/**
* prso_get_file_version
* 
* Helper to dynamically generate a file version for enqueued scripts/styles based
* on the filemtime()
* 
* NOTE that the param should be the path to the enequeud file from the theme root
* note it should start with a slash e.g. '/styles.css'
*
*
* @param	string	$file_path_from_theme_dir
* @access 	public
* @author	Ben Moody
*/
function prso_get_file_version( $file_path_from_theme_dir = NULL ) {
	
	return filemtime( get_stylesheet_directory() . $file_path_from_theme_dir );
	
}

add_filter('get_archives_link', 'archive_count_no_brackets');
add_filter('wp_list_categories', 'archive_count_no_brackets');
function archive_count_no_brackets($links) {
	
	//Wrap post count in span for styling
	$links = str_replace('(', '</a>&nbsp;<span class="prso-post-count">(', $links);
	$links = str_replace(')', ')</span>', $links);

	return $links;
	
}

function prso_get_theme_retina_images( $filename = NULL, $alt = NULL, $class = NULL ) {
	
	return PrsoThemeFunctions::get_theme_retina_images( $filename, $alt, $class );
	
}

function prso_wp_get_image( $image_data = NULL, $alt = NULL, $class = NULL ) {
	
	return PrsoThemeFunctions::get_wp_image_retina_html( $image_data , $alt , $class );
	
}

/**
* PRSO THEME FRAMEWORK -- DO NOT REMOVE!
* Call method to boot core framework
*
*/	

/**
* Include config file to set core definitions
*
*/
$config_path = get_template_directory() . '/prso_framework/config.php';

//Search for config in child theme
if( file_exists( get_stylesheet_directory() . '/prso_framework/config.php' ) ) {
	$config_path = get_stylesheet_directory() . '/prso_framework/config.php';
}

if( file_exists($config_path) ) {
	
	include( $config_path );
	
	if( class_exists('PrsoThemeConfig') ) {
		
		new PrsoThemeConfig();
		
		//Core loaded, load rest of plugin core
		include( get_template_directory() . '/prso_framework/bootstrap.php' );

		//Instantiate bootstrap class
		if( class_exists('PrsoThemeBootstrap') ) {
			new PrsoThemeBootstrap();
		}
		
	}
	
}

/**
* prso_theme_localize
* 
* Add all localized script vars here.
* 
* @access 	public
* @author	Ben Moody
*/
function prso_parent_theme_localize() {
	
	//Init vars
	global $post;
	$handle 	= 'prso-theme-app';
	$obj_name	= 'prsoParentThemeLocalVars';
	$data_array = array();
	
	if( !is_admin() ) {
		/** Cache data for localization **/
		
		if( isset($post->ID) ) {
			$data_array['currentPostID'] = $post->ID;
		}
			
		wp_localize_script( $handle, $obj_name, $data_array );
	}
	
}
//add_action( 'wp_print_scripts', 'prso_parent_theme_localize', 100 );