<?php

class PrsoCoreAppController extends PrsoCoreConfig {
	
	function __construct() {
		
		//Ensure vars set in config are available
 		parent::__construct();
		
		//Add wordpress action hook for load_plugin_views() so that other plugins can use the core method
		//$this->add_action( 'prso_core_load_plugin_views', 'load_plugin_views', 1, 1 );
		
		//Add wordpress action hook for load_plugin_functions() so that other plugins can use the core method
		$this->add_action( 'prso_core_load_plugin_functions', 'load_plugin_functions', 1, 1 );
		
		//Add wordpress action hook for request_router() so that other plugins can use the core method
		//$this->add_action( 'prso_core_request_router', 'request_router', 1, 1 );
		
		//Add wordpress action hook for plugin_redirect() so that other plugins can use the core method
		$this->add_action( 'prso_core_plugin_redirect', 'plugin_redirect', 10, 2 );
		
		//Add wordpress filter hook for get_slug() so that other plugins can use the core method
		//$this->add_filter( 'prso_core_get_slug', 'get_slug', 10, 4 );
		
		//Add wordpress filter hook for load_plugin_app_controller() so that other plugins can use the core method
		$this->add_filter( 'prso_core_load_plugin_app_controller', 'load_plugin_app_controller', 10, 4 );
		
		//Add wordpress filter hook for scan_plugin_views() so that other plugins can use the core method
		//$this->add_filter( 'prso_core_scan_plugin_views', 'scan_plugin_views', 10, 4 );
		
		//Add wordpress filter hook for form_action() so that other plugins can use the core method
		//$this->add_filter( 'prso_core_form_action', 'form_action', 10, 4 );
		
		//Add wordpress filter hook for get_options() so that other plugins can use the core method
		//$this->add_filter( 'prso_core_get_options', 'get_plugin_options', 10, 3 );
		
		//Add wordpress filter hook for render_plugin_view() so that other plugins can use the core method
		//$this->add_filter( 'prso_core_render_plugin_view', 'render_plugin_view', 10, 2 );
		
		//Add wordpress filter hook for validate_plugin_fields() so that other plugins can use the core method
		//$this->add_filter( 'prso_core_validate_plugin_fields', 'validate_plugin_fields', 10, 3 );
		
	}
	
	/**
	* add_action
	* 
	* Helper to deal with Wordpress add_action requests. Checks to make sure that the action is not
	* duplicated if a class is instantiated multiple times.
	* 
	* @access 	protected
	* @author	Ben Moody
	*/
	public function add_action( $tag = NULL, $method = NULL, $priority = 10, $accepted_args = NULL ) {
		
		if( isset($tag,$method) ) {
			//Check that action has not already been added
				add_action( $tag, array(&$this, $method), $priority, $accepted_args );
		}
		
	}
	
	/**
	* add_filter
	* 
	* Helper to deal with Wordpress add_filter requests. Checks to make sure that the filter is not
	* duplicated if a class is instantiated multiple times.
	* 
	* @access 	protected
	* @author	Ben Moody
	*/
	private function add_filter( $tag = NULL, $method = NULL, $priority = 10, $accepted_args = NULL ) {
		
		if( isset($tag,$method) ) {
			//Check that action has not already been added
				add_filter( $tag, array(&$this, $method), $priority, $accepted_args );
		}
		
	}
	
	/**
	* get_slug
	* 
	* Little helper to prepend any slug vars with the framework slug constant PRSO_SLUG
	* helps to avoid any name conflicts with options slugs
	* 
	* @access 	protected
	* @author	Ben Moody
	*/
 	public function get_slug( $slug = NULL, $var = NULL, $plugin_slug = NULL, $PluginObj = NULL ) {
 		
 		//Is this a request from a plugin outside prso_core?
 		if(isset( $plugin_slug, $var )) {
 			
 			if(isset( $PluginObj ) && is_object( $PluginObj )) {
 				
 				if(isset($PluginObj->$var)) {
 					$slug = $plugin_slug . $PluginObj->$var;
 				} else {
 					//Return a new version of $var with prso slug appended
 					$slug = $plugin_slug . $var;
 				}
 				
 			} else {
 				//Return a new version of $var with prso slug appended
 				$slug = $plugin_slug . $var;
 			} 
 			
 		} elseif(isset( $this->plugin_slug, $var )) {
 			
 			$plugin_slug = $this->plugin_slug;
 			
 			if(isset($this->$var)) {
 				$slug = $plugin_slug . $this->$var;
 			} else {
 				//Return a new version of $var with prso slug appended
 				$slug = $plugin_slug . $var;
 			}
 			
 		}
 		
 		return $slug;
 	}
 	
