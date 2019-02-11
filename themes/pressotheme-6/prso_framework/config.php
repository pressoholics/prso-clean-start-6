<?php
/**
 * Config
 *
 * Sets all constant definitions for the Pressoholics theme framework
 *
 * PHP versions 4 and 5
 *
 * @copyright     Pressoholics (http://pressoholics.com)
 * @link          http://pressoholics.com
 * @package       pressoholics theme framework
 * @since         Pressoholics v 0.1
 */
class PrsoThemeConfig {
	
/**
 * Contents
 *
 * 1. Thumbnails
 * 2. Nav Menus
 * 3. Sidebars
 * 4. Post Formats
 * 5. Theme Customization
 * 6. Scripts
 * 7. Wordpress Dashboard
 * 8. Wordpress User Admin Page
 * 9. Tag cloud widget args
 * 10. Merge scripts
 * 11. Merge stylesheets
 * 12. Custom pagination
 * 13. Cufon font replacement
 * 14. Backstretch image background
 * 15. Waypoints script
 * 16. Skrollr Script
 * 17. ACF Theme Options Page Setup
 *
 */
 
/******************************************************************
 * 1. 	Thumbnails
 *		Define your custom image sizes for wordpress to create
 *****************************************************************/ 

 	/**
	* $this->theme_thumbnail_settings
	* 
	* Register/Change theme thumbnails
	* 
	* $theme_thumbnail_settings[{thumbnail-name}] = array(
	*  		'width' 	=> '',
	*  		'height'	=> '',
	*  		'crop'		=> false
	*  )
	*/
 	protected $theme_thumbnail_settings = array(
 		'thumbnail' => array(
 			'width' 	=> 150,
 			'height'	=> 150,
 			'crop'		=> true
 		),
	 	'prso-orbit' => array(
	 			'width' 	=> 970,
	 			'height'	=> 364,
	 			'crop'		=> true
	 	),
	 	'prso-orbit-thumbnail' => array(
	 			'width' 	=> 100,
	 			'height'	=> 75,
	 			'crop'		=> true
	 	),
	 	'prso-thumb-600' => array(
	 			'width' 	=> 600,
	 			'height'	=> 150,
	 			'crop'		=> false
	 	),
	 	'prso-thumb-300' => array(
	 			'width' 	=> 300,
	 			'height'	=> 100,
	 			'crop'		=> true
	 	)
 	);



/******************************************************************
 * 2. 	Nav Menus
 *		Register any navigation locations in your theme here
 *****************************************************************/
 
 	/**
	* $this->theme_nav_menus
	* 
	* Register theme nav menus
	* 
	* array(
	*  		'nav_slug' => 'Nav Title',
	*  )
	*/
 	protected $theme_nav_menus = array( 
		'main_nav' => 'The Main Menu',   // main nav in header
		'footer_links' => 'Footer Links', // secondary nav in footer
		'mobile_nav' => 'Mobile Menu' // menu for mobile devices
	);
 
 
/******************************************************************
 * 3. 	Sidebars
 *		Register your theme's sidebars here
 *****************************************************************/
 
