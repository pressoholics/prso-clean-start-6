<?php
/**
 * Theme Core Functions
 *
 * Contains methods required by the Prso Theme Framework.
 * 
 * DO NOT call these functions directly, output for some of 
 * these methods can be edited via vars set in the config class - config.php
 *
 */
 
 /**
 * Contents
 *
 * 1. theme_setup	-	Calls functions in this class via wp actions and filters to setup the theme/admin area
 * 2. wp_head_cleanup	-	Remove elements and scripts from page head via wp_head action hook
 * 3. remove_rss_version	-	Returns NULL to remove wp version from rss feed
 * 4. enqueue_front_end_scripts	-	Register and Enqueue default scripts required for prso theme framework
 * 5. enqueue_zurb_foundation_scripts	-	Called by enqueue_front_end_scripts, use wp_dequeue_script in functions.php to remove unused scripts
 * 6. enqueue_comments_script	-	Enqueue comments script only on pages where comments are open
 * 7. load_google_jquery	-	Registers Googles CDN jQuery for theme, replaces wp version, set url using $theme_google_jquery_url in config.php
 * 8. load_wp_jquery	-	Enqueues Wordpress' jQuery library for theme front end
 * 9. enqueue_theme_styles	-	Registers base stylesheets for theme framework
 * 10. add_theme_support	-	Register support for theme features with wordpress, alter features via config.php
 * 11. add_custom_thumbnails	-	Register custom thumbnail sizes or alter wp defaults, use $theme_thumbnail_settings in config.php
 * 12. register_sidebars	-	Register theme sidebars, define using $theme_sidebar_settings in config.php
 * 13. remove_p_tag_from_images	-	Filter out <p> tags wrapped around <img> tags
 * 14. yoast_allow_rel	-	Adds rel="" to links
 * 15. add_user_contact_methods	-	Adds extra contact fields to admin user profiles, define in $admin_user_contact_methods in config.php
 * 16. admin_area_actions	-	Call any functions to alter wp admin area here
 * 17. disable_dashboard_widgets	-	Disable admin dashboard widgets. Define widget with admin_disable_dashboard_widgets in config.php
 * 18. add_comments_classes	-	Add html classes to comments html wrapper
 * 19. custom_post_password_form	-	Overwrite the form html output for protected posts
 * 20. update_wp_tag_cloud	-	Call any function to alter the wp tag cloud here.
 * 21. add_tag_class 	-	filter tag clould output so that it can be styled by CSS
 * 22. my_widget_tag_cloud_args	-	Override wordpress tag cloud args
 * 23. wp_tag_cloud_filter	-	Wrap the WP tag cloud in custom html
 * 24. add_class_the_tags	-	Add custom classes to tag <a> links
 * 25. remove_more_jump_link	-	Remove the html page jump (#DOM_ID) from more links
 * 26. remove_thumbnail_dimensions	-	Remove height/width dimensions from thumbnail images to ensure they are dynamic and fluid
 * 27. custom_wp_nav_menu	-	Override the list of allowed classes to output for WP Nav Menus
 * 28. current_to_active	-	Change the class used to indicate an active page in the WP Nav Menu
 * 29. strip_empty_classes	-	Deletes empty classes and removes the sub menu class_exists
 * 30. merge_scripts	-	Merges and minifies scripts, define scripts to merge via $theme_script_merge_args in config.php
 * 31. merge_styles	-	Merges and minifies stlyesheets, define options via $theme_style_merge_args in config.php
 * 32. REMOVED -> add_search_to_nav	-	REMOVED IN v4.1.1
 * 33. custom_pagination	-	Outputs custom pagination to template files via 'prso_pagination' action
 * 34. post_class_filter	-	Filter post classes printed by worpdress via post_class();
 * 35. gravity_forms_customizer		-	Includes gravity_forms_custom.php from inc folder
 * 36. load_cufon_script	-	Registers and enqueues Cufon font replacement script based on args from config.php
 * 37. load_backstretch_script	=	Register and enqueue script for Backstretch background image script
 * 39. update_post_views	-	Adds a view counter to posts/pages
 * 41. init_theme_shortcodes	-	Include theme custom shortcodes
 * 42. init_theme_options	-	Include theme options framework
 * 43. init_theme_textdomain	-	Loads the theme's translated strings
 * 44. oembed_zurb_video_wrapper	-	Wraps any youtube or vimeo video embeds in correct zurb foundation flex video wrappers
 * 45. Chrome admin area bug fix -  fixes issue where admin area is messed up on chrome
 *
 */
class PrsoThemeFunctions extends PrsoThemeAppController {
	
	function __construct() {
		
		//Ensure vars set in config are available
 		parent::__construct();
 		
 		//Prepare theme
 		$this->theme_setup();

 	}
 	
