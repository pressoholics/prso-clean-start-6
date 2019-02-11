<?php

/**
 * Prso_Woocom_Rest_Api
 *
 * Handle all interactions with woocommerce rest api
 */
class Prso_Woocom_Rest_Api extends Prso_Woocom {

	public function __construct() {

		add_filter( 'woocommerce_rest_product_object_query', array(
			$this,
			'woo_rest_product_query',
		), 999, 2 );

		add_filter( 'woocommerce_rest_prepare_product_object', array(
			$this,
			'woo_rest_prepare_product',
		), 999, 3 );

	}

	/**
	 * woo_rest_product_query
	 *
	 * @CALLED BY FILTER/ 'woocommerce_rest_product_query'
	 *
	 * Force woo posts per page to match WP deafult
	 *
	 * @access public
	 * @author Ben Moody
	 */
	public function woo_rest_product_query( $args, $request ) {

		$args['posts_per_page'] = parent::get_products_per_page();
		$args['post_status']    = 'publish';

		//Detect category filter in request
		if ( isset( $request['filter']['cat'] ) ) {

			unset( $args['cat'] );

			$term_id = intval( $request['filter']['cat'] );

			$args['tax_query'] = array(
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'term_id',
					'terms'    => array( $term_id ),
				),
			);

		}

		$args = apply_filters( 'prso_woocom_rest_api__query_args', $args );

		return $args;
	}

	/**
	 * woo_rest_prepare_product
	 *
	 * @CALLED BY FILTER/ 'woocommerce_rest_prepare_product'
	 *
	 * Filter woo prodcuts rest api product response, add node containing
	 *     rendered contents of woo content-product template
	 *
	 * @access public
	 * @author Ben Moody
	 */
	function woo_rest_prepare_product( $response, $post_object, $request ) {

		global $product, $post;

		$post = get_post( $post_object->id );

		setup_postdata( $post );

		$product = $post_object;

		ob_start();
		wc_get_template_part( 'content', 'product' );
		$response->data['item_html'] = ob_get_contents();
		ob_end_clean();

		return rest_ensure_response( $response );
	}

}
new Prso_Woocom_Rest_Api();