 	/**
	* $this->theme_sidebar_settings
	* 
	* Register theme sidebars
	* 
	* $theme_sidebar_settings[{sidebar_slug}] = array(
	*  		'id' => 'sidebar1',
	*    	'name' => 'Main Sidebar',
	*    	'description' => 'Used on every page BUT the homepage page template.',
	*		'class'         => '',
	*    	'before_widget' => '<aside id="%1$s" class="widget %2$s">',
	*    	'after_widget' => '</aside>',
	*    	'before_title' => '<h4 class="widgettitle">',
	*    	'after_title' => '</h4>'
	*  )
	*/
	protected $theme_sidebar_settings = array(
		'sidebar_main' => array(
	    	'id' => 'sidebar_main',
	    	'name' => 'Main Sidebar',
	    	'description' => 'Used on every page BUT the homepage page template.',
	    	'before_widget' => '<aside id="%1$s" class="widget %2$s">',
	    	'after_widget' => '</aside>',
	    	'before_title' => '<h4 class="widgettitle">',
	    	'after_title' => '</h4>',
	    ),
	    'sidebar_home' => array(
	    	'id' => 'sidebar_home',
	    	'name' => 'Homepage Sidebar',
	    	'description' => 'Used only on the homepage page template.',
	    	'before_widget' => '<aside id="%1$s" class="widget %2$s">',
	    	'after_widget' => '</aside>',
	    	'before_title' => '<h4 class="widgettitle">',
	    	'after_title' => '</h4>',
	    ),
	    'sidebar_blog_home' => array(
	    	'id' => 'sidebar_blog_home',
	    	'name' => 'Blog Home Sidebar',
	    	'description' => 'Used only on the blog home page template. NOTE, will overide Blog Sidebar on this specific page.',
	    	'before_widget' => '<aside id="%1$s" class="widget %2$s">',
	    	'after_widget' => '</aside>',
	    	'before_title' => '<h4 class="widgettitle">',
	    	'after_title' => '</h4>',
	    ),
	    'sidebar_blog' => array(
	    	'id' => 'sidebar_blog',
	    	'name' => 'Blog Sidebar',
	    	'description' => 'Used only on the blog page template.',
	    	'before_widget' => '<aside id="%1$s" class="widget %2$s">',
	    	'after_widget' => '</aside>',
	    	'before_title' => '<h4 class="widgettitle">',
	    	'after_title' => '</h4>',
	    ),
	    'sidebar_search' => array(
	    	'id' => 'sidebar_search',
	    	'name' => 'Search Sidebar',
	    	'description' => 'Used only on the search results archive template.',
	    	'before_widget' => '<aside id="%1$s" class="widget %2$s">',
	    	'after_widget' => '</aside>',
	    	'before_title' => '<h4 class="widgettitle">',
	    	'after_title' => '</h4>',
	    ),
	    'sidebar_mobile' => array(
	    	'id' => 'sidebar_mobile',
	    	'name' => 'Mobile Sidebar',
	    	'description' => 'Used only for visitors on Mobile and Tablet.',
	    	'before_widget' => '<aside id="%1$s" class="widget widget-mobile %2$s">',
	    	'after_widget' => '</aside>',
	    	'before_title' => '<h4 class="widgettitle">',
	    	'after_title' => '</h4>',
	    )
	);
 
 
/******************************************************************
 * 4. 	Post Formats
 *		Register your themes post formats
 *****************************************************************/ 
 
 	/**
	* $this->theme_post_formats
	* 
	* Setup theme post-formats support
	* 
	* array(
	*		'aside',   // title less blurb
	*		'gallery', // gallery of images
	*		'link',    // quick link to other site
	*		'image',   // an image
	*		'quote',   // a quick quote
	*		'status',  // a Facebook like status update
	*		'video',   // video 
	*		'audio',   // audio
	*		'chat'     // chat transcript 
	*	);
	*/
	protected $theme_post_formats = array(
			'aside',   // title less blurb
			'gallery', // gallery of images
			'link',    // quick link to other site
			'image',   // an image
			'quote',   // a quick quote
			'status',  // a Facebook like status update
			'video',   // video 
			'audio',   // audio
			'chat'     // chat transcript 
	);
 
 
/******************************************************************
 * 5. 	Theme Customization
 *		Define your theme's default customization vars - background, ect
 *****************************************************************/ 
 
 	/**
	* $this->theme_custom_background
	* 
	* Set options for theme custom-background support
	* 
	* array(
	*  	'default-color'          => '',
	*	'default-image'          => '',
	*	'wp-head-callback'       => '_custom_background_cb',
	*	'admin-head-callback'    => '',
	*	'admin-preview-callback' => ''
	*  )
	*/
 	protected $theme_custom_background = array(
		'default-color'          => 'ffffff'
	);
 
 
/******************************************************************
 * 6. 	Scripts
 *		Define certain script settings here
 *****************************************************************/ 
 
