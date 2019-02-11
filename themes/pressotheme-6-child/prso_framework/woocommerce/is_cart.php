<?php
//Remove items from product archive
add_action( 'wp', 'cl_woo_remove_cart_items' );
function cl_woo_remove_cart_items() {

	if ( ! is_cart() ) {
		return false;
	}

	//Remove items
	remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );
	remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cart_totals', 10 );

	//Cart total above all other cart collaterals
	add_action( 'woocommerce_cart_collaterals', 'woocommerce_cart_totals', 1 );
	//add_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display', 10 );

}