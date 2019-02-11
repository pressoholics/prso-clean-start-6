<?php

class Prso_Woocom {

	/**
	 * Reports constructor.
	 */
	public function __construct() {

		//Register woo classes
		//$this->init_woo();

/*
		add_filter( 'woocommerce_add_to_cart_fragments', array(
			$this,
			'woo_cart_count_fragments',
		), 10, 1 );
*/

	}

	/**
	 * init_woo
	 *
	 * @CALLED BY ACTION 'init'
	 *
	 * @access    public
	 * @author    Ben Moody
	 */
	public function init_woo() {

		//Vars
		$cpt_path = dirname( __FILE__ );

		//Include files
		prso_include_all_files( $cpt_path );

	}

	/**
	 * is_product_archive
	 *
	 * Helper to detect if current view is a WooCommerce product archive view
	 *
	 * @access public
	 * @author Ben Moody
	 */
	public static function is_product_archive() {

		if( is_admin() ) {
			return false;
		}

		$products_rest_api_endpoint = '/wp-json/wc/v2/products';

		//Is this a product rest api request
		if ( isset( $_SERVER['REQUEST_URI'] ) && ( strpos( $_SERVER['REQUEST_URI'], $products_rest_api_endpoint ) !== false ) ) {
			return true;
		}

		$queried_object = get_queried_object();

		if ( ! isset( $queried_object->ID ) && ! isset( $queried_object->term_id ) && ! isset( $queried_object->name ) ) {
			return false;
		}

		if ( is_shop() ) {
			return true;
		}

		if ( is_product_category() ) {
			return true;
		}

		return false;
	}

	/**
	 * get_woo_cart
	 *
	 * Render the custom woocommerce cart icon and item counter
	 *
	 * @access public
	 * @author Ben Moody
	 */
	public static function get_woo_cart() {

		$css_classes = null;
		$item_output = null;

		$count = WC()->cart->cart_contents_count;

		if ( 0 === $count ) {
			$css_classes = 'empty-cart';
		}

		ob_start();
		include( locate_template( '/template_parts/woocommerce/part-the_cart_icon.php' ) );
		$item_output = ob_get_contents();
		ob_end_clean();

		return $item_output;
	}

	/**
	 * get_products_per_page
	 *
	 * Helper to return the number of products per page
	 * Defaults to WP posts_per_page but can be filtered with :
	 *  apply_filters( 'prso_woocom_pagination__posts_per_page', get_option( 'posts_per_page' ) );
	 *
	 * @access public
	 * @author Ben Moody
	 */
	public function get_products_per_page() {
		return Prso_Woocom_Pagination::$posts_per_page;
	}

	/**
	 * woo_cart_count_fragments
	 *
	 * @CALLED BY FILTER 'woocommerce_add_to_cart_fragments'
	 *
	 * Add custom cart icon and item counter markup to woocommerce add to cart
	 *     fragment. This ensures that the markup for the custom cart icon is
	 *     updated when a product is added to cart via ajax
	 *
	 * @param array $fragments
	 *
	 * @param array $fragments
	 *
	 * @access public
	 * @author Ben Moody
	 */
	public function woo_cart_count_fragments( $fragments ) {

		$fragments['div#vt-woo-cart'] = '<div id="vt-woo-cart">' . vt_get_woo_cart() . '</div>';

		return $fragments;

	}


}

new Prso_Woocom();