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
		
		<!-- off canvas wrap !-->
		<div class="off-canvas-wrapper">
		
			<div class="off-canvas-wrapper-inner" data-off-canvas-wrapper>
				
				<!-- Off canvas menu items !-->
				<div class="off-canvas position-left" id="offCanvas" data-off-canvas data-position="left">
					
					<button data-close="" type="button" aria-label="Close menu" class="close-button">
				        <span aria-hidden="true">×</span>
				    </button>
					
					<?php 
						wp_nav_menu( 
					    	array( 
					    		'menu' 				=> 'mobile_nav', /* menu name */
					    		'menu_class' 		=> 'vertical menu',
					    		'theme_location' 	=> 'mobile_nav', /* where in the theme it's assigned */
					    		'container_class' 	=> 'mobile-nav', /* container tag */
					    		'depth' 			=> '2',
					    		'items_wrap'        => '<ul id="%1$s" class="%2$s" data-drilldown>%3$s</ul>',
					    		'walker' 			=> new mobile_nav_walker(),
					    		'fallback_cb'		=> false
					    	)
					    );
					?>
					
				</div>
				
				<div class="off-canvas-content" data-off-canvas-content>
					
					<!-- Mobile nav activate button !-->
					<div class="title-bar show-for-small-only show-for-medium-portrait">
					
						<div class="title-bar-left">
						
							<button class="menu-icon" type="button" data-open="offCanvas"></button>
							
							<span class="title-bar-title"><?php bloginfo('name'); ?></span>
							
						</div>
						
					</div>
					
					<!-- Main Container !-->
					<div id="main-container">
						
						<!-- Dev Helper for media quries !-->
						<?php if (defined('WP_DEBUG') && TRUE === WP_DEBUG): ?>
						<div id="dev-size-helper">
							<p class="show-for-small-only">SMALL</p>
							<p class="show-for-medium-only">MEDIUM</p>
							<p class="show-for-large-only">LARGE</p>
						</div>
						<?php endif; ?>
												
						<!-- Header Row !-->
						<div id="header-container" class="row">
								
							<div class="large-12 columns">
								<header role="banner" id="top-header">
									
									<div class="siteinfo">
										<h1><a class="brand" id="logo" href="<?php echo get_bloginfo('url'); ?>"><?php bloginfo('name'); ?></a></h1>
										<h4 class="subhead"><?php echo get_bloginfo ( 'description' ); ?></h4>
									</div>
										
									<div class="top-bar hide-for-small-only hide-for-medium-portrait">
									
									  <div class="top-bar-left">
									  	
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
									  
									  <div class="top-bar-right">
									  	
									  	<form action="<?php echo home_url( '/' ); ?>" method="get">
									      <div class="large-12 columns">
									        <input type="text" id="search" placeholder="Search" name="s" value="<?php the_search_query(); ?>" />
									      </div>
								  		</form>
									  	
									  </div>
									  
									</div>
									
								</header> <!-- end header -->
							</div>
							
						</div>
						<!-- /Header Row !-->
						
							<!-- Body Container !-->
							<div id="body-container">
								