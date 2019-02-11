<!doctype html>

<!--[if IEMobile 7 ]> <html <?php language_attributes(); ?>class="no-js iem7"> <![endif]-->
<!--[if lt IE 7 ]> <html <?php language_attributes(); ?> class="no-js ie6"> <![endif]-->
<!--[if IE 7 ]>    <html <?php language_attributes(); ?> class="no-js ie7"> <![endif]-->
<!--[if IE 8 ]>    <html <?php language_attributes(); ?> class="no-js ie8"> <![endif]-->
<!--[if (gte IE 9)|(gt IEMobile 7)|!(IEMobile)|!(IE)]><!--><html <?php language_attributes(); ?> class="no-js"><!--<![endif]-->

	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

		<title><?php wp_title('', true, 'right'); ?></title>

		<!-- icons & favicons -->

		<!-- media-queries.js (fallback) -->
		<!--[if lt IE 9]>
			<script src="http://css3-mediaqueries-js.googlecode.com/svn/trunk/css3-mediaqueries.js"></script>
		<![endif]-->

		<!-- html5.js -->
		<!--[if lt IE 9]>
			<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->

  		<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">

		<!-- wordpress head functions -->
		<?php wp_head(); ?>
		<!-- end of wordpress head -->

	</head>

	<body <?php body_class(); ?>>

		<!-- OLD IE Warning Message !-->
		<!--[if IE 8]>
		<div id="old-browser-alert" data-alert class="alert-box alert text-center" style="padding:30px 0;font-weight:bold;">
		  <?php _ex( 'This site was designed for modern browsers. To view this site please update your browser: ', 'text', PRSOTHEMEFRAMEWORK__DOMAIN ); ?>
		  <a style="color:#ffffff;text-decoration:underline;" href="http://outdatedbrowser.com/en" target="_blank">http://outdatedbrowser.com/en</a>
		</div>
		<style>
			.off-canvas-wrap {
				display: none;
			}
		</style>
		<![endif]-->

		<!-- Dev Helper for media quries !-->
		<?php if (defined('WP_DEBUG') && TRUE === WP_DEBUG): ?>
			<div id="breakpoints">

				<div class="show-for-small-only">
					SMALL
				</div>

				<div class="show-for-medium-only">
					MEDIUM
				</div>

				<div class="show-for-large-only">
					LARGE
				</div>

				<div class="show-for-xlarge-only">
					X-LARGE
				</div>

				<div class="show-for-xxlarge-only">
					XX-LARGE
				</div>

			</div>
		<?php endif; ?>

		<div class="off-canvas-wrapper">

			<div class="off-canvas position-left" id="offCanvas" data-off-canvas>

				<?php
				wp_nav_menu(
					array(
						'menu' 				=> 'mobile_nav', /* menu name */
						'menu_class' 		=> 'vertical menu drilldown',
						'theme_location' 	=> 'mobile_nav', /* where in the theme it's assigned */
						'container_class' 	=> 'mobile-nav', /* container tag */
						'depth' 			=> '4',
						'items_wrap'        => '<ul id="%1$s" class="%2$s" data-drilldown>%3$s</ul>',
						'walker' 			=> new mobile_nav_walker(),
						'fallback_cb'		=> false
					)
				);
				?>

			</div>

			<div class="off-canvas-content" data-off-canvas-content>

				<!-- Main Container !-->
				<div id="main-container">

					<nav id="site-mobile-navbar" class="title-bar">
						<button class="menu-icon" type="button" data-toggle="offCanvas"></button>
						<div class="title-bar-title">Menu</div>
					</nav>

					<!-- Header !-->
					<section id="header-container" role="banner">

						<header id="site-header-content">
							<div class="grid-x">
								<div class="large-12 cell">

									<div class="top-bar" id="main-menu">

										<?php
										// Adjust using Menus in Wordpress Admin
										if( has_nav_menu('main_nav') ) {
											//Get cached nav menu
											wp_nav_menu(
												array(
													'menu' 				=> 'main_nav', /* menu name */
													'menu_class' 		=> 'dropdown menu',
													'theme_location' 	=> 'main_nav', /* where in the theme it's assigned */
													'container' 		=> 'false', /* container tag */
													'depth' 			=> '4',
													'items_wrap'        => '<ul id="%1$s" class="%2$s" data-dropdown-menu>%3$s</ul>',
													'walker' 			=> new main_nav_walker(),
													'fallback_cb'		=> false
												)
											);
										}
										?>

									</div>

								</div>
							</div>
						</header>
					</section>

					<!-- Body Container !-->
					<div id="body-container">