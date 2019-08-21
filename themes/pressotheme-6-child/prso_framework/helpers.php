<?php
/**
 * prso_replace_dev_with_prodcution_image_urls
 *
 * @CALLED BY ACTION 'init'
 *
 * Replace dev image urls with production if WP_SITEURL and PROD_URL are defined in wp-config
 *
 * @access public
 * @author Ben Moody
 */
//add_action('init', 'prso_replace_dev_with_prodcution_image_urls' );
function prso_replace_dev_with_prodcution_image_urls() {

	if ( defined('WP_SITEURL') && defined('PROD_URL') ) {

		if ( WP_SITEURL != PROD_URL ){

			add_filter('wp_get_attachment_url', 'prso_wp_get_production_attachment_url', 10, 1 );
			add_filter('wp_get_attachment_image_attributes', 'prso_wp_get_production_attachment_url', 10, 1 );

			//Filter content for dev domain and replace with production domain
			add_filter('the_content', 'prso_wp_set_production_content_urls', 10, 1 );

		}

	}

}

/**
 * prso_wp_get_production_attachment_url
 *
 * @CALLED BY FILTER 'wp_get_attachment_url'
 * @CALLED BY FILTER 'wp_get_attachment_image_attributes'
 *
 * Filters src and srcset and replaces dev urls with production
 *
 * @access public
 * @author Ben Moody
 */
function prso_wp_get_production_attachment_url( $url ) {

	if( is_array($url) && isset($url['src'], $url['srcset']) ) {

		$url['src'] = str_replace( WP_SITEURL, PROD_URL, $url['src'] );
		$url['srcset'] = str_replace( WP_SITEURL, PROD_URL, $url['srcset'] );

	} else {

		$url = str_replace( WP_SITEURL, PROD_URL, $url );

	}

	return $url;
}

/**
 * prso_wp_set_production_content_urls
 *
 * @CALLED BY FILTER 'the_content'
 *
 * Filters all instances of the dev url in content with production url
 *
 * @access public
 * @author Ben Moody
 */
function prso_wp_set_production_content_urls( $content ) {

	$content = str_replace( WP_SITEURL, PROD_URL, $content );

	return $content;
}

/**
 * prso_set_cookie
 *
 * Helper to set a secure, httponly, samesite=strict cookie
 *
 * @param string $name - cookie name
 * @param string $value - cookie value
 * @param int $timestamp - expires timestamp in milliseconds
 *
 * @access public
 * @author Ben Moody
 */
function prso_set_cookie( $name = null, $value = null, $timestamp = null ) {

	//vars
	$cookie_expires = null;

	//If no timestamp provided default to session cookie
	if ( ! empty( $timestamp ) ) {
		$cookie_expires = date( 'D, d M Y H:i:s e', $timestamp );
	}

	$site_url = get_site_url();

	//Remove http to get just domain
	$find        = array( 'http://', 'https://' );
	$replace     = '';
	$site_domain = str_replace( $find, $replace, $site_url );

	$cookie_header = 'Set-Cookie: ' . esc_attr( $name ) . '=' . esc_attr( $value ) . '; path=/; domain=' . $site_domain . '; Expires=' . $cookie_expires . '; HttpOnly; Secure; SameSite=Strict';

	header( $cookie_header );
}

/**
 * ADD CUSTOM THEME FUNCTIONS HERE -----
 *
 */

/**
 * WooCommerce Support
 *
 * Include theme woocommerce file to use a framework for woo projects
 *
 * @access public
 * @author Ben Moody
 */
//prso_include_file( get_stylesheet_directory() . '/prso_framework/woocommerce.php' );

/**
 * prso_allow_iframes_filter
 *
 * @CALLED BY FILTER 'wp_kses_allowed_html'
 *
 * Allow iframe output when using wp_kses_post
 *
 * @access    public
 * @author    Ben Moody
 */
//add_filter( 'wp_kses_allowed_html', 'prso_allow_iframes_filter' );
function prso_allow_iframes_filter( $allowedposttags ) {

	// Allow iframes and the following attributes
	$allowedposttags['iframe'] = array(
		'align'        => true,
		'width'        => true,
		'height'       => true,
		'frameborder'  => true,
		'name'         => true,
		'src'          => true,
		'id'           => true,
		'class'        => true,
		'style'        => true,
		'scrolling'    => true,
		'marginwidth'  => true,
		'marginheight' => true,
	);

	return $allowedposttags;
}

