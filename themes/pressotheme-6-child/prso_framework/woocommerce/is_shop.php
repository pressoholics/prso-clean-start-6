<?php
/**
 * Actions, functions for is_shop or is_product_category views
 */

//Remove items from product archive
add_action( 'wp', 'cl_woo_remove_archive_items' );
function cl_woo_remove_archive_items() {

	if ( ! Prso_Woocom::is_product_archive() ) {
		return false;
	}

	//Remove woocommerce archive sidebar
	remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

	//Remove woo breadcrumbs
	remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );

	//Remove default woo title
	add_filter( 'woocommerce_show_page_title', '__return_false' );

	//Remove results count
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );

	//Remove woo ordering select
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );

}

//Add items from product archive
add_action( 'wp', 'cl_woo_add_archive_items' );
function cl_woo_add_archive_items() {

	if ( ! Prso_Woocom::is_product_archive() ) {
		return false;
	}

	//Render category / brand filters
	add_action( 'woocommerce_before_main_content', 'cl_woo_shop_filters', 5 );

}

/**
 * cl_woo_shop_filters
 *
 * @CALLED BY /ACTION 'woocommerce_before_main_content'
 *
 * Render shop product category / brand filters
 *
 * @access public
 * @author Ben Moody
 */
function cl_woo_shop_filters() {

	if ( is_shop() ) {

		//Shop masthead
		get_template_part( '/template_parts/woocommerce/part', 'shop_masthead' );

	} else { //Product category

		//Taxonomy masthead
		get_template_part( '/template_parts/woocommerce/part', 'tax_masthead' );

	}

	//category filters
	cl_woo_render_shop_cat_filter();

	//Brand Filters
	cl_woo_render_shop_brand_filters();

}

/**
 * cl_woo_render_shop_cat_filter
 *
 * Handle geting data and rendering the shop category nav based on current
 * context
 *
 * @access public
 * @author Ben Moody
 */
function cl_woo_render_shop_cat_filter() {

	//vars
	$output = null;
	$terms  = null;
	$args   = array();

	$templates   = array();
	$templates[] = "template_parts/woocommerce/part-shop_nav.php";

	$current_cat = get_queried_object();

	//First get top level terms, if already on a product cat page, get children of current qureied obj
	if ( is_product_category() ) {

		if ( ! is_object( $current_cat ) ) {
			return;
		}

		$args = array(
			'parent' => $current_cat->term_id,
		);

	} elseif ( is_shop() ) {

		$args = array(
			'parent' => 0,
		);

	}

	$terms = Prso_Woocom::get_product_terms( $args );

	if ( ! empty( $terms->terms ) ) {

		//Prepare data array for nav output (form hierarchy)
		$filter_data = cl_woo_prepare_cat_filter_data( $terms->terms );

		ob_start();
		require( locate_template( $templates, false ) );
		$output = ob_get_contents();
		ob_end_clean();

	} elseif ( 0 !== $current_cat->parent ) { //Does the current item have a parent and siblings?

		$args = array(
			'parent' => $current_cat->parent,
		);

		$terms = Prso_Woocom::get_product_terms( $args );

		if ( ! empty( $terms->terms ) ) {

			//Prepare data array for nav output (form hierarchy)
			$filter_data = cl_woo_prepare_cat_filter_data( $terms->terms );

			ob_start();
			require( locate_template( $templates, false ) );
			$output = ob_get_contents();
			ob_end_clean();

		}

	} else { //Fallback and just display current term

		//Prepare data array for nav output (form hierarchy)
		$filter_data = cl_woo_prepare_cat_filter_data( array( $current_cat ) );

		ob_start();
		require( locate_template( $templates, false ) );
		$output = ob_get_contents();
		ob_end_clean();

	}

	echo $output;
}

/**
 * cl_woo_prepare_cat_filter_data
 *
 * Helper to loop product cat terms and build an hierachical array with
 * children for each term if found
 *
 * @access public
 * @author Ben Moody
 */
function cl_woo_prepare_cat_filter_data( $terms ) {

	//var
	$output = array();

	foreach ( $terms as $key => $term ) {

		if ( 'uncategorized' === $term->slug ) {
			continue;
		}

		$output[ $key ] = $term;

		$child_terms = null;

		//Get children
		$output[ $key ]->children = cl_woo_get_cat_filter_children( $term->term_id );

	}

	return $output;
}

/**
 * cl_woo_get_cat_filter_children
 *
 * Helper to detect if a terms in a term group have any children, add children
 * node to object if found
 *
 * @access public
 * @author Ben Moody
 */
function cl_woo_get_cat_filter_children( $parent_term_id = null ) {

	$output      = array();
	$child_terms = null;

	//Get children of this term
	$child_terms = Prso_Woocom::get_product_terms(
		array(
			'parent' => $parent_term_id,
		)
	);

	if ( isset( $child_terms->terms ) && ! empty( $child_terms->terms ) ) {

		foreach ( $child_terms->terms as $key => $grandchild_term ) {

			$child_terms->terms[ $key ]->children = cl_woo_get_cat_filter_children( $grandchild_term->term_id );

		}

		$output = $child_terms->terms;

	}

	return $output;
}