 	/**
	* $this->theme_google_jquery_url
	* 
	* The url for Google jQuery library, used in front end only
	*/
	protected $theme_google_jquery_url = 'http://ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js';

 
/******************************************************************
 * 7. 	Wordpress Dashboard
 *		Cutsomize the main wordpress dashboard for users
 *****************************************************************/
 
 	/**
	* $this->admin_disable_dashboard_widgets
	* 
	* Remove admin dashboard widgets
	* 
	* $admin_disable_dashboard_widgets[] = array(
	*  		'id' 		=> '',
	*		'context'	=> ''
	*  )
	*/
	protected $admin_disable_dashboard_widgets = array(
		array(
			'id' 		=> 'dashboard_recent_comments',
			'context'	=> 'core'
		),
		array(
			'id' 		=> 'dashboard_incoming_links',
			'context'	=> 'core'
		),
		array(
			'id' 		=> 'dashboard_recent_drafts',
			'context'	=> 'core'
		),
		array(
			'id' 		=> 'dashboard_primary',
			'context'	=> 'core'
		),
		array(
			'id' 		=> 'dashboard_secondary',
			'context'	=> 'core'
		),
		array(
			'id' 		=> 'dashboard_plugins',
			'context'	=> 'core'
		)
	);
 
 
/******************************************************************
 * 8. 	Wordpress User Admin Page
 *		Customize aspects of the wordpress user admin view
 *****************************************************************/ 
 
 	/**
	* $this->admin_user_contact_methods
	* 
	* Add more contact fields to user profiles
	* 
	* array(
	  		'field_slug' => 'Field Name',
	  )
	*/
	protected $admin_user_contact_methods = array(
		'user_fb' 			=> 'Facebook',
		'user_tw'			=> 'Twitter',
		'google_profile'	=> 'Google Profile URL'
	);
 
/******************************************************************
 * 9. 	Tag cloud widget args
 *		Alter the wp tag cloud widget output
 *****************************************************************/ 

 	/**
	* $this->theme_tag_cloud_args
	* 
	* Add more contact fields to user profiles
	* 
	* array(
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
	*/
	protected $theme_tag_cloud_args = array(
		'number'	=>	20,		// show less tags
		'largest'	=>	9.75,	// make largest and smallest the same - i don't like the varying font-size look
		'smallest'	=>	9.75,	// make largest and smallest the same - i don't like the varying font-size look
		'unit'		=>	'px',
		'is_mobile'	=> array(	//Change sizes for mobile if requested
			'number'	=>	10,		
			'largest'	=>	1.5,	
			'smallest'	=>	1.5,	
			'unit'		=>	'rem',
		)
	);

/******************************************************************
 * 10. 	Merge Scripts
 *		Define some handles of scripts to auto merge and minify
 *****************************************************************/

 	/**
 	* $this->theme_script_merge_args
 	*
 	* NOTE :: To disable script auto merging empty/comment out this array
 	*
 	* Param - array:
	*	- 'merged_path' REQUIRED, PATH to your new merged scripts file, RELATIVE to stylesheet_directory, e.g. '/js/app.min.js'
	*	- 'depends' Array of script handles to be enqueued BEFORE the min script, e.g. 'jquery'
	*	- 'handles' Array of script handles to merge, if empty ALL theme AND plugin scripts will be merged
	*	- 'enqueue_handle' Shouldn't need to change this as default should work fine without conflict
	*/
	/*
	protected $theme_script_merge_args = array(
		'merged_path' 		=> '/javascripts/app.min.js',
		'depends'			=> array( 'jquery' ),
		'handles'			=> array( 
			'foundation-core', 'foundation-abide', 'foundation-accordion', 'foundation-accordion-menu', 'foundation-drilldown', 'foundation-dropdown', 'foundation-dropdown-menu'
			, 'foundation-equalizer', 'foundation-interchange', 'foundation-magellan', 'foundation-offcanvas', 'foundation-orbit'
			, 'foundation-responsive-menu', 'foundation-responsive-toggle', 'foundation-reveal', 'foundation-slider', 'foundation-sticky'
			, 'foundation-tabs', 'foundation-toggler', 'foundation-tooltip', 'foundation-util-box', 'foundation-util-keyboard'
			, 'foundation-util-media-query', 'foundation-util-motion', 'foundation-util-nest', 'foundation-util-timer-and-image-loader'
			, 'foundation-util-touch', 'foundation-util-triggers', 'fresco', 'prso-theme-app'
		)
	);
	*/
	
