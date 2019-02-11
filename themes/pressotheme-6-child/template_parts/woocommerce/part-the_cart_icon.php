<?php
/**
 * Created by PhpStorm.
 * User: ben
 * Date: 2018-06-07
 * Time: 12:56 PM
 */
?>
<a class="cart-contents <?php echo sanitize_html_class( $css_classes ); ?>"
   href="<?php echo esc_url( wc_get_cart_url() ); ?>"
   title="<?php echo esc_html_x( 'View your shopping cart', 'text', PRSOTHEMEFRAMEWORK__DOMAIN ); ?>">

	<i class="fa fa-shopping-cart" aria-hidden="true"></i>
	<?php
	if ( $count > 0 ) {
		?>
		<span class="cart-contents-count">
				<?php echo esc_html( $count ); ?>
			</span>
		<?php
	}
	?>
</a>
