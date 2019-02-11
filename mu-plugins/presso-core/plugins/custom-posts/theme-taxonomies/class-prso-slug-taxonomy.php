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
	public static $taxonomy_archive_slug = false;
	public static $taxonomy_rest_slug = false;

	public static $taxonomy_singular = 'cpt_singular';
	public static $taxonomy_plural = 'cpt_plural';

	public static $dashicon = 'dashicons-format-aside';
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

		//vars


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