 	/**
	* load_plugin_app_controller
	*
	* Called using custom wordpress filter hook 'prso_core_load_plugin_app_controller', 
	* Confirms that a plugin based on the Prso Plugin Framework has a valid app controller
	* If the app controller is found, it tries to include and instantiate
	*
	* @param 	array $args - contains required args, see $defaults for example
	* @return	bool $result - false if app controller could not be loaded in provided plugin root dir
	*/
	public function load_plugin_app_controller( $result = false, $args = array() ) {
		
		//Init vars
		$class_name = NULL;
		$defaults 	= array(
			'plugin_root_dir' 	=> NULL,
			'plugin_class_slug'	=> NULL
		);
		
		$args = wp_parse_args( $args, $defaults );
		
		extract($args);
		
		//Find user function class and create instance
		if( isset( $plugin_root_dir, $plugin_class_slug ) && file_exists( $plugin_root_dir . '/app_controller.php' ) ) {
		
			//Include admin view file
			include_once( $plugin_root_dir . '/app_controller.php' );
			
			//Instantiate class
			$class_name = $plugin_class_slug . 'AppController';
			if( class_exists( $class_name ) ) {
				new $class_name();
				$result = true;
			}
		
		}
		
		return $result;
	}
 	
 	/**
	* scan_plugin_views
	*
	* Called using custom wordpress filter hook 'prso_core_scan_plugin_views', 
	* Scans the plugin views dir provided in $args and caches each php file found in $view_files
	*
	* @param $args array - contains required args, see $defaults for example
	*/
	public function scan_plugin_views( $view_files = array(), $args = array() ) {
		
		//Init vars
		$scan		= NULL; //Cache result of dir scan
		$file		= NULL;
		$view_files = array();
		$defaults 	= array(
			'plugin_views_dir' 			=> NULL,
			'plugin_child_views_dir'	=> NULL
		);
		
		$args = wp_parse_args( $args, $defaults );
		
		extract( $args );
		
		//Support child themes by first checking for a child theme views directory
		if( isset($plugin_child_views_dir) ) {
			
			//Scan for files in child theme's prso_framwork folder
			$view_files = $this->return_view_files( $plugin_child_views_dir );
			
			//See if we found any view files in the child theme
			if( empty($view_files) ) {
				
				//No files found or folder does not exsist in child theme, run on parent
				$view_files = $this->return_view_files( $plugin_views_dir );
				
			}
			
		} elseif( isset($plugin_views_dir) && $plugin_child_views_dir === NULL ) {
			
			//Scan for files in main "parent" directory
			$view_files = $this->return_view_files( $plugin_views_dir );
			
		}
		
		return $view_files;
	}
 	
 	/**
	* return_view_files
	*
	* Helper to find any php files in a directory, 
	* Scans the $scan_dir provided and returns an array of each php file found
	*
	* @param $scan_dir string - path to directory you wish to scan for php files
	*/
 	private function return_view_files( $scan_dir = NULL ) {
	 	
	 	//Init vars
	 	$scan		= NULL; //Cache result of dir scan
		$file		= NULL;
	 	$results	= array();
	 	
	 	if( isset($scan_dir) && file_exists($scan_dir) ) {
		 	
		 	$scan = scandir( $scan_dir );
 			
 			//Check dir is valid
 			if( $scan !== FALSE ) {
	 			
	 			//Loop scandir result and store any found dirs in $result
	 			foreach( $scan as $file ) {
	 				if( !empty($file) && is_string($file) ) {
	 					
	 					//Confirm $file is a php file
	 					if( preg_match('/\.php$/i', $file) ) {
	 						$results[] = $file;
	 					}
	
	 				}
	 			}
	 			
 			}
		 	
	 	}
	 	
	 	return $results;
 	}
 	
