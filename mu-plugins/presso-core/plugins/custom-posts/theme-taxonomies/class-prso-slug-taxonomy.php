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

class PrsoSlugTaxonomy {

	public static $taxonomy_slug = 'cpt_slug';
	public static $taxonomy_rewrite_slug = 'cpt-slug';
	public static $taxonomy_with_front = false;
	
	public static $taxonomy_show_in_rest = false;
	public static $taxonomy_rest_slug = false;

	public static $taxonomy_singular = 'cpt_singular';
	public static $taxonomy_plural = 'cpt_plural';

	public static $query_var = false;

	function __construct() {

		$taxonomy_slug = self::$taxonomy_slug;

		//Register post type
		//add_action( 'init', array( $this, 'register_taxonomy' ), 1 );

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

	}

	public function hide_from_public() {

		global $wp_query;

		if ( is_tax( self::$taxonomy_slug ) ) {

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
			self::$taxonomy_slug => array('taxonomy_slug')
		));
		*/

	}

	/**
	 * Setup taxonomy
	 *
	 * First let's register our custom taxonomy
	 *
	 * @access    public
	 * @author    Ben Moody
	 */
	public function register_taxonomy() {

		$labels = array(
			'name'                       => self::$taxonomy_singular,
			'singular_name'              => self::$taxonomy_singular,
			'menu_name'                  => self::$taxonomy_plural,
			'all_items'                  => __( 'All Items', 'text_domain' ),
			'parent_item'                => __( 'Parent Item', 'text_domain' ),
			'parent_item_colon'          => __( 'Parent Item:', 'text_domain' ),
			'new_item_name'              => __( 'New Item Name', 'text_domain' ),
			'add_new_item'               => __( 'Add New Item', 'text_domain' ),
			'edit_item'                  => __( 'Edit Item', 'text_domain' ),
			'update_item'                => __( 'Update Item', 'text_domain' ),
			'view_item'                  => __( 'View Item', 'text_domain' ),
			'separate_items_with_commas' => __( 'Separate items with commas', 'text_domain' ),
			'add_or_remove_items'        => __( 'Add or remove items', 'text_domain' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'text_domain' ),
			'popular_items'              => __( 'Popular Items', 'text_domain' ),
			'search_items'               => __( 'Search Items', 'text_domain' ),
			'not_found'                  => __( 'Not Found', 'text_domain' ),
			'no_terms'                   => __( 'No items', 'text_domain' ),
			'items_list'                 => __( 'Items list', 'text_domain' ),
			'items_list_navigation'      => __( 'Items list navigation', 'text_domain' ),
		);
		$rewrite = array(
			'slug'                       => self::$taxonomy_rewrite_slug,
			'with_front'                 => self::$taxonomy_with_front,
			'hierarchical'               => false,
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => false,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => true,
			'show_tagcloud'              => true,
			'query_var'                  => self::$query_var,
			'rewrite'                    => $rewrite,
			'show_in_rest'               => self::$taxonomy_show_in_rest,
			'rest_base'                  => self::$taxonomy_rest_slug,
		);
		register_taxonomy( self::$taxonomy_slug, array( 'post' ), $args );
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
		$query_var       = false;
		$taxonomy__query = false;

		if ( is_admin() ) {
			return;
		}

		//Do we have an audience query request
		$query_var = $query->get( self::$query_var, false );

		if ( false === $query_var ) {
			return;
		}

	}

}

new PrsoSlugTaxonomy();