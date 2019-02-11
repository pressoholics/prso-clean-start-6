<?php
/**
 * WooCommerce
 */

//Woocommerce support
add_action( 'after_setup_theme', 'vt_woocommerce_support' );
function vt_woocommerce_support() {

	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
	add_theme_support( 'woocommerce' );

}

//Disable woocomerce styles
//add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

/**
 * vt_get_woo_cart
 *
 * Render the custom woocommerce cart icon and item counter
 * SEE template part woocommerce/part-the_cart_icon to customise the output
 *
 * @access public
 * @author Ben Moody
 */
function vt_get_woo_cart() {

	if( method_exists('Prso_Woocom', 'get_woo_cart') ) {
		return Prso_Woocom::get_woo_cart();
	}

}

/**
 * WooCommerce Shop/Archives
 *
 * Include file with actions/functions for woo shop/categeory pages
 *
 * @access public
 * @author Ben Moody
 */
//prso_include_file( get_stylesheet_directory() . '/prso_framework/woocommerce/is_shop.php' );

/**
 * WooCommerce Single Product
 *
 * Include file with actions/functions for woo single product pages
 *
 * @access public
 * @author Ben Moody
 */
//prso_include_file( get_stylesheet_directory() . '/prso_framework/woocommerce/is_product.php' );

/**
 * WooCommerce Cart Page
 *
 * Include file with actions/functions for woo cart pages
 *
 * @access public
 * @author Ben Moody
 */
//prso_include_file( get_stylesheet_directory() . '/prso_framework/woocommerce/is_cart.php' );
