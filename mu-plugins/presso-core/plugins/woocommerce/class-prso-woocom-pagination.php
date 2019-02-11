<?php

/**
 * Prso_Woocom_Pagination
 *
 * Handle all interactions with woocommerce rest api
 */
class Prso_Woocom_Pagination extends Prso_Woocom {

	public static $posts_per_page;

	public function __construct() {

		add_action( 'init', array( $this, 'init_woo_pagination' ) );

		add_action( 'pre_get_posts', array( $this, 'woo_products_order' ) );

		add_filter( 'loop_shop_per_page', array(
			$this,
			'woo_loop_shop_per_page',
		), 20, 1 );

	}

	public function init_woo_pagination() {

		//Cache posts per page
		self::$posts_per_page = apply_filters( 'prso_woocom_pagination__posts_per_page', get_option( 'posts_per_page' ) );

		//Maybe replace woo pagination with load more button
		$replace_pagination = apply_filters( 'prso_woocom_pagination__active', true );
		if ( true === $replace_pagination ) {

			remove_action( 'woocommerce_after_shop_loop', 'woocommerce_pagination', 10 );
			add_action( 'woocommerce_after_shop_loop', array(
				$this,
				'render_load_more_button',
			), 10 );

		}

	}

	/**
	 * render_load_more_button
	 *
	 * @CALLED BY /ACTION 'woocommerce_after_shop_loop'
	 *
	 * Render load more button on product shop index pages
	 *
	 * @access public
	 * @author Ben Moody
	 */
	public function render_load_more_button() {

		//vars
		$defaults = array(
			'endpoint'        => 'products',
			'dom_destination' => 'ul.products',
			'posts_per_page'  => self::$posts_per_page,
		);

		$load_more_param = apply_filters( 'prso_woocom_pagination__load_more_param', $defaults );

		?>
		<?php echo prso_render_load_more_button( $load_more_param ); ?>
		<?php

	}

	/**
	 * woo_products_order
	 *
	 * @CALLED BY /ACTION 'pre_get_posts'
	 *
	 * Filter main query params for woo archives
	 *
	 * @access public
	 * @author Ben Moody
	 */
	function woo_products_order( $query ) {

		if ( ! parent::is_product_archive() ) {
			return;
		}

		$query->set( 'orderby', 'date' );
		$query->set( 'order', 'DESC' );
		$query->set( 'post_per_page', self::$posts_per_page );
		$query->set( 'post_status', 'publish' );

	}


	/**
	 * woo_loop_shop_per_page
	 *
	 * @CALLED BY FILTER/ 'loop_shop_per_page'
	 *
	 * Force woo posts per page to match WP deafult
	 *
	 * @access public
	 * @author Ben Moody
	 */
	function woo_loop_shop_per_page( $cols ) {

		$cols = self::$posts_per_page;

		return $cols;
	}

}

new Prso_Woocom_Pagination();
