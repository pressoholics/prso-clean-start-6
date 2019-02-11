<?php
/**
 * Generate custom post type and taxonomies using http://generatewp.com
 *
 * Visit http://generatewp.com, generate the code and paste it into the correct
 * function Be sure to update the text domain to 'prso-child-theme-domain' ==
 * 'prso-child-theme-domain' constant for prso themes
 *
 * Find and replace cpt_slug AND Slug with your unqiue post type name
 *
 * @access    public
 * @author    Ben Moody
 */

class PrsoSlugPostType {

	public static $post_type_slug = 'cpt_slug';
	public static $post_type_rewrite_slug = 'cpt-slug';
	public static $post_type_archive_slug = false;
	public static $post_type_rest_slug = false;

	public static $post_type_singular = 'cpt_singular';
	public static $post_type_plural = 'cpt_plural';

	public static $dashicon = 'dashicons-format-aside';
	public static $query_var = false;

	function __construct() {

		$post_type_slug = self::$post_type_slug;

		//Register post type
		//add_action( 'init', array( $this, 'register_post_type' ), 1 );

		//Hide front end from users
		//add_action( 'wp', array($this, 'hide_from_public') );

		//Add list table filters for taxonomy
		//add_action( 'init', array($this, 'taxonomy_filters'), 0 );

		//Filter theme wp_api local object for this post type
		//		add_filter( 'prso_theme_localize__wp_api', array(
		//			$this,
		//			'wp_api_local_object',
		//		) );

		//Detect and handle any custom query vars
		//		add_action( 'pre_get_posts', array(
		//			$this,
		//			'detect_custom_query_variable',
		//		) );

		//REST prepare post
		//add_filter( "rest_prepare_{$post_type_slug}", array($this, 'rest_prepare'), 999, 3 );

		//Setup custom list table columns
		//add_filter( "manage_{$post_type_slug}_posts_columns", array($this, 'columns_filter'), 10, 1 );

		//Render output for list table columns
		//add_action( "manage_{$post_type_slug}_posts_custom_column", array($this, 'column_action'), 10, 1 );

		//Add ACF options page to custom post type in admin nav
		//		add_action(
		//			'init',
		//			array(
		//				$this,
		//				'acf_options_pages'
		//			)
		//		);

	}

	public function hide_from_public() {

		global $wp_query;

		if ( is_singular( self::$post_type_slug ) ) {

			$wp_query->set_404();
			status_header( 404 );

		}

	}

	/**
	 * Add post filters for these taxonomies to admin area
	 *
	 *
	 * @access    public
	 * @author    Ben Moody
	 */
	public function taxonomy_filters() {

		/*
		new Tax_CTP_Filter(array(
			self::$post_type_slug => array('taxonomy_slug')
		));
		*/

	}

	/**
	 * Setup post type
	 *
	 * First let's register our custom post type
	 *
	 * @access    public
	 * @author    Ben Moody
	 */
	public function register_post_type() {

		//vars
		$rewrite = array();

		$labels = array(
			'name'                  => self::$post_type_plural,
			'singular_name'         => self::$post_type_singular,
			'menu_name'             => self::$post_type_plural,
			'name_admin_bar'        => self::$post_type_plural,
			'archives'              => __( 'Item Archives', 'prso-child-theme-domain' ),
			'attributes'            => __( 'Item Attributes', 'prso-child-theme-domain' ),
			'parent_item_colon'     => __( 'Parent Item:', 'prso-child-theme-domain' ),
			'all_items'             => __( 'All Items', 'prso-child-theme-domain' ),
			'add_new_item'          => __( 'Add New Item', 'prso-child-theme-domain' ),
			'add_new'               => __( 'Add New', 'prso-child-theme-domain' ),
			'new_item'              => __( 'New Item', 'prso-child-theme-domain' ),
			'edit_item'             => __( 'Edit Item', 'prso-child-theme-domain' ),
			'update_item'           => __( 'Update Item', 'prso-child-theme-domain' ),
			'view_item'             => __( 'View Item', 'prso-child-theme-domain' ),
			'view_items'            => __( 'View Items', 'prso-child-theme-domain' ),
			'search_items'          => __( 'Search Item', 'prso-child-theme-domain' ),
			'not_found'             => __( 'Not found', 'prso-child-theme-domain' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'prso-child-theme-domain' ),
			'featured_image'        => __( 'Featured Image', 'prso-child-theme-domain' ),
			'set_featured_image'    => __( 'Set featured image', 'prso-child-theme-domain' ),
			'remove_featured_image' => __( 'Remove featured image', 'prso-child-theme-domain' ),
			'use_featured_image'    => __( 'Use as featured image', 'prso-child-theme-domain' ),
			'insert_into_item'      => __( 'Insert into item', 'prso-child-theme-domain' ),
			'uploaded_to_this_item' => __( 'Uploaded to this item', 'prso-child-theme-domain' ),
			'items_list'            => __( 'Items list', 'prso-child-theme-domain' ),
			'items_list_navigation' => __( 'Items list navigation', 'prso-child-theme-domain' ),
			'filter_items_list'     => __( 'Filter items list', 'prso-child-theme-domain' ),
		);

		if ( ! empty( self::$post_type_rewrite_slug ) ) {

			$rewrite = array(
				'slug'       => self::$post_type_rewrite_slug,
				'with_front' => true,
				'pages'      => true,
				'feeds'      => true,
			);

		}

		$args = array(
			'label'               => self::$post_type_singular,
			'description'         => __( 'List of all posts', 'prso-child-theme-domain' ),
			'labels'              => $labels,
			'supports'            => array(
				'title',
				'editor',
				'thumbnail',
				'excerpt',
			),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 5,
			'menu_icon'           => self::$dashicon,
			'show_in_admin_bar'   => false,
			'show_in_nav_menus'   => false,
			'can_export'          => true,
			'has_archive'         => self::$post_type_archive_slug,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'rewrite'             => $rewrite,
			'capability_type'     => 'page',
			'show_in_rest'        => true,
			'rest_base'           => self::$post_type_rest_slug,
			'query_var'           => self::$query_var,
		);
		register_post_type( self::$post_type_slug, $args );

	}