function cl_woo_render_shop_brand_filters() {

	//vars
	$brands      = array();
	$output      = null;
	$templates   = array();
	$templates[] = "template_parts/woocommerce/part-brand_filters.php";
	$cache_group = 'cl_woo_brand_filters';

	if ( is_product_category() ) {

		$queried_object = get_queried_object();

		if ( isset( $queried_object->term_id ) ) {

			//Try cache
			$brands = wp_cache_get(
				'cat_brands__term_id_' . $queried_object->term_id,
				$cache_group
			);

			if( false === $brands ) {

				//Get post ids of all products in this category, we need to limit brand terms to those related to available products
				$args    = array(
					'post_type'              => 'product',
					'posts_per_page'         => - 1,
					'post_status'            => 'publish',
					'tax_query'              => array(
						array(
							'taxonomy' => 'product_cat',
							'field'    => 'term_id',
							'terms'    => $queried_object->term_id,
						),
					),
					'fields'                 => 'ids',
					// Normal query goes here //
					'no_found_rows'          => true,
					// counts posts, remove if pagination required
					'update_post_term_cache' => false,
					// grabs terms, remove if terms required (category, tag...)
					'update_post_meta_cache' => false,
					// grabs post meta, remove if post meta required
				);
				$results = new WP_Query( $args );

				if ( $results->have_posts() ) {

					//Try and get brands realted to these products
					$brands = wp_get_object_terms( $results->posts, 'cl_product_brand' );

					//Cache results
					wp_cache_set(
						'cat_brands__term_id_' . $queried_object->term_id,
						$brands,
						$cache_group
					);

				}

			}

		}

	}

	//If we still don't have any brands, get them all except for empty ones
	if ( empty( $brands ) ) {

		//Try cache
		$brands = wp_cache_get(
			'cat_brands',
			$cache_group
		);

		if( false === $brands ) {

			$brands_results = get_brand_terms();

			if ( is_object( $brands_results ) && isset( $brands_results->terms ) ) {
				$brands = $brands_results->terms;

				//Cache results
				wp_cache_set(
					'cat_brands',
					$brands,
					$cache_group
				);
			}

		}


	}

	if ( ! empty( $brands ) ) {

		//Get post count for each brand
		foreach ( $brands as $key => $brand ) {

			$brand_cache_key = 'cat_brands_count__term_id_' . $brand->term_id;

			if ( ! isset( $brand->term_id ) ) {
				continue;
			}

			if( is_product_category() ) {

				$cache_group = $cache_group . '__product_cat_' . $queried_object->term_id;

			}

			//Try cache
			$brand_count = wp_cache_get(
				$brand_cache_key,
				$cache_group
			);

			if( false === $brand_count ) {

				$args    = array(
					'post_type'              => 'product',
					'posts_per_page'         => - 1,
					'post_status'            => 'publish',
					'tax_query'              => array(
						'relation' => 'AND',
						array(
							'taxonomy' => 'cl_product_brand',
							'field'    => 'term_id',
							'terms'    => $brand->term_id,
						),
					),
					'fields'                 => 'ids',
					// Normal query goes here //
					'no_found_rows'          => true,
					// counts posts, remove if pagination required
					'update_post_term_cache' => false,
					// grabs terms, remove if terms required (category, tag...)
					'update_post_meta_cache' => false,
					// grabs post meta, remove if post meta required
				);

				//Add in current product category if applicable
				if( is_product_category() ) {

					$args['tax_query'][] = array(
						'taxonomy' => 'product_cat',
						'field'    => 'term_id',
						'terms'    => $queried_object->term_id,
					);

				}

				$results = new WP_Query( $args );

				$brands[ $key ]->count = 0;

				if ( $results->have_posts() ) {
					$brand_count = $results->post_count;

					//Cache results
					wp_cache_set(
						$brand_cache_key,
						$brand_count,
						$cache_group
					);
				}

			}

			$brands[ $key ]->count = $brand_count;

		}

	}

	ob_start();
	require( locate_template( $templates, false ) );
	$output = ob_get_contents();
	ob_end_clean();

	echo $output;
}

function cl_woo_get_sort_options() {

	$catalog_orderby_options = apply_filters( 'woocommerce_catalog_orderby', array(
		'menu_order' => __( 'Default sorting', 'woocommerce' ),
		'popularity' => __( 'Popularity', 'woocommerce' ),
		'rating'     => __( 'Average rating', 'woocommerce' ),
		'date'       => __( 'Newest Arrivals', 'woocommerce' ),
		'price'      => __( 'Price low to high', 'woocommerce' ),
		'price-desc' => __( 'Price high to low', 'woocommerce' ),
	) );

	return $catalog_orderby_options;
}

function get_brand_terms( $args = array() ) {

	//vars
	$defaults = array(
		'taxonomy'   => 'cl_product_brand',
		'hide_empty' => true,
		'child_of'   => 0,
	);
	$results  = null;

	$args = wp_parse_args( $args, $defaults );

	$results = new WP_Term_Query( $args );

	return $results;
}

/**
 * vt_prod_cat_get_masthead_url
 *
 * Helper to return attachment URL for product category terms, can also be used
 * to detect if masthead has been enabled for term as returns false when not
 * enabled
 *
 * @return mixed attachment_id / false
 * @access public
 * @author Ben Moody
 */
function vt_prod_cat_get_masthead_url() {

	$image_id = get_field( 'prod_cat_masthead_image', get_queried_object() );

	if ( false === $image_id ) {

		//Get fallback image from options
		$image_id = get_field( 'product_category_masthead_fallback', 'option' );

	}

	$image_url = wp_get_attachment_image_url( $image_id, 'cl_product_cat_masthead' );

	return $image_url;
}

function vt_get_page_masthead_url() {

	$image_id = get_post_thumbnail_id();

	if ( empty( $image_id ) ) {

		//Get fallback image from options
		$image_id = get_field( 'product_category_masthead_fallback', 'option' );

	}

	$image_url = wp_get_attachment_image_url( $image_id, 'cl_product_cat_masthead' );

	return $image_url;
}

function cl_get_store_grid_image( $size = 'full' ) {

	$image_id = get_sub_field( 'image' );

	$image_url = wp_get_attachment_image_url( $image_id, $size );

	return $image_url;

}