 	/**
	* theme_setup
	* 
	* All core methods used to setup the theme framework are called here.
	* Either directly or via WP Actions and Filters 
	* 
	* @access 	private
	* @author	Ben Moody
	*/
 	private function theme_setup() {
 		
 		//Theme init action 'after_switch_theme'
 		add_action( 'init', array($this, 'theme_activation') );
 		
 		//Setup translation options
 		
 		//Cleanup Wordpress Head
 		add_action( 'wp', array($this, 'wp_head_cleanup') );
 		
 		//Remove WP version from RSS
 		add_filter( 'the_generator', array($this, 'remove_rss_version') );
 		
 		//Load scripts for non-admin (front end) pages
 		add_action( 'wp_enqueue_scripts', array($this, 'enqueue_front_end_scripts') );
 		
 		//Load styles for front end pages
 		add_action( 'wp_enqueue_scripts', array($this, 'enqueue_theme_styles') );
 		
 		//Set Prso Theme support
 		$this->add_theme_support();
 		
 		//Add extra form fields to WP User profile contact methods
		add_filter( 'user_contactmethods', array($this, 'add_user_contact_methods'), 10, 1 );
 		
 		//Register Prso Theme sidebars
 		add_action( 'widgets_init', array($this, 'register_sidebars') );
 		
 		//Remove <p> tag from around imgs (//css-tricks.com/snippets/wordpress/remove-paragraph-tags-from-around-images/)
 		add_filter( 'the_content', array($this, 'remove_p_tag_from_images'), 100 );
 		add_filter( 'widget_text', array($this, 'remove_p_tag_from_images'), 100 );
 		
 		//Hack to enable rel='' attr for links - thanks to yoast
 		if( !function_exists('yoast_allow_rel') ) {
 			add_action( 'wp_loaded', array($this, 'yoast_allow_rel') );
 		}
 		
 		//Admin specific Actions and Filters
 		$this->admin_area_actions();
 		
 		//Add Zurb Foundation grid classes to comments
 		add_filter( 'comment_class', array($this, 'add_comments_classes') );
 		
 		//Overwrite WP default post password form
 		add_filter( 'the_password_form', array($this, 'custom_post_password_form') );
 		
 		//Update WP tag cloud to improve style
 		$this->update_wp_tag_cloud();
 		
 		//Enable shortcodes in widgets
 		add_filter('widget_text', 'do_shortcode');
 		
 		//Disable page jump in 'read more' links
 		add_filter( 'the_content_more_link', array($this, 'remove_more_jump_link') );
 		
 		// Remove height/width attributes on images so they can be responsive
 		add_filter( 'post_thumbnail_html', array($this, 'remove_thumbnail_dimensions'), 10 );
 		add_filter( 'image_send_to_editor', array($this, 'remove_thumbnail_dimensions'), 10 );
 		
 		//Stop wp from marking blog nav as active for all custom post types
 		add_filter( 'nav_menu_css_class', array($this, 'custom_wp_nav_menu'), 10, 2 );
 		
 		//change the standard class that wordpress puts on the active menu item in the nav bar
 		add_filter( 'wp_nav_menu', array($this, 'current_to_active'), 10, 2 );
 		
 		//Deletes empty classes and removes the sub menu class_exists
 		add_filter( 'wp_nav_menu', array($this, 'strip_empty_classes') );
 		
 		//Merges and minifies scripts
 		add_action( 'wp_print_scripts', array($this, 'merge_scripts') );
 		
 		//Merges and minifies stylesheets
 		add_action( 'wp_print_styles', array($this, 'merge_styles') );
 		
 		//Custom prso theme framework pagination
 		add_action( 'prso_pagination', array($this, 'custom_pagination'), 10, 2 );
 		
 		//Add filter to alter the array of classes used by post_class()
 		add_filter( 'post_class', array($this, 'post_class_filter'), 10, 1 );
 		
 		//Call method to setup theme options
 		//add_action( 'init', array($this, 'admin_init_theme_options_redux') );
 		add_action( 'init', array($this, 'admin_init_theme_options_acf') );
 		
 		//Init theme text domain
 		add_action('after_setup_theme', array($this, 'init_theme_textdomain'));
 		
 		//Filter oembed and wrap youtube and vimeo videos in flex video wrappers
 		add_filter( 'oembed_result', array($this, 'oembed_zurb_video_wrapper'), 10, 3 );
 		
 		//Fix chrome admin area bug
 		add_action('admin_enqueue_scripts', array($this, 'chromefix_inline_css') );
 		
 	}
 	
 	/**
	* theme_activation
	* 
	* @CAlled by 'after_theme_switch'
	* 
	* Call any methods to be run at theme activation 'theme switch'
	*
	* @access 	public
	* @author	Ben Moody
	*/
 	public function theme_activation() {

	 	//Set thumbnail sizes
		$this->add_custom_thumbnails();
		
		//Force Gravity forms to load scripts in footer
	    add_filter('gform_init_scripts_footer', '__return_true');
	 	
 	}
 	
