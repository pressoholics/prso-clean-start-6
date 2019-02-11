<?php
/**
 * Database driven App Functions
 *
 * Contains functions that are driven by settings in the theme plugin admin panel.
 *
 * Use the Wordpress API call's within __construct to call your methods:
 *	//Action hook example
 *	add_action( 'init', array( &$this, 'test' ) ); 
 *
 * Contents:
 * 
 *
 */
class PrsoCoreFunctions extends PrsoCoreAppController {
	
	function __construct() {
	
		//Ensure vars set in config are available
 		//parent::__construct();
 		
	}
	
}