 	/**
	* load_plugin_views
	*
	* Called using custom wordpress action hook 'prso_core_load_plugin_views', 
	* Loops all files found in the plugin's view dir and tries to include and instatiate each
	*
	* @param $args array - contains required args, see $defaults for example
	*/
	public function load_plugin_views( $args = array() ) {
		
		//Init vars
		$defaults = array(
			'views_scan' 				=> array(),
			'plugin_class_slug'			=> NULL,
			'plugin_views_dir'			=> NULL,
			'plugin_child_views_dir'	=> NULL
		);
		
		$args = wp_parse_args( $args, $defaults );
		
		extract( $args );
		
		if( is_array( $views_scan ) && !empty( $views_scan ) && isset( $plugin_class_slug, $plugin_views_dir ) && is_admin() ) {
 			
 			//Loop the result of the models dir scan and try to instantiate each model class
 			foreach( $views_scan as $view ) {
 				
 				//Ucase first letter of model name to fit convension
 				$view_explode = explode('.', $view); //Extract view name string from .php extension
 				$view_explode[0] = str_replace( '-', ' ', $view_explode[0] ); //Convert filenames separtated with - to spaces
 				$view_explode[0] = str_replace( '_', ' ', $view_explode[0] ); //Convert filenames separtated with _ to spaces
 				$view_explode[0] = ucwords( $view_explode[0] ); //Camelcase the view name
 				$view_explode[0] = str_replace( ' ', '', $view_explode[0] ); //Remove spaces added to filenames separated with - or _
				$view_class = $plugin_class_slug . $view_explode[0] . 'View';
 				
 				//Support for Child Themes prso_framwork folder by searching for files here if dir is provided
 				if( isset($plugin_child_views_dir) && file_exists( $plugin_child_views_dir . '/' . $view ) ) {
	 				
 					//include_once admin view file
					include_once( $plugin_child_views_dir . '/' . $view );
					
					//Instantiate class
					if( class_exists( $view_class ) ) {
						new $view_class();
					}
	 				
 				} else {
	 				
	 				//Check if view file exsists
	 				if( file_exists( $plugin_views_dir . '/' . $view ) ) {
	 					
	 					//include_once admin view file
						include_once( $plugin_views_dir . '/' . $view );
						
						//Instantiate class
						if( class_exists( $view_class ) ) {
							new $view_class();
						}
	 					
	 				}
	 				
 				}
 				
 			}
 			
 		}
		
	}
	
	/**
	* load_plugin_functions
	*
	* Called using custom wordpress action hook 'prso_core_load_plugin_functions', 
	* Confirms that a plugin based on the Prso Plugin Framework has a valid functions.php file
	* If the functions file is found, it tries to include and instantiate
	*
	* @param $args array - contains required args, see $defaults for example
	*/
	public function load_plugin_functions( $args = array() ) {
		
		//Init vars
		$class_name	= NULL;
		$defaults 	= array(
			'plugin_root_dir' 	=> NULL,
			'plugin_class_slug'	=> NULL
		);
		
		$args = wp_parse_args( $args, $defaults );
		
		extract($args);
		
		if( isset( $plugin_root_dir, $plugin_class_slug ) ) {
			
			//Find user function class and create instance
			if( file_exists( $plugin_root_dir . '/functions.php' ) ) {
			
				//Include admin view file
				include_once( $plugin_root_dir . '/functions.php' );
				
				//Instantiate class
				$class_name = $plugin_class_slug . 'Functions';
				if( class_exists( $class_name ) ) {
					new $class_name();
				}
			
			}
			
		}
		
	}
 	
 	/**
	* Request Router
	*
	* Detects any plugin specific controller and action requested in the admin url
	* finds the action and calls the specific method passing any params.
	* 
	* Other plugins can make use of this method by hooking into it with:
	* do_action( 'prso_core_request_router', $args );
	*
	* @param $args array - contains required args, see $defaults for example
	*
	*/
	public function request_router( $args = array() ) {
		
		//Init vars
		$controller_key 		= 'controller';
		$action_key				= 'action';
		$data					= $_GET;
		
		$defaults = array(
			'plugin_menu_slug' 		=> NULL,
			'plugin_class_slug'		=> NULL,
			'plugin_views_dir'		=> NULL
		);
		
		$args = wp_parse_args( $args, $defaults );
		
		extract( $args );
		
		//First check to see if a controller and action has been provided
		if( isset($plugin_menu_slug, $plugin_class_slug, $plugin_views_dir) && array_key_exists('controller', $data) && array_key_exists('action', $data) ) {
			
			//Run a nonce check on the get data
			$page_slug 		= $this->get_slug( $plugin_menu_slug );
			$action			= $data['action'];
			$controller		= $data['controller'];
			$nonce_action 	= "{$plugin_menu_slug}-{$action}_{$controller}";
			$nonce			= $_REQUEST['_wpnonce'];
			
			check_admin_referer( $nonce_action );
			
			//Call action to find and load appropriate view
			if( !$this->load_view( $data['controller'], $data['action'], $data, $args ) ) {
				//Critical error
				wp_die( __( "There was an error while trying to load the plugin index controller." ) );
			}
		} elseif( isset($plugin_menu_slug, $plugin_class_slug, $plugin_views_dir) ) {
			
			//Load index page template
			if( !$this->load_view( 'index', 'index', $data, $args ) ) {
				//Critical error
				wp_die( __( "There was an error while trying to load the plugin index controller." ) );
			}
			return;
		}
		
	}
	
