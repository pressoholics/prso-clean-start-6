<div class="large-8 cell clearfix" role="main">
				
	<h1><span>Search Results for:</span> <?php echo esc_attr(get_search_query()); ?></h1>

	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
	
	<article id="post-<?php the_ID(); ?>" <?php post_class('clearfix'); ?> role="article">
		
		<header>
			
			<h3>
				<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>">
					<?php the_title(); ?>
				</a>
			</h3>

			<p class="meta">
				<?php _e("Posted", PRSOTHEMEFRAMEWORK__DOMAIN); ?>
				<time datetime="<?php echo the_time('Y-m-j'); ?>" pubdate>
					<?php the_time('F jS, Y'); ?>
				</time>
				<?php _e("by", PRSOTHEMEFRAMEWORK__DOMAIN); ?>
				<?php the_author_posts_link(); ?>
				<span class="amp">&</span>
				<?php _e("filed under", PRSOTHEMEFRAMEWORK__DOMAIN); ?>
				<?php the_category(', '); ?>.
			</p>

		</header> <!-- end article header -->
	
		<section class="post_content">
			<?php the_excerpt('<span class="read-more">Read more on "'.the_title('', '', false).'" &raquo;</span>'); ?>
	
		</section> <!-- end article section -->
		
		<footer>
	
			
		</footer> <!-- end article footer -->
	
	</article> <!-- end article -->
	
	<?php endwhile; ?>

	<?php prso_theme_paginate(); // use the page navi function ?>
	
	<?php else : ?><?php endif; ?>

</div> <!-- end #main -->