	/**
 	* $this->theme_script_merge_exceptions
 	*
 	* NOTE: To ignore a script add it's enqueue handle to $theme_script_merge_exceptions array
 	*
 	* e.g. array('jquery');
	*/
	protected $theme_script_merge_exceptions = array();
	
/******************************************************************
 * 11. 	Merge Stylesheets
 *		Define path to where your auto merged stylsheet should go (relative to theme root)
 *****************************************************************/

 	/**
 	* $this->theme_style_merge_args
 	*
 	* NOTE :: To disable sylesheet auto merging empty/comment out this array
 	*
 	* Param - array:
	*	- 'merged_path' REQUIRED, PATH to your new merged stylesheet file, RELATIVE to stylesheet_directory, e.g. '/css/app-min.css'
	*	- 'enqueue_handle' Shouldn't need to change this as default should work fine without conflict
	*/
	/*
	protected $theme_style_merge_args = array(
		'merged_path' 	=> '/stylesheets/app-min.css'
	);
	*/
	
	/**
 	* $this->theme_style_merge_exceptions
 	*
 	* NOTE: To ignore a stylesheet add it's enqueue handle to $theme_style_merge_exceptions array
 	*
 	* e.g. array('gforms_css');
	*/
	protected $theme_style_merge_exceptions = array();

/******************************************************************
 * 12. 	Custom Pagination
 *		Control/Override the 'prso_pagination' action which handles pagination in theme files
 *****************************************************************/

 	/**
 	* $this->theme_custom_pagination
 	*
 	* NOTE :: Set to FALSE to disable custom pagination and use WP default prev/next links
 	*
	*/
	protected $theme_custom_pagination = TRUE;
	
	/**
 	* $this->theme_custom_pagination_override
 	*
 	* NOTE :: If you want to use a custom pagination function in child theme functions.php
 	*			just add the name of the function here and 'prso_pagination' will call that function for you.
 	*
	*/
	protected $theme_custom_pagination_override = NULL;

/******************************************************************
 * 13. 	Cufon font replacement
 *		
 *****************************************************************/

 	/**
 	* $this->theme_cufon_script_args
 	*
 	* NOTE :: Comment out to disable cufon script OR leave empty to use defaults
 	*
 	* array(
			'handle'		=>	'cufon',
			'script_cdn'	=>	'http://cdnjs.cloudflare.com/ajax/libs/cufon/1.09i/cufon-yui.js',
			'script'		=>	get_template_directory_uri() . '/javascripts/cufon-yui.js',
			'version'		=>	'1.09i'
		);
 	*
	*/
	//protected $theme_cufon_script_args = array();

/******************************************************************
 * 14. 	Backstretch background image script
 *		
 *****************************************************************/

 	/**
 	* $this->theme_backstretch_script_args
 	*
 	* NOTE :: Comment out to disable script OR leave empty to use defaults
 	*
 	* array(
			'handle'		=>	'backstretch',
			'script_cdn'	=>	'http://cdnjs.cloudflare.com/ajax/libs/jquery-backstretch/2.0.3/jquery.backstretch.min.js ',
			'script'		=>	get_template_directory_uri() . '/javascripts/jquery/jquery.backstretch.min.js',
			'version'		=>	'2.0.3'
		);
 	*
	*/
	//protected $theme_backstretch_script_args = array();
	
/******************************************************************
 * 15. 	Waypoints script
 *		
 *****************************************************************/

