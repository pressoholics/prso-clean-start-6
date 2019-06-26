				
						</div>
						<!-- /Body Container !-->
					
					<!-- /Main Container !-->
					</div>
					
					<footer id="footer-container" class="grid-container">
						<div class="grid-x">

							<?php if( has_nav_menu('footer_links') ): ?>
							<nav id="footer-one" class="footer-nav">
								<h6><?php echo prso_get_nav_menu_meta( 'footer_links' ); ?></h6>
								<?php
									wp_nav_menu(
									array(
										'menu' 				=> 'footer_links', /* menu name */
										'theme_location' 	=> 'footer_links', /* where in the theme it's assigned */
										'depth' 			=> '1',
										'fallback_cb'		=> false
									)
								);
								?>
							</nav>
							<?php endif; ?>
								
						</div> <!-- end footer -->
					</footer>

				</div><!-- /.off-canvas-content !-->

			</div><!-- /.off-canvas-wrapper !-->

		<!--[if lt IE 7 ]>
  			<script src="//ajax.googleapis.com/ajax/libs/chrome-frame/1.0.3/CFInstall.min.js"></script>
  			<script>window.attachEvent('onload',function(){CFInstall.check({mode:'overlay'})})</script>
		<![endif]-->
		
		<?php wp_footer(); // js scripts are inserted using this function ?>

	</body>
	
</html>