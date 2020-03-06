<?php
/**
 * PrsoCoreBootstrap
 *
 * Instantiates all required classes for the Pressoholics Core plugin framework
 *
 * E.G Instantiates models, views
 *
 * PHP versions 4 and 5
 *
 * @copyright     Pressoholics (http://pressoholics.com)
 * @link          http://pressoholics.com
 * @package       pressoholics theme framework
 * @since         Pressoholics v 0.1
 */
 
 class PrsoCoreBootstrap extends PrsoCoreConfig {
 	
 	private $models_scan 	= array(); //Cache all models in models dir
 	private $plugins_scan 	= array(); //Cache all plugins in plugins dir
 	private $views_scan		= array(); //Cache all views in views dir (does not include views for request_router)
 	
 	function __construct( $args = array() ) {
 		//Ensure vars set in config are available
 		parent::__construct();
 		
 		//Boot plugin
 		$this->boot( $args );
 	}
 	
 	/**
	* boot
	* 
	* Calls methods to scan models dir and load instances of all valid models found
	* 
	*/
 	public function boot( $args = array() ) {
 		
 		//Load app controller
 		if( $this->load_app_controller() ) {
 			
 			//Scan the models dir
	 		$this->models_scan = $this->scan_models();
	 		
	 		//Scan the plugins dir
	 		$this->plugins_scan = $this->scan_plugins();
	 		
	 		//Instantiate models
	 		$this->load_models();
	 		
	 		//Load general app functions
	 		$this->load_app_functions();
	 		
	 		//Load third party plugins
	 		$this->load_plugins();
 			
 			//Register PrsoCore as loaded
 			define( 'PrsoCoreActive', true );
 			
 		} else {
		
			//Error loading app controller
			wp_die( wp_sprintf( '%1s: ' . __( 'Sorry, there was an error loading the Prso Core Plugin.', 'prso_core' ), __FILE__ ) );
			
		}
 		
 	}
 	
 	private function load_app_controller() {
		
		
		//include_once admin view file
		include_once( $this->plugin_root . '/app_controller.php' );
		
		//Instantiate class
		$class_name = $this->plugin_class_slug . 'AppController';
		
		new $class_name();
			
		return true;
 	}
 	
 	/**
	* scan_models
	* 
	* Scans theme framework models dir, caches and dir found in
	* $this->models_scan array.
	*
	* Returns false on error
	* 
	*/
 	private function scan_models() {
 			
 		//Init vars
 		$result = false;
 		$scan	= null; //Cache result of dir scan
 		
 		$result = array(
		    'minify',
		);
 		
 		return $result;
 	}
 	
 	/**
	* scan_plugins
	* 
	* Scans theme framework plugins dir, caches any dir found in
	* $this->plugins_scan array.
	*
	* Returns false on error
	* 
	*/
 	private function scan_plugins() {
 			
 		//Init vars
 		$result = false;
 		$scan	= null; //Cache result of dir scan
 		
 		$result = array(
		    'custom-posts',
		    'rest-api',
		    'multilingual-press',
		    'woocommerce',
		    'wpcache',
		    'acf',
		    'gutenberg',
		    'i18n',
		);
 		
 		return $result;
 	}
 	
 	/**
	* load_models
	* 
	* Checks to see if any valid models where found in $this->models_scan
	* If the helper file exsists an instance is created and the helper object is
	* stored in a global var which matches the following convension:
	*
	* 'Prso' . Helpername(uppercase)  e.g  PrsoHtml
	*
	* Call helper methods in wordpress template by:
	* Global PrsoHelpername;
	* $PrsoHelpername->method();
	* 
	*/
 	private function load_models() {
 		
 		if( $this->models_scan && is_array( $this->models_scan ) ) {
 			
 			//Loop the result of the models dir scan and try to instantiate each model class
 			foreach( $this->models_scan as $model ) {
 				
 				//Ucase first letter of model name to fit convension
				$model_filename	= $model;
				$model			= ucfirst($model);
				$model_class 	= $this->plugin_class_slug . $model . 'Model';
				$model_global	= $this->plugin_class_slug . ucfirst($model); //Add a unique var name to avoid conflicts
 				$model_path		= $this->plugin_models . '/' . $model_filename . '/' . $model_filename . '.php';
 				
				//include_once the model file
			    prso_include_file( $model_path );
				
				new $model_class;
	 					
 			}
 			
 		}
 		
 	}
 	
 	/**
	* load_app_functions
	* 
	* Loads the app_functions class, which contains all custom methods for this app
	* 
	*/
 	private function load_app_functions() {
 		
 		//Init vars
 		$args 	= array(
			'plugin_root_dir' 	=> $this->plugin_root,
			'plugin_class_slug'	=> $this->plugin_class_slug
		);
 		
 		//PrsoCoreAppController::load_plugin_functions( $args );
 		do_action( 'prso_core_load_plugin_functions', $args );
 		
 	}
 	
 	/**
	* load_plugins
	* 
	* Checks to see if any valid plugins where found in $this->plugins_scan
	*
	* 
	*/
 	private function load_plugins() {
 			
		//Loop the result of the plugins dir scan and try to include_once the plugin file
		foreach( $this->plugins_scan as $plugin ) {

		//include_once the plugin file
			prso_include_file( $this->plugins_folder . '/' . $plugin . '/' . $plugin . '.php' );
			
		}
 		
 	}
 	
 }