	/**
	* load_view
	*
	* Finds a views controller file, creates and instance of the class, and triggers the action method
	* Returns false on error.
	*
	*/
	private function load_view( $controller = 'index', $action = 'index', $data = null, $args = array() ) {
		
		//Init vars
		$view_class_slug 	= NULL;
		$plugin_views_dir	= NULL;
		$controller			= strtolower( $controller );
		$file_path			= NULL;
		$result				= false;
		
		if(isset( $args['plugin_class_slug'], $args['plugin_views_dir'] )) {
			$view_class_slug = $args['plugin_class_slug'];
			$plugin_views_dir = $args['plugin_views_dir'];
		}
		
		//Find user function class and create instance
		$file_path = $plugin_views_dir . '/' . $this->plugin_request_router_views_dir_name . "/{$controller}.php";
		
		if( file_exists( $file_path ) ) {
		
			//Include view file
			include_once( $file_path );
			
			//Instantiate class
			$view_class_slug = $view_class_slug . ucfirst( $controller ) . 'View';
			
			if( class_exists( $view_class_slug ) ) {
				$$view_class_slug = new $view_class_slug($data);
				
				//Load action
				if( method_exists( $$view_class_slug, $action ) ) {
					$$view_class_slug->$action();
					$result = true;
				}
			}
			
		} elseif( $controller == 'index' && !file_exists( $file_path ) ) {
			//Assume that this plugin is not making use of the requrest router (not multi page)
			$result = true;
		}
		
		return $result;
	}
	
	public function plugin_redirect( $meta_redirect = false, $args = array() ) {
		
		//Init vars
		$url = null;
		$defaults = array(
			'plugin_menu_slug' 	=> NULL,
			'controller'		=> '',
			'action'			=> '',
			'params'			=> array()
		);
		
		$args = wp_parse_args( $args, $defaults );
		extract( $args );
		
		if( $meta_redirect ) {

			$url = admin_url() . 'admin.php?page='. $plugin_menu_slug . '&controller=' . $controller . '&action=' . $action;
			
			//Loop extra params and add them to the end of the url
			if( !empty($params) && is_array($params) ) {
				foreach( $params as $key => $val ) {
					$url.= "&{$key}={$val}";
				}
			}

			//Add a Nonce to the url
			$nonce_action = "{$plugin_menu_slug}-{$action}_{$controller}";
	
			$url = wp_nonce_url( $url, $nonce_action );
			
			echo "<meta http-equiv='refresh' content='0; url={$url}'>";
			exit;
		
		} elseif(isset( $plugin_menu_slug )) {
			
			//Redirect to the plugin home
			$url = add_query_arg( array( 'page' => $plugin_menu_slug ), admin_url() . 'admin.php' );
	
			wp_redirect( $url );
			exit;
			
		}
		
	}
	
	/**
	* form_action
	*
	* Creates a form action url based on the $controller and $action params provided.
	* The method will also create a Nonce based on page_slug-action-controller and append it
	* to the url using add_query_arg
	*
	*/
	public function form_action( $action_url = NULL, $action = null, $page_slug = NULL, $controller = NULL ) {
		
		if(isset( $action, $page_slug, $controller )) {
			
			$url = add_query_arg( array( 'controller' => $controller, 'action' => $action, 'noheader' => 'true', '_wpnonce' => false ) );
		
			//Add a Nonce to the url
			$nonce_action = "{$page_slug}-{$action}_{$controller}";
			
			$url = wp_nonce_url( $url, $nonce_action );
			
			$action_url = $url;
			
			unset($url);
			unset($nonce_action);
		}
		
		return $action_url;
	}
	