 	/**
 	* $this->theme_waypoints_script_args
 	*
 	* NOTE :: Comment out to disable script OR leave empty to use defaults
 	*
 	* array(
			'handle'		=>	'jquery-waypoints',
			'script_cdn'	=>	'http://cdnjs.cloudflare.com/ajax/libs/waypoints/2.0.2/waypoints.min.js',
			'script'		=>	get_template_directory_uri() . '/javascripts/jquery/jquery.waypoints.min.js',
			'version'		=>	'2.0.2'
		);
 	*
	*/
	//protected $theme_waypoints_script_args = array();
	
/******************************************************************
 * 16. 	Skrollr script
 *		
 *****************************************************************/

 	/**
 	* $this->theme_skrollr_script_args
 	*
 	* NOTE :: Comment out to disable script OR leave empty to use defaults
 	*
 	* array(
			'handle'		=>	'skrollr',
			'script_cdn'	=>	'https://cdnjs.cloudflare.com/ajax/libs/skrollr/0.6.29/skrollr.min.js',
			'script'		=>	get_template_directory_uri() . '/javascripts/skrollr/skrollr.js',
			'version'		=>	'0.6.29'
		);
 	*
	*/
	//protected $theme_skrollr_script_args = array();

/******************************************************************
 * 17. 	ACF Theme Options Page Setup
 *		
 *****************************************************************/
  	
  	/**
 	* $this->theme_acf_options_args
 	*
	*/
	protected $theme_acf_options_args = array(
			'main_page'	=>	array(
				'page_title' 	=> 'Theme General Settings',
				'menu_title'	=> 'Theme Settings',
				'menu_slug' 	=> 'prso-theme-settings',
				'capability'	=> 'edit_posts',
				'redirect'		=> false
			),
			'sub_pages'	=> array(
				array(
					'page_title' 	=> 'Sub Page Example',
					'menu_title'	=> 'Sub Page'
				)
			)
	);
	
/******************************************************************
 * 18. 	Setup Foundation Script Enqueue
 *		
 *****************************************************************/
  	
  	/**
 	* $this->theme_foundation_script_arg
 	*
 	protected $theme_foundation_script_arg = array(
		'foundation-abide', 
		'foundation-accordion', 
		'foundation-accordion-menu', 
		'foundation-drilldown', 
		'foundation-dropdown', 
		'foundation-dropdown-menu', 
		'foundation-equalizer', 
		'foundation-interchange', 
		'foundation-magellan', 
		'foundation-offcanvas', 
		'foundation-orbit', 
		'foundation-responsive-menu', 
		'foundation-responsive-toggle', 
		'foundation-reveal', 
		'foundation-slider', 
		'foundation-sticky', 
		'foundation-tabs', 
		'foundation-toggler', 
		'foundation-tooltip', 
		'foundation-util-box', 
		'foundation-util-keyboard', 
		'foundation-util-media-query', 
		'foundation-util-motion', 
		'foundation-util-nest', 
		'foundation-util-timer-and-image-loader',
		'foundation-util-touch',
		'foundation-util-triggers',
		'fresco'
	);
	*/
	protected $theme_foundation_script_arg = array(
		'foundation-abide', 
		'foundation-accordion', 
		'foundation-accordion-menu', 
		'foundation-drilldown', 
		'foundation-dropdown', 
		'foundation-dropdown-menu', 
		'foundation-equalizer', 
		'foundation-interchange', 
		'foundation-magellan', 
		'foundation-offcanvas', 
		'foundation-orbit', 
		'foundation-responsive-menu', 
		'foundation-responsive-toggle', 
		'foundation-reveal', 
		'foundation-slider', 
		'foundation-sticky', 
		'foundation-tabs', 
		'foundation-toggler', 
		'foundation-tooltip', 
		'foundation-util-box', 
		'foundation-util-keyboard', 
		'foundation-util-media-query', 
		'foundation-util-motion', 
		'foundation-util-nest', 
		'foundation-util-timer-and-image-loader',
		'foundation-util-touch',
		'foundation-util-triggers',
		'fresco' //Lightbox
	);
  	
//***** END -- THEME OPTIONS - DON'T EDIT PASSED HERE!! *****//
	
}