				
						</div>
						<!-- /Body Container !-->
					
					<!-- /Main Container !-->
					</div>
					
					<div id="footer-container">
						<footer role="contentinfo" class="row">
						
								<div class="large-12 columns">
			
									<div class="row">
			
										<nav class="large-10 columns clearfix">
											<?php
											if( has_nav_menu('footer_links') ) {
												//Get cached nav menu
											    PrsoCoreWpqueryModel::cached_nav_menu( 
											    	array(
											    		'menu' 				=> 'footer_links', /* menu name */
											    		'menu_class' 		=> 'link-list',
											    		'theme_location' 	=> 'footer_links', /* where in the theme it's assigned */
											    		'container_class' 	=> 'footer-links clearfix', /* container class */
											    		'walker' 			=> new footer_links_walker(),
											    		'fallback_cb'		=> false
											    	)
											    );
											}
											?>
										</nav>
			
									</div>
			
								</div>
								
						</footer> <!-- end footer -->
					</div>
				
				</div><!-- Close off-canvas content wrapper !-->
				
			</div><!-- Close off-canvas wrapper !-->
		</div><!-- Close off-canvas wrapper !-->
		
		<!--[if lt IE 7 ]>
  			<script src="//ajax.googleapis.com/ajax/libs/chrome-frame/1.0.3/CFInstall.min.js"></script>
  			<script>window.attachEvent('onload',function(){CFInstall.check({mode:'overlay'})})</script>
		<![endif]-->
		
		<?php wp_footer(); // js scripts are inserted using this function ?>

	</body>
	
</html>