//Add BugHerd script for admins only
//add_action( 'wp_footer', 'gcc_enqueue_bugherd_admin' );
function gcc_enqueue_bugherd_admin() {
	if ( current_user_can( 'manage_options' ) ):
		?>
		<script type='text/javascript'>
            (function (d, t) {
                var bh = d.createElement(t), s = d.getElementsByTagName(t)[0];
                bh.type = 'text/javascript';
                bh.src = 'https://www.bugherd.com/sidebarv2.js?apikey=';
                s.parentNode.insertBefore(bh, s);
            })(document, 'script');
		</script>
	<?php
	endif;
}

/**
 * prso_custom_login_view
 *
 * @CAlled by: 'login_enqueue_scripts'
 *
 * Customize the wp login view
 *
 * @access    public
 * @author    Ben Moody
 */
//add_action( 'login_enqueue_scripts', 'prso_custom_login_view' );
function prso_custom_login_view() { ?>
	<style type="text/css">
		body {

		}

		.login h1 a {
			background-image: url(<?php echo get_stylesheet_directory_uri(); ?>/dist/assets/img/admin/site_login_logo.png);
			background-size: cover;
			display: block;
			width: 324px;
		}
	</style>
<?php }

/**
 * prso_get_queried_term_name
 *
 * Helper to return current queried term id if set
 *
 * @access public
 * @author Ben Moody
 */
function prso_get_queried_term_id() {

	//vars
	$queried_obj = get_queried_object();

	if ( ! isset( $queried_obj->term_id ) ) {
		return false;
	}

	return intval( $queried_obj->term_id );
}

/**
 * prso_get_search_query
 *
 * Helper to return search query string if set
 *
 * @access public
 * @author Ben Moody
 */
function prso_get_search_query() {

	//vars
	$query = esc_html( get_search_query() );

	if ( empty( $query ) ) {
		return false;
	}

	return $query;
}

/**
 * prso_tiny_mce_editor_styles
 *
 * @CALLED BY ACTION 'init'
 *
 * Enqueue custom visual editor stylesheet
 *
 * @access    public
 * @author    Ben Moody
 */
add_action( 'init', 'prso_tiny_mce_editor_styles', 10 );
function prso_tiny_mce_editor_styles() {

	add_editor_style( 'dist/assets/css/editor.css' );

}

/**
 * Enqueue supplemental block editor styles.
 */
add_action( 'enqueue_block_editor_assets', 'prso_editor_frame_styles' );
function prso_editor_frame_styles() {

	wp_enqueue_style(
		'prso-editor-frame-styles',
		get_stylesheet_directory_uri() . '/dist/assets/css/block-editor.css',
		false,
		'1.0',
		'all'
	);

}

/**
* prso_disable_frontend_embeds_init
*
* @CALLED BY ACTION 'init'
*
* Disable embeds for frontend
*
* @access public
* @author Ben Moody
*/
add_action( 'init', 'prso_disable_frontend_embeds_init', 9999 );
function prso_disable_frontend_embeds_init() {

	if( is_admin() ) {
		return;
	}

	if ( ! method_exists( 'Prso_Gutenberg', 'is_gutenberg_request' ) ) {
		return;
	}

	if ( Prso_Gutenberg::is_gutenberg_request() ) {
		return;
	}

	// Remove the REST API endpoint.
	remove_action( 'rest_api_init', 'wp_oembed_register_route' );

	// Turn off oEmbed auto discovery.
	// Don't filter oEmbed results.
	remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );

	// Remove oEmbed discovery links.
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );

	// Remove oEmbed-specific JavaScript from the front-end and back-end.
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );

	// REMOVE WP EMOJI
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );

	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );

}

/**
* prso_multisite_body_classes
*
* @CALLED BY FILTER 'body_class'
*
* Body class filter will add site data to css class in multisite
*
* @access public
* @author Ben Moody
*/
//add_filter( 'body_class', 'prso_multisite_body_classes' );
function prso_multisite_body_classes( $classes ) {

	$id        = get_current_blog_id();
	$slug      = strtolower( str_replace( ' ', '-', trim( get_bloginfo( 'name' ) ) ) );
	$classes[] = $slug;
	$classes[] = 'site-id-' . $id;

	return $classes;
}

/**
* prso_excerpt_length
*
* @CALLED BY FILTER 'excerpt_length'
*
* @access public
* @author Ben Moody
*/
add_filter( 'excerpt_length', 'prso_excerpt_length', 999 );
function prso_excerpt_length( $length ) {
	return 15;
}

