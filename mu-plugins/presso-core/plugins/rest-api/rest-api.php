<?php

/**
 * PrsoCustomRestApi
 *
 * Class contains any customisation to rest api
 *∂
 *
 * @access    public
 * @author    Ben Moody
 */
class PrsoCustomRestApi {

	public static $base_api_url = 'prso/v1';

	function __construct() {

		//Register custom rest endpoints
		//$this->init_custom_endpoints();

		//Prevent external access to ALL REST API endpoints
		add_filter( 'rest_authentication_errors', array(
			$this,
			'restrict_external_rest_access',
		) );

		add_filter( 'woocommerce_rest_check_permissions', array(
			$this,
			'woo_restrict_external_rest_access',
		), 999, 4 );


		add_filter( 'rest_prepare_post', array(
			$this,
			'rest_prepare_post',
		), 10, 3 );

		//Register a REST response 'html' field for the search endpoint
		add_action( 'rest_api_init', array(
			$this,
			'search_endpoint_rest_fields',
		), 10 );


		/*
				add_filter( 'rest_post_query', array(
					$this,
					'rest_post_query',
				), 999, 2 );
		*/

	}

	/**
	 * search_endpoint_rest_fields
	 *
	 * @CALLED BY ACTION 'rest_api_init'
	 *
	 * Register a REST response 'html' field for the search endpoint, will
	 *     contain the server rendered template markup
	 *
	 * @access public
	 * @author Ben Moody
	 */
	public function search_endpoint_rest_fields() {

		// schema for the html field
		$schema = array(
			'description' => 'Template HTML for item',
			'type'        => 'string',
			'context'     => array( 'view' ),
		);

		// registering the bs_author_name field
		register_rest_field(
			'search-result',
			'html',
			array(
				'get_callback'    => array( $this, 'rest_prepare_search' ),
				'update_callback' => null,
				'schema'          => $schema,
			)
		);

	}

	/**
	 * rest_prepare_search
	 *
	 * @CALLED BY callback for register_rest_field() call in
	 *     PrsoCustomRestApi::search_endpoint_rest_fields()
	 *
	 * The REST search endpoint doesn't have a rest_prepare filter and it only
	 *     returns a few fields by default. We want to add a html field with
	 *     our server rendered markup.
	 *
	 * PrsoCustomRestApi::search_endpoint_rest_fields() registers the html
	 *     field for the search endpoint and this methods returns the template
	 *     markup as a string
	 *
	 * @param object $object
	 * @param string $field_name
	 * @param object $request
	 *
	 * @return string $output
	 * @access public
	 * @author Ben Moody
	 */
	public function rest_prepare_search( $object, $field_name, $request ) {

		global $post;
		$output = null;
		$post   = get_post( $object['id'] );

		if ( ! is_object( $post ) ) {
			return '';
		}

		setup_postdata( $post );

		ob_start();

		get_template_part( '/template_parts/posts/part', 'grid_item' );

		$output = ob_get_contents();
		ob_end_clean();

		wp_reset_postdata();

		return $output;

	}

	/**
	 * init_custom_endpoints
	 *
	 * @CALLED BY ACTION 'init'
	 *
	 * Includes all class files for each rest endpoint
	 *
	 * @access    public
	 * @author    Ben Moody
	 */
	public function init_custom_endpoints() {

		//Vars
		$cpt_path = dirname( __FILE__ ) . '/custom-endpoints';

		//Include shortcake shortcode for posts
		prso_include_all_files( $cpt_path );

	}

	/**
	 * rest_prepare_post
	 *
	 * @CALLED BY FILTER/ 'rest_prepare_post'
	 *
	 * Filter rest api response for indivdual posts
	 *
	 * @access public
	 * @author Ben Moody
	 */
	function rest_prepare_post( $response, $post_object, $request ) {

		//vars
		global $post;

		$post = $post_object;

		setup_postdata( $post );

		ob_start();

		get_template_part( '/template_parts/posts/part', 'grid_item' );

		$response->data['html'] = ob_get_contents();
		ob_end_clean();

		wp_reset_postdata();

		return rest_ensure_response( $response );
	}

	/**
	 * rest_post_query
	 *
	 * @CALLED BY FILTER/ 'rest_post_query'
	 *
	 * Force posts per page to match WP deafult
	 *
	 * @access public
	 * @author Ben Moody
	 */
	function rest_post_query( $args, $request ) {

		$args['posts_per_page'] = get_option( 'posts_per_page' );
		$args['post_status']    = 'publish';
		$args['orderby']        = 'date';
		$args['order']          = 'DESC';

		//Per page param
		if ( isset( $_GET['per_page'] ) ) {
			$args['posts_per_page'] = intval( $_GET['per_page'] );
		}

		//is search
		if ( isset( $args['s'] ) && ! empty( $args['s'] ) ) {

		}

		//Detect category filter in request
		if ( isset( $request['filter']['cat'] ) ) {

			unset( $args['cat'] );

			$term_id = intval( $request['filter']['cat'] );

			$args['tax_query'] = array(
				array(
					'taxonomy' => 'category',
					'field'    => 'term_id',
					'terms'    => array( $term_id ),
				),
			);

		}

		return $args;
	}

	/**
	 * woo_restrict_external_rest_access
	 *
	 * @CALLED BY FILTER/ 'woocommerce_rest_check_permissions'
	 *
	 * Add our custom rest api access restriction to woocommerece which by
	 *     default only allow logged in users to access it's api
	 *
	 * @access public
	 * @author Ben Moody
	 */
	public function woo_restrict_external_rest_access( $permission, $context, $object_id, $post_type ) {

		//vars
		$custom_rest_access = $this->restrict_external_rest_access();

		if ( is_wp_error( $custom_rest_access ) ) {
			return false;
		}

		return true;
	}

	/**
	 * restrict_external_rest_access
	 *
	 * @CALLED BY FILTER 'rest_authentication_errors'
	 *
	 * Checks and validates HTTP_X_WP_NONCE in request, prevents reqeusts to
	 *     REST API without valid rest api nonce
	 *
	 * @param bool $result
	 *
	 * @return mixed WP_Error/bool
	 * @access public
	 * @author Ben Moody
	 */
	public function restrict_external_rest_access( $result = null ) {

		if ( defined( 'WP_DEBUG' ) && ( true === WP_DEBUG ) ) {
			return true;
		}

		if ( Prso_Gutenberg::is_gutenberg_request() ) {
			return true;
		}

		if ( ! empty( $result ) ) {
			return $result;
		}

		global $wp_rest_auth_cookie;

		/*
		 * Is cookie authentication being used? (If we get an auth
		 * error, but we're still logged in, another authentication
		 * must have been used).
		 */
		if ( true === $wp_rest_auth_cookie && is_user_logged_in() ) {
			return true;
		}

		// Determine if there is a nonce.
		$nonce = null;

		if ( isset( $_REQUEST['_wpnonce'] ) ) {
			$nonce = $_REQUEST['_wpnonce'];
		} elseif ( isset( $_SERVER['HTTP_X_WP_NONCE'] ) ) {
			$nonce = $_SERVER['HTTP_X_WP_NONCE'];
		}

		// Check the nonce.
		$result = wp_verify_nonce( $nonce, 'wp_rest' );

		if ( ! $result ) {
			return new WP_Error( 'rest_cookie_invalid_nonce', __( 'PRSO Framework Functions: Cookie nonce is invalid.' ), array( 'status' => 403 ) );
		}

		return true;
	}

}

new PrsoCustomRestApi();