 	/**
	* wp_head_cleanup
	* 
	* Call actions to remove elements from wp_head()
	* 
	* @access 	public
	* @author	Ben Moody
	*/
 	public function wp_head_cleanup() {
 		
 		//Remove header links
 		remove_action( 'wp_head', 'feed_links_extra', 3 );                    // Category Feeds
		remove_action( 'wp_head', 'feed_links', 2 );                          // Post and Comment Feeds
		remove_action( 'wp_head', 'rsd_link' );                               // EditURI link
		remove_action( 'wp_head', 'wlwmanifest_link' );                       // Windows Live Writer
		remove_action( 'wp_head', 'index_rel_link' );                         // index link
		remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 );            // previous link
		remove_action( 'wp_head', 'start_post_rel_link', 10, 0 );             // start link
		remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 ); // Links for Adjacent Posts
		remove_action( 'wp_head', 'wp_generator' );                           // WP version
		
		//Remove emoji garbage
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );	
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );	
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		add_filter( 'tiny_mce_plugins', array($this, 'disable_emojis_tinymce') );

		if ( !is_admin() && ('wp-login.php' !== get_current_screen()) ) {
			wp_deregister_script('jquery');                                   // De-Register jQuery
			wp_register_script('jquery', '', '', '', true);                   // It's already in the Header
		}
 		
 	}
 	
 	/**
	* disable_emojis_tinymce
	* 
	* Disable emojis on tinymce, adds extra js, css, overhead we don't need
	* 
	* @access 	public
	* @author	Ben Moody
	*/
 	public function disable_emojis_tinymce( $plugins ) {
	  if ( is_array( $plugins ) ) {
	    return array_diff( $plugins, array( 'wpemoji' ) );
	  } else {
	    return array();
	  }
	}
 	
 	/**
	* remove_rss_version
	* 
	* Returns NULL to WP Filter to remove the rss version as a security measure
	* 
	* @access 	public
	* @author	Ben Moody
	*/
 	public function remove_rss_version() {
 		return '';
 	}
 	
 	/**
	* enqueue_front_end_scripts
	* 
	* If you need to add any scripts for front end user call them here.
	*
	* Remember to register the script with WP if not a core WP script
	* 
	* @access 	public
	* @author	Ben Moody
	*/
 	public function enqueue_front_end_scripts() {
 		
 		//Init vars
 		$google_jquery_url = NULL;
 		
 		//Ensure scripts are loaded for front end only
 		if( !is_admin() ) {
 			
 			//Load Zurb Foundation scripts
 			$this->enqueue_zurb_foundation_scripts();
 			
 			//Load Wordpress commments jQuery for single pages only
 			$this->enqueue_comments_script();
 			
 		}
 		
 	}
 	
 	/**
	* enqueue_zurb_foundation_scripts
	* 
	* Register and Enqueue all scripts required by the Zurb Foundation framework
	*
	* Called directly in enqueue_front_end_scripts()
	* 
	* @access 	private
	* @author	Ben Moody
	*/
 	private function enqueue_zurb_foundation_scripts() {
 		
 		/** Core jQuery Plugins **/
	    
	    
	    //NOTE if detected Child-Theme app.js will override Parent app.js
	    if( is_child_theme() ) {
	    	wp_register_script( 'prso-theme-app', get_stylesheet_directory_uri() . '/dist/assets/js/app.js', array('jquery'), PRSOTHEMEFRAMEWORK__VERSION, true );
	    }

	    wp_enqueue_script( 'prso-theme-app' );
 		
 	}
 	
 	/**
	* enqueue_comments_script
	* 
	* Enqueues WP comments script only on single pages where comments are open.
	* 
	* Called directly in enqueue_front_end_scripts()
	*
	* @access 	private
	* @author	Ben Moody
	*/
 	private function enqueue_comments_script() {
 	
 		if( is_single() && comments_open() ) {
 			wp_enqueue_script( 'comment-reply' );
 		}
 		
 	}
 	
 	/**
	* enqueue_theme_styles
	* 
	* Enqueues the style sheet for theme -> '/stylesheets/app.css'
	* 
	* @access 	public
	* @author	Ben Moody
	*/
 	public function enqueue_theme_styles() {
 		
 		//Register the App's specific stylesheet - NOTE if child theme is used will try to find app.css in child dir
	    wp_register_style( 'presso-theme-app', get_stylesheet_directory_uri() . '/dist/assets/css/app.css', array(), filemtime( get_stylesheet_directory() . '/dist/assets/css/app.css' ), 'all' );

	    //Enqueue App's SASS produced stylesheet
    	wp_enqueue_style( 'presso-theme-app' );
 		
 	}
 	
 	/**
	* add_theme_support
	* 
	* Register any WP theme support elements here.
	*
	* Some of these settings can be altered via the config class vars
	* 
	* @access 	public
	* @author	Ben Moody
	*/
 	public function add_theme_support() {
 		
 		//Init vars
 		$nav_menu_args	= array( 
			'main_nav' => 'The Main Menu',   // main nav in header
			'footer_links' => 'Footer Links' // secondary nav in footer
		);
		$post_format_args = array();
		$custom_background_args = array(
			'default-color' => 'ffffff'
		);
 		
 		//Get nav menu options from config class
 		if( isset($this->theme_nav_menus) ) {
 			$nav_menu_args = wp_parse_args( $this->theme_nav_menus, $nav_menu_args );
 		}
 		
 		//Get post format options from config class
 		if( isset($this->theme_post_formats) ) {
 			$post_format_args = wp_parse_args( $this->theme_post_formats, $post_format_args );
 		}
 		
 		//Get custom background options from config class
 		if( isset($this->theme_custom_background) ) {
 			$custom_background_args = wp_parse_args( $this->theme_custom_background, $custom_background_args );
 		}
 		
 		if ( function_exists( 'add_theme_support' ) ) {
 		
	 		//Post Thumbnails
	 		add_theme_support( 'post-thumbnails' );
			
			//WP custom background
			add_theme_support( 'custom-background', $custom_background_args );
			
			//RSS thingy
			add_theme_support( 'automatic-feed-links' );
			
			//to add header image support go here: //themble.com/support/adding-header-background-image-support/
			
			//Post format support
			add_theme_support( 'post-formats', $post_format_args );	
			
			//Add custom Nav Menu support
			add_theme_support( 'menus' );            // wp menus
			//Register navs set in config class - edit $theme_nav_menus array in config class
			register_nav_menus( $nav_menu_args );	 // wp3+ menus
			
		}
 		
 	}
 	
 	/**
	* add_custom_thumbnails
	* 
	* Registers custom thumbnail sizes for theme as well as overrides wp defaults if requested
	*
	* NOTE: Use $theme_thumbnail_settings array in config.php to customize thumbnails
	* 
	* @access 	public
	* @author	Ben Moody
	*/
	private function add_custom_thumbnails() {
		
		//Init vars
		$defaults = array(
			'width' 	=> NULL,
			'height'	=> NULL,
			'crop'		=> false
		);
		
		//Check settings from config class
		if( isset($this->theme_thumbnail_settings) && is_array($this->theme_thumbnail_settings) ) {
			
			//Loop thumbnail sizes
			foreach( $this->theme_thumbnail_settings as $name => $args ) {

				if( !isset($args['config']) ) {
					continue;
				}

				$image_config = wp_parse_args( $args['config'], $defaults );
				
				extract($image_config);
				
				if( isset( $width, $height, $crop ) ) {
					
					//Check for requests to update WP default thumbnails
					switch( $name ) {
						case 'featured';
							//Add default thumb size
							set_post_thumbnail_size( $width, $height, $crop );   // default thumb size
							break;
						case 'thumbnail';
							if( is_admin() ) {
								update_option('thumbnail_size_w', $width);
								update_option('thumbnail_size_h', $height);
								update_option('thumbnail_crop', $crop);
							}
							break;
						case 'medium':
							if( is_admin() ) {
								update_option('medium_size_w', $width);
								update_option('medium_size_h', $height);
								update_option('medium_crop', $crop);
							}
							break;
						case 'large':
							if( is_admin() ) {
								update_option('large_size_w', $width);
								update_option('large_size_h', $height);
								update_option('large_crop', $crop);
							}
							break;
						default:

							//Add custom thumbnail image size to wordpress
							add_image_size( $name, $width, $height, $crop );

							//High res version
							if( is_int($width) ) {
								$width = $width * 2;
							}

							if( is_int($height) ) {
								$height = $height * 2;
							}
							add_image_size( "{$name}--x2", $width, $height, $crop );

							break;
					}
					
				}

				//Set any custom srcset rules
				if( isset($args['srcset']) && is_array($args['srcset']) ) {

					$this->register_theme_custom_image_srcset( $name, $args['srcset'] );

//					foreach($args['srcset'] as $srcset_attribute) {
//						$this->register_theme_custom_image_srcset( $name, $srcset_attribute );
//					}

				}
				
			}
			
		}
		
		//Add custom image sizes to "add media:" option for edit post/page
		add_filter( 'image_size_names_choose', array($this, 'insert_custom_image_sizes') );

		//Filter image srcset attributes
		add_filter( 'wp_get_attachment_image_attributes', array($this, 'get_image_size_srcset_attr'), 900, 3 );

		//Filter sizes attribute added to images
		add_filter( 'wp_calculate_image_sizes', array($this, 'custom_image_srcset_sizes_attr'), 900, 2 );

	}

	/**
	 * register_theme_custom_image_srcset
	 *
	 * @CALLED BY PrsoThemeFunctions::add_custom_thumbnails()
	 *
	 * Setup the srcset rules for any custom images
	 *
	 * @param array $prso_srcset_rules
	 * @access public
	 * @author Ben Moody
	 */
	private function register_theme_custom_image_srcset( $image_size = '', $srcset_rules = array() ) {

		//vars
		global $prso_srcset_rules;
		$current_image_size_rules = array();

		if( isset($image_size) && !empty($srcset_rules) ) {

			if( isset($prso_srcset_rules[ $image_size ]) ) {
				$current_image_size_rules = $prso_srcset_rules[ $image_size ];
			}

			$prso_srcset_rules[ $image_size ] = wp_parse_args( $srcset_rules, $current_image_size_rules );

		}


	}

	/**
	 * get_image_size_srcset_attr
	 *
	 * @CALLED BY FILTER 'wp_get_attachment_image_attributes'
	 *
	 * Helper to generate the image srcset attritbute for a specific custom image
	 * size based on the rules set in the prso_register_theme_custom_image_srcset()
	 * function.
	 *
	 * If a srcset rule is detected it adds the srcset attribute via the 'wp_get_attachment_image_attributes' filter
	 *
	 *
	 * @param int $attachment_id
	 * @param string $master_image_size
	 *
	 * @return string $srcset
	 * @access public
	 * @author Ben Moody
	 */
	public function get_image_size_srcset_attr( $attr, $attachment, $master_image_size ) {

		//vars
		global $prso_srcset_rules;
		$sources = array();
		$srcset  = '';

		if( is_admin() ) {
			return $attr;
		}

		if( !isset($attachment->ID) ) {
			return $attr;
		}

		if( !isset($prso_srcset_rules[ $master_image_size ]) ) {
			return $attr;
		}

		$attachment_id = $attachment->ID;

		//Build sources for this image size based on rules
		foreach ( $prso_srcset_rules[ $master_image_size ] as $rules ) {

			//Default to x2 version of image if custom image size not explicit
			if( empty($rules['image_size']) ) {
				$rules['image_size'] = "{$master_image_size}--x2";
			}

			$sources[ $rules['value'] ] = array(
				'url'        => wp_get_attachment_image_url( $attachment_id, $rules['image_size'] ),
				'descriptor' => $rules['descriptor'],
				'value'      => intval( $rules['value'] ),
			);

		}

		if ( empty( $sources ) ) {
			return null;
		}

		foreach ( $sources as $source ) {
			$srcset .= str_replace( ' ', '%20', $source['url'] ) . ' ' . $source['value'] . $source['descriptor'] . ', ';
		}

		$attr['srcset'] = $srcset;

		return $attr;
	}

	/**
	 * custom_image_srcset_sizes_attr
	 *
	 * @CALLED BY FILTER 'wp_calculate_image_sizes'
	 *
	 * Filter the sizes attribute of srcset images
	 *
	 * @access public
	 * @author Ben Moody
	 */
	public function custom_image_srcset_sizes_attr( $sizes, $size ) {

		/**
		* prso_theme__image_sizes_attr
		 *
		 * Filter the image sizes attribute string used by srcset for all images
		*
		* @since 6.5
		*
		* @see PrsoThemeFunctions::custom_image_srcset_sizes_attr()
		*
		* @param string sizes_attrbute
		*/
		$sizes = apply_filters( 'prso_theme__image_sizes_attr', '' );

		return $sizes;
	}
	
	// Add custom image sizes to post edit insert media option
	public function insert_custom_image_sizes( $image_sizes ) {
	
		// get the custom image sizes
		global $_wp_additional_image_sizes;
		
		// if there are none, just return the built-in sizes
		if ( empty( $_wp_additional_image_sizes ) )
			return $image_sizes;
		 
		// add all the custom sizes to the built-in sizes
		foreach ( $_wp_additional_image_sizes as $id => $data ) {
			// take the size ID (e.g., 'my-name'), replace hyphens with spaces,
			// and capitalise the first letter of each word
			if ( !isset($image_sizes[$id]) )
			$image_sizes[$id] = ucfirst( str_replace( '-', ' ', $id ) );
		}
	 
		return $image_sizes;
	}
	
	/**
	* register_sidebars
	* 
	* Registers the theme sidebars.
	*
	* NOTE: to add or remove sidebars from the theme edit the $theme_sidebar_settings in config class
	* 
	* @access 	public
	* @author	Ben Moody
	*/
	public function register_sidebars() {
		
		//Init vars
		$sidebar_defaults = array(
			'id'            => '',
			'name'          => '',
			'description'   => '',
		    'class'         => '',
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget'  => '</aside>',
			'before_title'  => '<h2 class="widgettitle">',
			'after_title'   => '</h2>' 
		);
		
		if( isset($this->theme_sidebar_settings) && is_array($this->theme_sidebar_settings) ) {
			
			//Loop each sidebar setting from config class and call WP register_sidebar() function
			foreach( $this->theme_sidebar_settings as $sidebar_ID => $sidebar_args ) {
				
				$sidebar_args = wp_parse_args( $sidebar_args, $sidebar_defaults );
				
				extract($sidebar_args);
				
				if( !empty($id) && !empty($name) ) {
					
					register_sidebar(
						array(
							'id'            => $id,
							'name'          => $name,
							'description'   => $description,
						    'class'         => $class,
							'before_widget' => $before_widget,
							'after_widget'  => $after_widget,
							'before_title'  => $before_title,
							'after_title'   => $after_title
						)
					);
					
				}
				
			}
			
		}
		
	}
	
	/**
	* remove_p_tag_from_images
	* 
	* Filters out <p> tags wrapped around <img> tags by WP
	* 
	* @access 	public
	* @author	Ben Moody
	*/
	public function remove_p_tag_from_images( $content ) {
		
		return preg_replace('/<p>\s*(<a .*>)?\s*(<img .* \/>)\s*(<\/a>)?\s*<\/p>/iU', '\1\2\3', $content);
		
	}
	
	/**
	* yoast_allow_rel
	* 
	* Adds rel='' to links
	* 
	* @access 	public
	* @author	Ben Moody
	*/
	public function yoast_allow_rel() {
		global $allowedtags;
		$allowedtags['a']['rel'] = array ();
	}
	
	/**
	* add_user_contact_methods
	* 
	* Used to add extra user contact fields to user profile view.
	*
	* NOTE: to edit the list of fields see $admin_user_contact_methods array in config class
	* 
	* @access 	public
	* @author	Ben Moody
	*/
	public function add_user_contact_methods( $contact_methods ) {
		
		//Check if options have been set in config class
		if( isset($this->admin_user_contact_methods) && is_array($this->admin_user_contact_methods) ) {
			
			//Merge arrays
			$content_methods = wp_parse_args( $contact_methods, $this->admin_user_contact_methods );
			
		}
		
		return $content_methods;
	}
	
	/**
	* admin_area_actions
	* 
	* Call methods to effect WP admin area here, eg. add/remove dashboard widgets
	* 
	* @access 	private
	* @author	Ben Moody
	*/
	private function admin_area_actions() {
		
		if( is_admin() ) {
		
			//Disable dashboard widgets
			add_action( 'admin_menu', array($this, 'disable_dashboard_widgets') );
		
		}
		
	}
	
	/**
	* disable_dashboard_widgets
	* 
	* Disables admin dashboard widgets.
	*
	* NOTE: to edit the list of dashboard see $admin_disable_dashboard_widgets array in config class
	* 
	* @access 	public
	* @author	Ben Moody
	*/
	public function disable_dashboard_widgets() {
		
		//Init vars
		$defaults = array(
			'id' 		=> NULL,
			'page'		=> 'dashboard',
			'context'	=> NULL
		);
		
		//Check if disable_dashboard_widgets array isset in config class
		if( isset($this->admin_disable_dashboard_widgets) && is_array($this->admin_disable_dashboard_widgets) ) {
			
			//Loop each request and call WP remove_meta_box for each
			foreach( $this->admin_disable_dashboard_widgets as $method_args ) {
				
				$method_args = wp_parse_args( $method_args, $defaults );
				
				if( isset( $method_args['id'], $method_args['page'], $method_args['context'] ) ) {
					remove_meta_box( $method_args['id'], $method_args['page'], $method_args['context'] );
				}
				
			}
			
		}
		
	}
	
	/**
	* add_comments_classes
	* 
	* Adds new classes to comments html wrapper
	* 
	* @access 	public
	* @author	Ben Moody
	*/
	public function add_comments_classes( $classes ) {
		array_push($classes, "large-12", "columns");
    	return $classes;
	}
	
	/**
	* custom_post_password_form
	* 
	* Overwrite the WP standard password form used on password protected posts/pages
	* 
	* @access 	public
	* @author	Ben Moody
	*/
	public function custom_post_password_form() {
		
		global $post;
		$label = 'pwbox-'.( empty( $post->ID ) ? rand() : $post->ID );
		$o = '<div class="clearfix"><form action="' . esc_url( site_url( 'wp-login.php?action=postpass', 'login_post' ) ) . '" method="post">
		' . __( "<p>This post is password protected. To view it please enter your password below:</p>" ) . '
		<div class="row collapse">
	        <div class="large-12 columns"><label for="' . $label . '">' . __( "Password:" ) . ' </label></div>
	        <div class="large-8 columns">
	            <input name="post_password" id="' . $label . '" type="password" size="20" class="input-text" />
	        </div>
	        <div class="large-4 columns">
	            <input type="submit" name="Submit" class="postfix button nice blue radius" value="' . esc_attr__( "Submit" ) . '" />
	        </div>
		</div>
	    </form></div>
		';
		return $o;
		
	}
	
	/**
	* update_wp_tag_cloud
	* 
	* Add actions/Filter calls to edit the WP tag cloud here
	* 
	* @access 	private
	* @author	Ben Moody
	*/
	private function update_wp_tag_cloud() {
		
		//filter tag clould output so that it can be styled by CSS
		add_action( 'wp_tag_cloud', array($this, 'add_tag_class') );
		
		//Tweak tag cloud args
		add_filter( 'widget_tag_cloud_args', array($this, 'my_widget_tag_cloud_args') );
		
		//Wrap tag cloud output
		add_filter( 'wp_tag_cloud', array($this, 'wp_tag_cloud_filter'), 10, 2 );
		
		//Alter the link (<a>) tag html
		add_filter( 'the_tags', array($this, 'add_class_the_tags') );
		
	}
	
	/**
	* add_tag_class
	* 
	* filter tag clould output so that it can be styled by CSS
	*
	* @access 	public
	* @author	Ben Moody
	*/
	public function add_tag_class( $taglinks ) {
	    $tags = explode('</a>', $taglinks);
	    $regex = "#(.*tag-link[-])(.*)(' title.*)#e";
	        foreach( $tags as $tag ) {
	            $tagn[] = preg_replace($regex, "('$1$2 label radius tag-'.get_tag($2)->slug.'$3')", $tag );
	        }
	    $taglinks = implode('</a>', $tagn);
	    return $taglinks;
	}
	
	/**
	* my_widget_tag_cloud_args
	* 
	* Override the WP tag cloud args
	* 
	* @access 	public
	* @author	Ben Moody
	*/
	public function my_widget_tag_cloud_args( $args ) {
		
		//Init vars
		$defaults = array(
			'number'	=>	20,		// show less tags
			'largest'	=>	9.75,	// make largest and smallest the same - i don't like the varying font-size look
			'smallest'	=>	9.75,	// make largest and smallest the same - i don't like the varying font-size look
			'unit'		=>	'px',
			'is_mobile'	=> array(	//Change sizes for mobile if requested
				'number'	=>	5,		
				'largest'	=>	2,	
				'smallest'	=>	2,	
				'unit'		=>	'rem',
			)
		);
		
		//Parse args from config.php
		if( wp_is_mobile() && isset($this->theme_tag_cloud_args['is_mobile']) && !empty($this->theme_tag_cloud_args['is_mobile']) ) {
			$args = wp_parse_args( $this->theme_tag_cloud_args['is_mobile'], $defaults['is_mobile'] );
		} elseif( isset($this->theme_tag_cloud_args) ) {
			$args = wp_parse_args( $this->theme_tag_cloud_args, $defaults  );
		} else {
			$args = wp_parse_args( $args, $defaults );
		}
		
		return $args;
	} 
	
	/**
	* wp_tag_cloud_filter
	* 
	* Wrap the WP tag cloud in custom html.
	* 
	* @access 	public
	* @author	Ben Moody
	*/
	public function wp_tag_cloud_filter($return, $tags) {
	  return '<div id="tag-cloud"><p>'.$return.'</p></div>';
	}
	
	/**
	* add_class_the_tags
	* 
	* Add custom classes to tag <a> links
	* 
	* @access 	public
	* @author	Ben Moody
	*/
	public function add_class_the_tags($html){
	    $postid = get_the_ID();
	    $html = str_replace('<a','<a class="label radius"',$html);
	    return $html;
	}
	
	/**
	* remove_more_jump_link
	* 
	* Remove the html page jump (#DOM_ID) from more links
	* 
	* @access 	public
	* @author	Ben Moody
	*/
	public function remove_more_jump_link($link) {
		$offset = strpos($link, '#more-');
		if ($offset) {
			$end = strpos($link, '"',$offset);
		}
		if ($end) {
			$link = substr_replace($link, '', $offset, $end-$offset);
		}
		return $link;
	}
	
	/**
	* remove_thumbnail_dimensions
	* 
	* Remove height/width dimensions from thumbnail images to ensure they are dynamic and fluid
	* 
	* @access 	public
	* @author	Ben Moody
	*/
	public function remove_thumbnail_dimensions( $html ) {
	    $html = preg_replace( '/(width|height)=\"\d*\"\s/', "", $html );
	    return $html;
	}
	
	/**
	* custom_wp_nav_menu
	* 
	* Override the list of allowed classes to output for WP Nav Menus
	* 
	* @access 	public
	* @author	Ben Moody
	*/
	public function custom_wp_nav_menu( $css_class, $item ) {
		
		//Init vars
		global $post;
		
        //Remove 'current_page_parent' from blog menu item when not a blog page
        if( get_post_type() !== 'post' ) {

	        if( $item->object_id == get_option('page_for_posts') ) {
	            foreach ($css_class as $k=>$v) {
	                if ($v=='current_page_parent') unset($css_class[$k]);
	            }
	        }
	        
	    }
	    
	    //Add custom post type slug as css class to all menu items for custom posts
	    if( (get_post_type($item->object_id) !== 'post') && (get_post_type($item->object_id) !== 'page') ) {
		    
		    $css_class[] = str_replace('_', '-', get_post_type($item->object_id));
		    
	    }
	    
	    //Add object id class
	    $css_class[] = 'active-for-post-' . $item->object_id;
	    
	    //Is active page?
        if( isset($post->ID, $item->object_id) && ($post->ID == $item->object_id) ) {
            $css_class[] = 'active';
        }
	    
	    return $css_class;
	}
	
	/**
	* current_to_active
	* 
	* Change the class used to indicate an active page in the WP Nav Menu
	* 
	* @access 	public
	* @author	Ben Moody
	*/
	public function current_to_active( $nav_menu, $args ){
        $replace = array(
                //List of menu item classes that should be changed to "active"
                'current_page_item' 	=> 'active',
                'current_page_parent' 	=> 'active',
                'current_page_ancestor' => 'active',
                'current-menu-item' 	=> 'active',
                'current-menu-parent' 	=> 'active',
                'current-menu-ancestor' => 'active',
        );
        $nav_menu = str_replace(array_keys($replace), $replace, $nav_menu);
        
        return $nav_menu;
    }
    
    /**
	* strip_empty_classes
	* 
	* Deletes empty classes and removes the sub menu class_exists
	*
	* @access 	public
	* @author	Ben Moody
	*/
    public function strip_empty_classes($menu) {
	    $menu = preg_replace('/ class=""| class="sub-menu"/','',$menu);
	    return $menu;
	}
	
	/**
	* merge_scripts
	* 
	* Called during wp_print_scripts to intercept script output from theme and plugins.
	* It dequeues all scripts enqueued using wp_enqueue_scripts and calls $this->minify_scripts to merge
	* all the scripts into one single app.min.js.
	*
	* NOTE: To ignore a script add it's enqueue handle to $exceptions array
	*
	* Param - $args array:
	*	- 'merged_path' REQUIRED, Full PATH to your new merged scripts file
	*	- 'merged_url' REQUIRED, URL to new merged scripts file
	*	- 'depends' Array of script handles to be enqueued BEFORE the min script, e.g. 'jquery'
	*	- 'handles' Array of script handles to merge, if empty ALL theme AND plugin scripts will be merged
	*	- 'enqueue_handle' Shouldn't need to change this as default should work fine without conflict
	*
	* @access 	public
	* @author	Ben Moody
	*/
	public function merge_scripts() {
		
		//Init vars
		$args 		= array();
		$exceptions = array();
		
		//Get vars from config.php
		if( isset($this->theme_script_merge_args) ) {
			$args = $this->theme_script_merge_args;
		}
		
		if( isset($this->theme_script_merge_exceptions) ) {
			$exceptions = $this->theme_script_merge_exceptions;
		}
		
		if( isset($args['merged_path']) && !empty($args['merged_path']) ) {
			
			//Before calling the merge action prepend the theme's stylesheet dir and uri to args
			$args['merged_url'] = get_stylesheet_directory_uri() . $args['merged_path'];
			$args['merged_path'] = get_stylesheet_directory() . $args['merged_path'];
			
			do_action( 'prso_minify_merge_scripts', $args, $exceptions );
		}
		
	}
	
	
	/**
	* merge_styles
	*
	* Called during wp_print_styles to intercept style output and merge/minify all enqueued styles
	* into one stylesheet.
	*
	* Makes use of custom WP Action 'prso_minify_merge_styles' which de-enqueues all styles and enqueues
	* the new merged stylesheet. Note it will ignore all WP Core stylesheets and process only those in
	* /wp-content/ thus all plugins and theme styles.
	*
	* Param - $args array:
	*	- 'merged_path' REQUIRED, Full PATH to your new merged stylesheet file
	*	- 'merged_url' REQUIRED, URL to new merged stylesheet
	*	- 'enqueue_handle' Shouldn't need to change this as default should work fine without conflict
	*
	* @access 	public
	* @author	Ben Moody
	*/
	public function merge_styles() {
		
		//Init vars
		$args = array();
		$exceptions = array();
		
		//Get vars from config.php
		if( isset($this->theme_style_merge_args) ) {
			$args = $this->theme_style_merge_args;
		}
		
		if( isset($this->theme_style_merge_exceptions) ) {
			$exceptions = $this->theme_style_merge_exceptions;
		}
		
		if( isset($args['merged_path']) && !empty($args['merged_path']) ) {
			
			//Before calling the merge action prepend the theme's stylesheet dir and uri to args
			$args['merged_url'] = get_stylesheet_directory_uri() . $args['merged_path'];
			$args['merged_path'] = get_stylesheet_directory() . $args['merged_path'];
			
			do_action( 'prso_minify_merge_styles', $args, $exceptions );
			
		}
		
	}
	
	/**
	* theme_pagination
	*
	* Called using custom action 'prso_pagination' within theme template files
	*
	* NOTE: If you wish to disable (use WP default prev/next post links set $theme_custom_pagination in config.php
	*		If you want to use your own pagination then you can always create a function in your child theme's functions.php
	*		AND add the function's name to the $theme_custom_pagination_override var in config.php
	*
	* Param
	*		To enable/disable use the $theme_custom_pagination var in config.php
	*		To override this function in your child theme functions.php add function name to $theme_custom_pagination_override var in config.php
	*
	* @param	$before		string	String to place before the pagination output
	* @param	$after		string	String to place after the pagination output
	* @access 	public
	* @author	Ben Moody
	*/
	public function custom_pagination( $before = '', $after = '' ) {
		
		//Init vars
		global $wpdb, $wp_query;
		$pagination_active 	= TRUE;
		$override_function	= NULL;
		$output				= NULL; //Output WP prev/next link pagination fallback if required
		
		//Cache pagination status from framework config
		if( isset($this->theme_custom_pagination) && is_bool($this->theme_custom_pagination) ) {
			$pagination_active = $this->theme_custom_pagination;
		}
		
		//Cache pagination override function name string from config
		if( isset($this->theme_custom_pagination_override) && !empty($this->theme_custom_pagination_override) ) {
			$override_function = esc_attr($this->theme_custom_pagination_override);
		}
		
		//First check if someone has with disabled this function or overridden it
		if( $pagination_active === TRUE && empty($override_function) ) {
			
			$request 		= $wp_query->request;
			$posts_per_page = intval(get_query_var('posts_per_page'));
			$paged 			= intval(get_query_var('paged'));
			$numposts 		= $wp_query->found_posts;
			$max_page 		= $wp_query->max_num_pages;
			
			
			if ( $numposts <= $posts_per_page ) { return; }
			if(empty($paged) || $paged == 0) {
				$paged = 1;
			}
			$pages_to_show = 7;
			$pages_to_show_minus_1 = $pages_to_show-1;
			$half_page_start = floor($pages_to_show_minus_1/2);
			$half_page_end = ceil($pages_to_show_minus_1/2);
			$start_page = $paged - $half_page_start;
			if($start_page <= 0) {
				$start_page = 1;
			}
			$end_page = $paged + $half_page_end;
			if(($end_page - $start_page) != $pages_to_show_minus_1) {
				$end_page = $start_page + $pages_to_show_minus_1;
			}
			if($end_page > $max_page) {
				$start_page = $max_page - $pages_to_show_minus_1;
				$end_page = $max_page;
			}
			if($start_page <= 0) {
				$start_page = 1;
			}
				
			echo $before.'<ul class="pagination clearfix">'."";
			if ($paged > 1) {
				$first_page_text = "&laquo";
				echo '<li class="prev"><a href="'.get_pagenum_link().'" title="First">'.$first_page_text.'</a></li>';
			}
				
			echo '<li class="">';
			previous_posts_link('&larr; Previous');
			echo '</li>';
			for($i = $start_page; $i  <= $end_page; $i++) {
				if($i == $paged) {
					echo '<li class="current"><a href="#">'.$i.'</a></li>';
				} else {
					echo '<li><a href="'.get_pagenum_link($i).'">'.$i.'</a></li>';
				}
			}
			echo '<li class="">';
			next_posts_link('Next &rarr;');
			echo '</li>';
			if ($end_page < $max_page) {
				$last_page_text = "&raquo;";
				echo '<li class="next"><a href="'.get_pagenum_link($max_page).'" title="Last">'.$last_page_text.'</a></li>';
			}
			echo '</ul>'.$after."";
			
		}
		
		//Ok let's see if we should output default WP prev/next pagination links
		if( $pagination_active === FALSE && empty($override_function) ) {
			
			ob_start();
			?>
			<nav class="wp-prev-next">
				<ul class="clearfix">
					<?php if( get_previous_posts_link() !== NULL ): ?>
					<li class="prev-link"><?php previous_posts_link(__('&laquo; Older Entries', "prso_theme")); ?></li>
					<?php endif; ?>
					<?php if( get_next_posts_link() !== NULL ): ?>
					<li class="next-link"><?php next_posts_link(__('Newer Entries &raquo;', "prso_theme")); ?></li>
					<?php endif; ?>
				</ul>
			</nav>
			<?php
			$output = ob_get_contents();
			ob_end_clean();
			
			echo $output;
			
		}
		
		//Last thing, lets see if the child theme has overriden this function
		if( !empty($override_function) && function_exists($override_function) ) {
			//Call the override function
			call_user_func_array($override_function);
		}
		
	}
	
	/**
	* post_class_filter
	* 
	* Used to filter the classes returned to the page by post_class()
	*
	* @param	array	$classes	-	Array of class names for a post
	* @access 	public
	* @author	Ben Moody
	*/
	public function post_class_filter( $classes = array() ) {
		
		if( !empty($classes) ) {
			
			// Change 'sticky' class to 'sticky-post', avoid conflict with .sticky used by zurb topbar
			// to make nav bars stick to the top of the page
			foreach( $classes as $key => $class ) {
				if( $class === 'sticky' ) {
					$classes[$key] = 'sticky-post';
				}
			}
			
		}
		
		return $classes;
	}
	
	/**
	* gravity_forms_customizer
	* 
	* Includes the Prso Gravity Forms Customizer inc file
	* This file alters how gravity forms renders some key form fields so that
	* they conform to Zurb Foundation standards.
	*
	* It also provides some custom filters/actions for you to use in your child theme
	* to customize forms further than gravity forms allows by default.
	*
	* @access 	private
	* @author	Ben Moody
	*/
	private function gravity_forms_customizer() {
		
		//Init vars
		$file_path 			= get_template_directory() . "/prso_framework/includes/gravity_forms_custom.php";
		$child_file_path	= get_stylesheet_directory() . "/prso_framework/includes/gravity_forms_custom.php";
		
		if( file_exists($child_file_path) ) {
		
			require_once( $child_file_path ); //Child theme override
			
		} elseif( file_exists($file_path) ) {
		
			require_once( $file_path );
			
		}
		
	}
	
	/**
	* admin_init_theme_options_acf
	* 
	* Includes the theme shortcode file
	* Includes file to load all theme shortcodes
	*
	* @access 	private
	* @author	Ben Moody
	*/
	public function admin_init_theme_options_acf() {
		
		//Init vars
		$options = $this->theme_acf_options_args;
		
		if( function_exists('acf_add_options_page') ) {
			
			//Setup main options page
			if( isset($options['main_page']['page_title']) ) {
				acf_add_options_page( $options['main_page'] );
			}
			
			//Setup any sub pages
			if( isset($options['sub_pages']) && is_array($options['sub_pages']) ) {
				foreach( $options['sub_pages'] as $sub_page_args ) {
					
					$sub_page_args['parent_slug'] = $options['main_page']['menu_slug'];
					
					acf_add_options_sub_page( $sub_page_args );
					
				}
			}
			
		}
		
	}
	
	/**
	* init_theme_textdomain
	* 
	* Loads the theme's translated strings
	*
	* @access 	private
	* @author	Ben Moody
	*/
	public function init_theme_textdomain() {
		
		load_theme_textdomain( PRSOTHEMEFRAMEWORK__DOMAIN, get_stylesheet_directory() . '/languages' );
		
	}

	/**
	* oembed_zurb_video_wrapper
	*
	* @called by 'oembed_result' filter
	*
	* Detects any youtube or vimeo video emebeds and wraps them in the correct
	* zurb foundation flex video wrapper
	*
	* @access 	public
	* @author	Ben Moody
	*/
	public function oembed_zurb_video_wrapper( $html, $url, $args ) {

		//Init vars
		$output 			= NULL;
		$wrapper_start		= NULL;

		//Detect if this is vimeo
		if( strpos($url, 'vimeo') > 0 ) {
			$wrapper_start = "<div class='flex-video widescreen vimeo'>";
		} elseif( strpos($url, 'youtube') > 0 ) {
			$wrapper_start = "<div class='flex-video widescreen'>";
		}

		//Wrap embed code
		if( !empty($wrapper_start) ) {
			$output = $wrapper_start . $html . '</div>';
		} else {
			$output = $html;
		}

		return $output;

	}
	
	//Fix chrome admin area bug
	function chromefix_inline_css() { 
	 	wp_add_inline_style( 'wp-admin', '#adminmenu { transform: translateZ(0); }' );
	}
	
	
	
}