/**
* prso_excerpt_more
*
* @CALLED BY FILTER 'excerpt_more'
*
* @access public
* @author Ben Moody
*/
add_filter( 'excerpt_more', 'prso_excerpt_more' );
function prso_excerpt_more( $more ) {
	return '...';
}

/**
 * prso_pre_get_posts
 *
 * @CALLED BY /ACTION 'pre_get_posts'
 *
 * Set query vars
 *
 * @access public
 * @author Ben Moody
 */
add_action( 'pre_get_posts', 'prso_pre_get_posts', 1 );
function prso_pre_get_posts( $query ) {

	if ( is_admin() ) {
		return;
	}

	if ( ! $query->is_main_query() ) {
		return;
	}

	$query->set( 'orderby', 'date' );
	$query->set( 'order', 'DESC' );
	$query->set( 'post_per_page', get_option( 'posts_per_page' ) );

}

/**
 * prso_is_gutenberg_editor_request
 *
 * Helper to return result of static method Prso_Gutenberg::is_gutenberg_request()
 *
 * @return bool
 * @access public
 * @author Ben Moody
 */
function prso_is_gutenberg_editor_request() {

	if ( ! method_exists( 'Prso_Gutenberg', 'is_gutenberg_request' ) ) {
		return false;
	}

	return Prso_Gutenberg::is_gutenberg_request();

}

// unregister all default WP Widgets
add_action('widgets_init', 'prso_unregister_default_wp_widgets', 1);
function prso_unregister_default_wp_widgets() {
	unregister_widget('WP_Widget_Pages');
	unregister_widget('WP_Widget_Calendar');
	unregister_widget('WP_Widget_Archives');
	unregister_widget('WP_Widget_Links');
	unregister_widget('WP_Widget_Meta');
	unregister_widget('WP_Widget_Search');
	unregister_widget('WP_Widget_Text');
	unregister_widget('WP_Widget_Categories');
	unregister_widget('WP_Widget_Recent_Posts');
	unregister_widget('WP_Widget_Recent_Comments');
	unregister_widget('WP_Widget_RSS');
	unregister_widget('WP_Widget_Tag_Cloud');
	unregister_widget('WP_Nav_Menu_Widget');
}

