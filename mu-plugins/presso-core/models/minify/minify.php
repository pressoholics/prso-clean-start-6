<?php
/**
 * Admin Model class file.
 *
 * Simplifies the constuction of admin pages.
 *
 * CONTENTS:
 *
 * 
 * 
 */
 
class PrsoCoreMinifyModel {
	
	private $data = array();
	
	//Magic methods set and get
	public function __set( $name, $value ) {
		
		if( isset($this->data) ) {
			$this->data[$name] = $value;
		}
		
	}
	
	public function __get( $name ) {
	
		if( isset($this->data) && array_key_exists( $name, $this->data ) ) {
			return $this->data[$name];
		}
		
		return NULL;
	}
	
	function __construct() {
		
		//Add custom wordpress action hooks for this model
		$this->model_custom_wp_actions();
		
		//Add custom wordpress filter hooks for this model
		$this->model_custom_wp_filters();
	}
	
	private function model_custom_wp_actions() {
		
		//WP Action hook for $this->merge_scripts
		$this->add_action( 'prso_minify_merge_scripts', 'merge_scripts', 10, 2 );
		
		//WP Action hook for $this->merge_styles
		$this->add_action( 'prso_minify_merge_styles', 'merge_styles', 10, 2 );
		
	}
	
	private function model_custom_wp_filters() {
	
		//WP Filter hook for $this->get_file_path_from_url
		$this->add_filter( 'prso_minify_get_file_path', 'get_file_path_from_url', 10, 1 );
		
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
	private function add_action( $tag = NULL, $method = NULL, $priority = 10, $accepted_args = NULL ) {
		
		if( isset($tag,$method) ) {
			//Check that action has not already been added
				add_action( $tag, array($this, $method), $priority, $accepted_args );
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
				add_filter( $tag, array($this, $method), $priority, $accepted_args );
		}
		
	}
	
	/**
	* merge_scripts
	* 
	* Called during wp_print_scripts to intercept script output from theme and plugins.
	* It dequeues all scripts enqueued using wp_enqueue_scripts and calls $this->minify_scripts to merge
	* all the scripts into one single app.min.js.
	*
	* NOTE: To ignore a script add it's enqueue handle to $exceptions array
	* NOTE: If you want to specify the scripts to merge (recommended) then add the handles to $args['handles'] array
	*
	* NOTE: If specific scripts are set for merging then the method will force all other enqueued scripts into footer (plugins ect)
	*
	* @access 	public
	* @author	Ben Moody
	*/
	public function merge_scripts( $args = array(), $exceptions = array() ) {
		
		//Init vars
		global $wp_scripts;
		$script_queue 	= array();
		$script_params 	= array();
		$scripts_src	= array();
		$merged 		= get_stylesheet_directory() . '/javascripts/app.min.js';
		
		//Set default scripts not to merge - use enqueue handle
		$exception_defaults = array(
			'jquery',
			'admin-bar'
		);
		
		$defaults = array(
			'merged_path' 		=> NULL, //Full path to your new merged script file -REQ
			'merged_url'		=> NULL,
			'depends'			=> array( 'jquery' ), //Array of script handles your merged script depends on
			'enqueue_handle'	=> 'prso-theme-app'
		);
		
		//Parse args
		$args = wp_parse_args( $args, $defaults );
		//Parse exceptions
		$exceptions = wp_parse_args( $exceptions, $exception_defaults );
		
		//Add 'depends' scripts to exception list too
		$exceptions = wp_parse_args( $defaults['depends'], $exceptions );
		
		extract( $args );
		
		//Sanitize path and url
		$merged_path = esc_attr( $merged_path );
		$merged_url = esc_url( $merged_url );
		
		//Cache registerred scripts object from global $wp_scripts object
		if( isset($wp_scripts->registered) ) {
			$script_params = $wp_scripts->registered;
		}
		
		//Loop all regisitered scripts
		if( isset($wp_scripts->queue) && !empty($wp_scripts->queue) && !is_admin() ) {
			
			$script_queue = $wp_scripts->queue;
			
			foreach( $script_queue as $script_handle ) {
				
				//Check script is not on the exceptions list
				if( !in_array($script_handle, $exceptions) && isset($script_params[$script_handle]->src) ) {
					
					//Now check if we should only include specific script handles
					if( isset($handles) && is_array($handles) ) {
						
						if( in_array( $script_handle, $handles ) ) {
							//Cache script src url
							$scripts_src[] = $script_params[$script_handle]->src;
							
							//Remove script from queue
							wp_dequeue_script( $script_handle );
							wp_deregister_script( $script_handle );
						}
						
					}
					
				}
				
			}
			
			//Now we have a list of scripts lets minify them
			if (!file_exists($merged_path)) {
				$this->minify_scripts( $scripts_src, $merged_path );
			}
			
			//Enqueue merged script
			wp_enqueue_script( $enqueue_handle, $merged_url , $depends, filemtime( $merged_path ), true );
			
		}
		
	}
	
	/**
	* minify_scripts
	* 
	* Called by $this->merge_scripts(), loops array of script URLs and uses
	* JSMIN to minify and merge all scripts into one single file which is enqueued by merge_scripts into the footer
	*
	* NOTE: The method compares the merged file time with each enqueued script and will update the merge file
	* if a source file has been updated or a new one is added.
	*
	*
	* @access 	private
	* @author	Ben Moody
	*/
	private function minify_scripts( $files = array(), $merged_path = NULL ) {
		
		//Init vars
		$jsmin_path = dirname(__FILE__) . '/includes/jsmin.php';
		$js 		= '';
		$changes 	= false;
		
		if( !empty($files) && isset($merged_path) ) {
			
			//Require jsmin php file
			if( file_exists($jsmin_path) && defined('ABSPATH') ) {
				
				require_once($jsmin_path);
				
				// if merged file doesn't exist yet, create a placeholder
				if (!file_exists($merged_path)) :
					file_put_contents($merged_path, 'PRSO Framework Temp File');
					touch($merged_path, 1);
					$changes = true;
				endif;
				
				$lastmodified = filemtime($merged_path);
				
				//check if any of the files were modified
				foreach ($files as $file) :
					
					//Get file's absolute path using the url
					$file_absolute_path = apply_filters( 'prso_minify_get_file_path', $file );
					
					if (filemtime($file_absolute_path) > $lastmodified) 
						$changes = true;
				endforeach;
			
			    // if a file was modified, write a new merged one
				if ($changes) :
				
					foreach ($files as $file) :
					  $js .= JSMin::minify(file_get_contents($file));
					endforeach;
					
					file_put_contents($merged_path, $js);
					
					// finally set all file modification dates to the one of the merged one
					foreach ($files as $file) :
						//Get file's absolute path using the url
						$file_absolute_path = apply_filters( 'prso_minify_get_file_path', $file );
						
						//Update file mod date on each merged file (originals)
					    touch($file_absolute_path, filemtime($merged_path));
					endforeach;
					
				endif;

			}
			
		}
		
	}
	
	/**
	* merge_styles
	* 
	* Deenqueues all styles and enqueues a new merged stylessheet. 
	* Note it will ignore all WP Core 	stylesheets and process only those in
	* /wp-content/ thus all plugins and theme styles.
	*
	* @access 	public
	* @author	Ben Moody
	*/
	public function merge_styles( $args = array(), $exceptions = array() ) {
		
		//Init vars
		global $wp_styles;
		$exception_handles = array();
		$registered_styles = array();
		$merge_styles = array();
		
		//Set default scripts not to merge - use enqueue handle
		$exception_defaults = array(
			'wp-admin',
			'wp-includes'
		);
		
		$defaults = array(
			'merged_path' 		=> NULL, //Full path to your new merged script file -REQ
			'merged_url'		=> NULL,
			'depends'			=> array(), //Array of script handles your merged script depends on
			'enqueue_handle'	=> 'presso-theme-app-min'
		);
		
		//Parse args
		$args = wp_parse_args( $args, $defaults );
		//Parse exceptions
		$exceptions = wp_parse_args( $exceptions, $exception_defaults );
		
		extract( $args );
		
		//Sanitize path and url
		$merged_path = esc_attr( $merged_path );
		$merged_url = esc_url( $merged_url );
		
		if( isset($wp_styles->registered) ) {
		
			$registered_styles = $wp_styles->registered;
			
			//Loop all registered style and cache style src - skip those in exception directories
			foreach( $registered_styles as $style ) {
				
				//Style base dir
				$base_dir = dirname($style->src);
				
				//Get file's absolute path using the url
				$file_absolute_path = apply_filters( 'prso_minify_get_file_path', $base_dir );
				
				if( file_exists($file_absolute_path) ) {
					
					//Loop exceptions and cache their handles
					foreach( $exceptions as $exception ) {
						$excep_dir = "/{$exception}/";
						
						if( preg_match( $excep_dir, $base_dir ) ) {
							$exception_handles[] = $style->handle;
						}
					}
					
					//If style handle is not an exception cache it and dequeue the stylesheet
					if( !in_array( $style->handle, $exception_handles ) && is_string( $style->src ) ) {
						$merge_styles[] = $style->src;
						//Remove style from queue
						wp_dequeue_style( $style->handle );
						
					}
					
				}
				
			}
			
		}
		
		//Minify_stylesheets
		if( !file_exists($merged_path) ) {
			$this->minify_stylesheets( $merge_styles, $merged_path );
		}
		
		//Enqueue minifyed stylesheet
    	wp_enqueue_style( $enqueue_handle, $merged_url, $depends, filemtime($merged_path) );
		
	}
	
	/**
	* minify_stylesheets
	* 
	* In order to minimize the number and size of HTTP requests for CSS content,
 	* this script combines multiple CSS files into a single file and compresses
 	* it on-the-fly.
 	*
 	* NOTE it detects changes to original stylesheets and will only write the merged file
 	* if a change in one of the original enqueued styleheets is found.
	*
	* @access 	public
	* @author	Ben Moody
	*/
	private function minify_stylesheets( $files = array(), $merged_path = NULL ) {
		
		//Init vars
		$buffer 	= NULL;
		$changes	= false;
		
		if( !empty($files) && isset($merged_path) ) {
			
			//Loop files and cache contents
			foreach( $files as $file ) {
				$buffer.= file_get_contents( $file );
			}
			
			// Remove comments
			$buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
			
			// Remove space after colons
			$buffer = str_replace(': ', ':', $buffer);
			
			// Remove whitespace
			$buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);
			
			// if merged file doesn't exist yet, create a placeholder
			if (!file_exists($merged_path)) :
				file_put_contents($merged_path, 'PRSO Framework Temp File');
				touch($merged_path, 1);
			endif;
			
			
			$lastmodified = filemtime($merged_path);
				
			//check if any of the files were modified
			foreach ($files as $file) :
				
				//Get file's absolute path using the url
				$file_absolute_path = apply_filters( 'prso_minify_get_file_path', $file );
				
				if (filemtime($file_absolute_path) > $lastmodified) 
					$changes = true;
			endforeach;
			
			// if a file was modified, write a new merged one
			if ($changes) :
				
				file_put_contents($merged_path, $buffer);
				
				// finally set all file modification dates to the one of the merged one
				foreach ($files as $file) :
					//Get file's absolute path using the url
					$file_absolute_path = apply_filters( 'prso_minify_get_file_path', $file );
					
					//Update file mod date on each merged file (originals)
				    touch($file_absolute_path, filemtime($merged_path));
				endforeach;
				
			endif;
			
		}
		
	}
	
	/**
	* get_file_path_from_url
	* 
	* Converts a file URL (FULL) to an absolute path
	*
	*
	* @access 	private
	* @author	Ben Moody
	*/
	public function get_file_path_from_url( $file_url = NULL ) {
		
		if( isset($file_url) ) {
			
			$file_url = esc_url( $file_url );
			
			//Get file's absolute path using the url
			$file_url_info 	= parse_url($file_url);
			
			if( isset($file_url_info['path']) ) {
				
				$file_path 		= $file_url_info['path'];
			
				//Isolate path to wp-content dir used by themes and plugins
				$file_path = explode('wp-content', $file_path);
				
				if( isset($file_path[1]) && defined('ABSPATH') ) {
					$file_url = ABSPATH . 'wp-content' . $file_path[1];
				}
				
			}
			
		}
		
		return $file_url;
	}
	
}