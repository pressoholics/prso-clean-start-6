<?php
//Remove items from product archive
add_action( 'wp', 'cl_woo_remove_single_product_items' );
function cl_woo_remove_single_product_items() {

	global $post,$wp_query;

	if ( ! is_product() ) {
		return false;
	}

	//Get product brand and cache in wp_query
	$wp_query->product_brands = get_the_terms( $post, 'cl_product_brand' );

	//Remove woocommerce archive sidebar
	remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

	//Add clearlife rating below title
	add_action( 'woocommerce_single_product_summary', 'cl_woo_single_add_cl_rating', 6 );

	//Remove short description
	add_filter( 'woocommerce_short_description', '__return_false' );

	//Remove meta
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );

	//Remove upsells
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );

	//Remove desciption tab h2 title
	add_filter( 'woocommerce_product_description_heading', '__return_false' );

}

/**
 * Add a custom product data tab
 */
add_filter( 'woocommerce_product_tabs', 'cl_woo_new_product_tab' );
function cl_woo_new_product_tab( $tabs ) {

	//Brand description
	if( false !== cl_get_product_brands() ) {
		$tabs['about_brand'] = array(
			'title' 	=> esc_html_x( 'About The Brand', 'text', PRSOTHEMEFRAMEWORK__DOMAIN ),
			'priority' 	=> 50,
			'callback' 	=> 'cl_woo_product_tab__about_brand'
		);
	}

	$tabs['return_policy'] = array(
		'title' 	=> esc_html_x( 'Return Policy', 'text', PRSOTHEMEFRAMEWORK__DOMAIN ),
		'priority' 	=> 51,
		'callback' 	=> 'cl_woo_product_tab__return_policy'
	);


	return $tabs;

}

function cl_woo_product_tab__about_brand() {

	//vars
	$brands = cl_get_product_brands();

	if( !isset($brands[0]->term_id) ) {
		return;
	}

	echo wp_kses_post( apply_filters( 'the_content', get_field('brand_description', $brands[0]) ) );

}

function cl_woo_product_tab__return_policy() {

	//vars
	global $post;
	$vendor_term = null;
	$policy = null;

	//Try and get vendor this current product
	$vendor_term = wp_get_object_terms( $post->ID, 'wcpv_product_vendors' );

	//Vendor policy?
	if( is_array($vendor_term) && isset($vendor_term[0]) ) {
		$policy = get_field( 'vendor_return_policy', $vendor_term[0] );
	}

	//fallback to store policy
	if( empty($policy) ) {
		$policy = get_field('store_return_policy', 'options' );
	}

	echo wp_kses_post( apply_filters( 'the_content', $policy ) );

}

function cl_get_product_brands() {

	global $wp_query;

	if( isset($wp_query->product_brands) ) {
		return $wp_query->product_brands;
	}

	return false;
}

/**
* cl_woo_single_add_cl_rating
*
* @CALLED BY /ACTION 'woocommerce_single_product_summary'
*
* Render Clearlife ratingnuer product title
*
* @access public
* @author Ben Moody
*/
function cl_woo_single_add_cl_rating() {

	//vars
	global $post;
	$cl_rating_terms = null;

	if( !isset($post->ID) ) {
		return;
	}

	//Try and get cl reating terms for current post
	$cl_rating_terms = wp_get_post_terms( $post->ID, 'cl_rating' );

	if( is_wp_error($cl_rating_terms) ) {
		return;
	}

	$cl_rating_info_url = null;
	$cl_rating_info_page = get_field( 'clearlife_rating_info_page', 'option' );
	if( false !== $cl_rating_info_page ) {
		$cl_rating_info_url = get_permalink( $cl_rating_info_page->ID );
	}

	?>
	<ul class="cl-rating">
		<?php foreach( $cl_rating_terms as $cl_rating ): ?>
		<li>
			<a href="<?php echo esc_url( $cl_rating_info_url ); ?>" target="_blank" title="<?php echo esc_html( $cl_rating->description ); ?>">
				<?php echo wp_get_attachment_image( get_field( 'icon', $cl_rating ), 'full' ); ?>
			</a>
		</li>
		<?php endforeach; ?>
	</ul>
	<?php

}