// Comment Layout
if( !function_exists('prso_theme_comments') ) {

	function prso_theme_comments($comment, $args, $depth) {
		$GLOBALS['comment'] = $comment; ?>
	<li <?php comment_class(); ?>>
		<article id="comment-<?php comment_ID(); ?>" class="clearfix">
			<div class="comment-author vcard clearfix">
				<div class="
                        <?php
				$authID = get_the_author_meta('ID');

				if($authID == $comment->user_id)
					echo "callout";
				?>
                    ">
					<div class="grid-x">
						<div class="avatar large-2 cell">
							<?php echo get_avatar($comment,$size='75',$default='' ); ?>
						</div>
						<div class="large-10 cell">
							<?php printf(__('<h4 class="span8">%s</h4>'), get_comment_author_link()) ?>
							<time datetime="<?php echo comment_time('Y-m-j'); ?>"><a href="<?php echo htmlspecialchars( get_comment_link( $comment->comment_ID ) ) ?>"><?php comment_time('F jS, Y'); ?> </a></time>

							<?php edit_comment_link(__('Edit'),'<span class="edit-comment">', '</span>'); ?>

							<?php if ($comment->comment_approved == '0') : ?>
								<div class="alert-box success">
									<?php _e('Your comment is awaiting moderation.') ?>
								</div>
							<?php endif; ?>

							<?php comment_text() ?>

							<!-- removing reply link on each comment since we're not nesting them -->
							<?php comment_reply_link(array_merge( $args, array('depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
						</div>
					</div>
				</div>
			</div>
		</article>
		<!-- </li> is added by wordpress automatically -->
		<?php
	} // don't remove this bracket!

}

/**
* react_term_link_filter
*
* @CALLED BY FILTER 'term_link'
*
* Filter term link to add URL params required by the post grid react app
*
* @access public
* @author Ben Moody
*/
//add_filter('term_link', 'react_term_link_filter', 10, 3);
function react_term_link_filter( $url, $term, $taxonomy ) {

	switch( $taxonomy ) {
		case 'category':
			$taxonomy = 'categories';
			break;
		case 'tag':
			$taxonomy = 'tags';
			break;
	}

	$url = add_query_arg(
		array(
			'page' => 1,
			'per_page' => get_option( 'posts_per_page' ),
			$taxonomy => $term->term_id,
		),
		$url
	);

	return esc_url_raw( $url );
}

/**
* react_get_queried_object_id
*
* Helper to return the object ID of the current quried object, useful when setting selected tax filters for react local object
*
* @access public
* @author Ben Moody
*/
function react_get_queried_object_id() {

	//vars
	$object = get_queried_object();

	if( isset($object->term_id) ) {
		return $object->term_id;
	} elseif( isset($object->ID) ) {
		return $object->ID;
	}

	return false;
}

/**
* prso_theme_asset_link
*
* Helper to get url to theme dist assets dir
 * $asset_path is relative to the assets dir, no slash required
*
* @access public
* @author Ben Moody
*/
function prso_theme_asset_link( $asset_path = null ) {

	$url = get_stylesheet_directory_uri();

	$url = "{$url}/dist/assets/{$asset_path}";

	return $url;
}

/**
* prso_get_nav_menu_meta
*
* Helper to get nav menu meta data from the nav menu location (slug) used when the nav was registered
*e
* @access public
* @author Ben Moody
*/
function prso_get_nav_menu_meta( $menu_name, $meta_node = 'name' ) {

	//vars
	$menu_object = null;
	$locations = null;
	$menu_id = null;

	$locations = get_nav_menu_locations();

	if( !is_array($locations) ) {
		return null;
	}

	if( !isset($locations[ $menu_name ]) ) {
		return null;
	}

	$menu_id = $locations[ $menu_name ];

	$menu_object = wp_get_nav_menu_object( $menu_id );

	if( false === $menu_object ) {
		return null;
	}

	if( !isset($menu_object->$meta_node) ) {
		return null;
	}

	return $menu_object->$meta_node;
}

/**
 * prso_get_nav_menu_obj_by_location
 *
 * @access public
 * @author Ben Moody
 */
function prso_get_nav_menu_obj_by_location( $location_name ) {

	//vars
	$menu_object = null;
	$locations = null;
	$menu_id = null;

	$locations = get_nav_menu_locations();

	if( !is_array($locations) ) {
		return null;
	}

	if( !isset($locations[ $location_name ]) ) {
		return null;
	}

	$menu_id = $locations[ $location_name ];

	$menu_object = wp_get_nav_menu_object( $menu_id );

	if( false === $menu_object ) {
		return null;
	}

	return $menu_object;
}

/**
* prso_remove_add_to_any_script
*
* @CALLED BY ACTION 'wp_enqueue_scripts'
*
* Only load the addtoany plugin resources on specific pages, defaults to post single only
*
* @access public
* @author Ben Moody
*/
add_action( 'wp_enqueue_scripts', 'prso_remove_add_to_any_script', 900 );
function prso_remove_add_to_any_script() {

	//Allow on single posts
	if( is_singular('post') ) {
		return;
	}

	wp_dequeue_script('addtoany');
	wp_dequeue_style('addtoany');

}

/**
 * prso_remove_add_to_any_header_script
 *
 * @CALLED BY ACTION 'wp'
 *
 * Only load the addtoany plugin resources on specific pages, defaults to post single only
 *
 * @access public
 * @author Ben Moody
 */
add_action( 'wp', 'prso_remove_add_to_any_header_script' );
function prso_remove_add_to_any_header_script() {

	//Allow on single posts
	if( is_singular('post') ) {
		return;
	}

	remove_action('wp_head', 'A2A_SHARE_SAVE_head_script');

}

/**
* prso_i18n_string_translation
*
* @CALLED BY FILTER 'gettext_with_context'
*
* Hook into WP i18n system and use context to try and find an acf option with the same key, replace if valid option value found.
*
* @access public
* @author Ben Moody
*/
add_filter( 'gettext_with_context', 'prso_i18n_string_translation', 900, 4 );
function prso_i18n_string_translation( $translation, $text, $context, $domain ) {

	if( $domain !== PRSOTHEMEFRAMEWORK__DOMAIN ) {
		return $translation;
	}

	if (strpos($context, 'prso-i18n') === false) {
		return $translation;
	}

	//Try and fetch translation based on context
	$acf_string = get_field( $context, 'option' );

	if( !$acf_string ) {
		return $translation;
	}

	return $acf_string;
}