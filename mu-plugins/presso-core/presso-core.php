<?php
/*
Plugin Name: Presso Core Plugin
Version: 1.0
Author URI: http://Pressoholics.com
Plugin URI: http://Pressoholics.com
Description:  Plugin to handle all Pressoholic theme business logic.
Author: Ben Moody
*/

/**
 * Presso Plugin
 *
 * Plugin provides a framework to handle all business logic for Pressohoilc themes
 *
 * PHP versions 4 and 5
 *
 * @copyright     Pressoholics (http://pressoholics.com)
 * @link          http://pressoholics.com
 * @package       pressoholics theme framework
 * @since         Pressoholics v 1.0
 */

/**
* Call method to boot core framework
*
*/		
if( file_exists( dirname(__FILE__) . '/bootstrap.php' ) ) {

	if( !class_exists('PrsoCoreBootstrap') ) {
		
		/**
		* Include config file to set core definitions
		*
		*/
		prso_include_file( dirname(__FILE__) . '/config.php' );
		
		if( class_exists('PrsoCoreConfig') ) {
			
			new PrsoCoreConfig();
			
			//Core loaded, load rest of plugin core
			prso_include_file( dirname(__FILE__) . '/bootstrap.php' );

			//Instantiate bootstrap class
			if( class_exists('PrsoCoreBootstrap') ) {
				new PrsoCoreBootstrap();
				define( 'PRSOPLUGINFRAMEWORK__LOADED', true );
			}
			
		}
		
	}
	
}

/**
 * prso_include_file
 *
 * Helper to test file include validation and include_once if safe
 *
 * @param    string    Path to include
 *
 * @return    mixed    Bool/WP_Error
 * @access    public
 * @author    Ben Moody
 */
function prso_include_file( $path ) {

	//Check if a valid path for include
	if ( validate_file( $path ) > 0 ) {

		//Failed path validation
		return new WP_Error(
			'fbbrcpl_include_file',
			'File include path failed path validation',
			$path
		);

	}

	include_once( $path );

	return true;
}

/**
 * prso_include_all_files
 *
 * Helper to autoload all php files found in the supplied path
 *
 * @param string $path_to_files
 *
 * @access public
 * @author Ben Moody
 */
function prso_include_all_files( $path_to_files = null ) {

	//vars
	$pathnames = array();

	if ( empty( $path_to_files ) ) {
		return;
	}

	//Get pathnames of files in destination
	$pathnames = glob( "{$path_to_files}/*.php" );

	if ( false === $pathnames ) {
		return;
	}

	//Loop and include each found file
	foreach ( $pathnames as $file_path ) {

		prso_include_file( $file_path );

	}

	return;
}

/**
* Debug helper
* Prints out debug information about given variable.
*
* Only runs if wp debugging mode if set to true
*
* @param boolean $var Variable to show debug information for.
* @param boolean $showHtml If set to true, the method prints the debug data in a screen-friendly way.
*/	
function prso_debug( $var, $showHtml = FALSE, $showFrom = TRUE ) {
	
	//Init vars
	$calledFrom = NULL;
	
	if( defined('WP_DEBUG') && defined('ABSPATH') && WP_DEBUG === TRUE ) {
		
		if ($showFrom) {
			$calledFrom = debug_backtrace();
			echo '<strong>' . substr(str_replace(ABSPATH, '', $calledFrom[0]['file']), 0) . '</strong>';
			echo ' (line <strong>' . $calledFrom[0]['line'] . '</strong>)';
		}
		
		echo "\n<pre class=\"prso-debug\">\n";

		$var = print_r($var, true);
		
		if ($showHtml) {
			$var = str_replace('<', '&lt;', str_replace('>', '&gt;', $var));
		}
		
		echo $var . "\n</pre>\n";
		
	}
	
}

/**
* prso_memory
* Prints out debug information on memory usage
*
*/
//add_action( 'shutdown', 'prso_memory', 999 );
function prso_memory( $text = NULL ) {
	prso_debug( "{$text} Peak: " . memory_get_peak_usage() );
}