	/**
	 * wp_api_local_object
	 *
	 * @CALLED BY FILTER 'prso_theme_localize__wp_api'
	 *
	 * Fitler local object for theme and add in rest api data for this post type
	 *
	 * @param array $local_object
	 *
	 * @return array $local_object
	 * @access public
	 * @author Ben Moody
	 */
	public function wp_api_local_object( $local_object ) {

		if ( ! is_post_type_archive( self::$post_type_slug ) ) {
			return $local_object;
		}

		$local_object[ self::$post_type_slug ] = array(
			'restRoute' => rest_url( 'wp/v2/' . self::$post_type_rest_slug ),
			'perPage'   => get_option( 'posts_per_page' ),
		);

		return $local_object;
	}

	/**
	 * detect_custom_query_variable
	 *
	 * @CALLED BY /ACTION 'pre_get_posts'
	 *
	 * Detect taxonomy custom query variable in query, if found add tax_query
	 *     to the query object
	 *
	 * @access public
	 * @author Ben Moody
	 */
	public function detect_custom_query_variable( $query ) {

		//vars
		$query_var      = false;
		$taxonomy_query = false;

		if ( is_admin() ) {
			return;
		}

		//Do we have an audience query request
		$query_var = $query->get( self::$query_var, false );

		if ( false === $query_var ) {
			return;
		}

	}

	/**
	 * Manage custom post type index view table columns
	 *
	 * Add or remove columns from the index table for your custom post type
	 *
	 *
	 * https://codex.wordpress.org/Plugin_API/Filter_Reference/manage_$post_type_posts_columns
	 * @access    public
	 * @author    Ben Moody
	 */
	public function columns_filter( $columns ) {

		$column_thumbnail = array(
			'thumbnail' => 'Thumbnail',
		);

		$columns = array_slice( $columns, 0, 1, true ) + $column_thumbnail + array_slice( $columns, 1, null, true );

		return $columns;
	}

	/**
	 * Add content to index view columns
	 *
	 * @access    public
	 * @author    Ben Moody
	 */
	public function column_action( $column ) {

		global $post;

		switch ( $column ) {
			case 'thumbnail':
				echo get_the_post_thumbnail( $post->ID, 'edit-screen-thumbnail' );
				break;
		}

	}

	/**
	 * rest_prepare
	 *
	 * @CALLED BY FILTER 'rest_prepare_cpt_slug'
	 *
	 * Filter the rest output for this post type
	 *
	 * @access public
	 * @author Ben Moody
	 */
	public function rest_prepare( $response, $current_post, $request ) {

		//vars
		global $post;
		$post_type_slug = self::$post_type_slug;

		$post = $current_post;

		setup_postdata( $post );

		ob_start();

		get_template_part( "/template_parts/{$post_type_slug}/part", 'result_item' );

		$response->data['html'] = ob_get_contents();
		ob_end_clean();

		wp_reset_postdata();

		return rest_ensure_response( $response );
	}

	/**
	 * acf_options_pages
	 *
	 * @CALLED BY ACTION 'register_acf_options_pages'
	 *
	 * Register any ACF options pages for this taxonomy and call any related
	 *     methods/actions
	 *
	 * @access public
	 * @author Ben Moody
	 */
	public function acf_options_pages() {

		if ( ! function_exists( 'acf_add_options_sub_page' ) ) {
			return;
		}

		//Add options page for post type archive content
		acf_add_options_sub_page(
			array(
				'title'      => esc_html_x( 'Archive Content', 'options page title', 'prso-child-theme-domain' ),
				'parent'     => 'edit.php?post_type=' . self::$post_type_slug,
				'capability' => 'manage_options',
			)
		);

	}

}

new PrsoSlugPostType();