	/**
	* get_plugin_options
	* 
	* Little helper to get data from the wordpress options database table
	* and cache it for later use.
	* 
	* @access 	protected
	* @author	Ben Moody
	*/
	public function get_plugin_options( $option_data = array(), $option_slug = NULL, $multi_array_slug = NULL ) {
		
		//Init vars
		$temp_data = array();
		
		if(isset( $option_slug )) {
			
			//Get options from database using slug provided
			$temp_data = get_option( $option_slug );
			
			//if an multi array of data then use multi array slug as key
			if( isset( $multi_array_slug ) && array_key_exists( $multi_array_slug, $temp_data ) ) {
				$option_data = $temp_data[ $multi_array_slug ];
			} else {
				$option_data = $temp_data;
			}
			
			unset( $temp_data );
		}
		
		return $option_data;
	}
	
	/**
	* render_plugin_view
	*
	* Called using custom wordpress filter hook 'prso_core_render_plugin_view', 
	* Returns the html required to render a plugin options page by calling the required
	* WP functions, echo provided args, and applying custom filters.
	*
	* @param $args array - contains required args, see $defaults for example
	*/
	public function render_plugin_view( $view_output = NULL, $args = array() ) {
		
		//Init vars
		$defaults = array(
			'screen_icon' 	=> 'options-general',
			'view_slug'		=> NULL,
			'view_title'	=> NULL,
			'submit_title'	=> 'Submit',
		);
		
		$args = wp_parse_args( $args, $defaults );
		
		extract($args);
		
		if( isset($view_slug) ) {
			
			ob_start();
			?>
			<div class="wrap">
				<?php screen_icon( $screen_icon ); ?>
				<h2><?php echo $view_title; ?></h2>
				
				<!-- Output jquery to active field validation messages !-->
				<?php echo apply_filters( 'prso_core_get_flash_script', NULL ); ?>
				
				<!-- Output any custom html before the form !-->
				<?php echo apply_filters( 'prso_core_render_plugin_view_before_form', NULL ); ?>
				
				<form action="options.php" method="post">
					<!-- Field validation !-->
					<?php settings_fields( $view_slug ); ?>
					<!-- Form sections !-->
					<?php do_settings_sections( $view_slug ); ?>
					
					<!-- Output any custom html before the submit button !-->
					<?php echo apply_filters( 'prso_core_render_plugin_view_before_submit', NULL ); ?>
					
					<p class="submit">
						<input class="button-primary" name="Submit" type="submit" value="<?php echo $submit_title; ?>" />
					</p>
				</form>
				
				<!-- Output any custom html before the form !-->
				<?php echo apply_filters( 'prso_core_render_plugin_view_after_form', NULL ); ?>
			</div>
			<?php
			$view_output = ob_get_contents();
			ob_end_clean();
			
		}
		
		return $view_output;
	}
	
	/**
	* render_plugin_view
	*
	* Called using custom wordpress filter hook 'prso_core_render_plugin_view', 
	* Returns the html required to render a plugin options page by calling the required
	* WP functions, echo provided args, and applying custom filters.
	*
	* @param $args array - contains required args, see $defaults for example
	*/
	public function validate_plugin_fields( $validate = array(), $data = array(), $args = array() ) {
		
		//First check to see if required Pressoholic framework plugin methods are installed
		if( has_action('prso_core_validate') ) {
			
			//Init vars
			$defaults = array(
				'success_message' 	=> 'Options saved.',
				'fail_message'		=> 'Some fields failed validation. See below for details.'
			);
			
			$args = wp_parse_args( $args, $defaults );
			
			extract($args);
			
			if( !empty($validate) && !empty($data) ) {
				
				//Call pressoholics validation helper and pass core data array and validation array
				$data = apply_filters( 'prso_core_validate', $data, $validate );
				
				//Detect validation errors in data look for error key
				if( isset( $data['error'] ) && $data['error'] ) {
					//Set main page flash message
					do_action( 'prso_core_set_flash', $fail_message, 'error' );
				} else {
					//All is well :)
					do_action( 'prso_core_set_flash', $success_message, 'success' );
				}
				
			}
			
			return $data;
			
		} else {
			//Missing requried objects, so Pressoholics companion theme plugin not installed :(
			wp_die( __( 'Required plugin missing. Have you installed the Pressoholics Theme Plugin?' ) );		
		}
		
	}
	
}