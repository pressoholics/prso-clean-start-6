<?php
/**
 * Config
 *
 * Sets all constant definitions for the Pressoholics Core theme plugin framwork
 *
 * PHP versions 4 and 5
 *
 * @copyright     Pressoholics (http://pressoholics.com)
 * @link          http://pressoholics.com
 * @package       pressoholics theme framework
 * @since         Pressoholics v 0.1
 */
class PrsoCoreConfig {
	
	
	//***** CHANGE PLUGIN OPTIONS HERE *****//
	
	/**
	* VERY IMPORTANT
	*
	* Define a unique slug to prepend to all wordpress database keys to ensure
	* there are no conflicts
	*
	* Effects Class Names and Keys for saved options
	*
	* Be sure to Prepend all Class names with this slug (convert to CamelCase - E.G. foo_bar_ => FooBar)
	*
	* If you need a string to be unique say with an option key call $this->get_slug('your_string'), it will return
	* your_string with the plugin slug prepended to it.
	*
	*/
	protected $plugin_slug = 'prso_core_';
	
	/**
 	* Admin page setting vars: Admin Parent Page Settings...
 	*
 	*/
 	protected $page_title_parent 	= 'Pressoholics Framework Core Options'; //Cache parent page title string
 	protected $menu_title_parent 	= 'Presso Framework'; //Cache parent menu title string
 	protected $capability_parent	= 'administrator'; //Cache parent user capability
 	protected $menu_slug_parent		= 'prso_core_admin'; //Cache parent menu slug - prepend prso unqiue slug key
 	protected $icon_url_parent		= NULL; //Cache parent menu icon url
 	protected $position_parent		= NULL; //Cache parent menu postition
 	
 	//Plugin specific options
	protected $plugin_options_db_slug 	= 'prso_core_data'; //The unique slug used to identify this plugin - also used to store and indentify plugin option data
 	
	//***** END -- PLUGIN OPTIONS *****//
	
	
	
	
	
	
	
	
	
	/**
	* The full path to the directory which holds "presso_framework", WITHOUT a trailing DS.
	*
	*/
	protected $plugin_root = NULL;
	protected $plugin_filename = NULL;
	
	/**
	* The full path to the directory which holds "models", WITHOUT a trailing DS.
	*
	*/
	protected $plugin_models = NULL;
	
	/**
	* The full path to the directory which holds "plugins", WITHOUT a trailing DS.
	*
	*/
	protected $plugins_folder = NULL;
	
	/**
	* The full path to the directory which holds "views", WITHOUT a trailing DS.
	*
	*/
	protected $plugin_views = NULL;
	protected $plugin_request_router_views_dir_name = 'request_router';
	
	/**
	* Unique slug prepended to all class names, based on var $plugin_slug set at top of this file
	*
	*/
	protected $plugin_class_slug = NULL;
	
	function __construct() {
		
		//Set plugin root
		$this->plugin_root = dirname(__FILE__);
		
		//Set plugin filename
		$this->plugin_filename = __FILE__;
		
		//Set plugin models dir
		$this->plugin_models = $this->plugin_root . '/models';
		
		//Set plugin plugins folder
		$this->plugins_folder = $this->plugin_root . '/plugins';
		
		//Set plugin views folder
		$this->plugin_views = $this->plugin_root . '/views';
		
		//Set plugin Class slug to be prepended to class names making them unique
		$this->plugin_class_slug = str_replace(' ', '', ucwords(str_replace('_', ' ', $this->plugin_slug)));
			
	}
	
}