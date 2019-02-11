<div class="large-8 columns clearfix" role="main">

	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
	
	<article id="post-<?php the_ID(); ?>" <?php post_class('clearfix'); ?> role="article">
		
		<header>
		
			<a href="<?php the_permalink() ?>" title="<?php the_title_attribute(); ?>"><?php the_post_thumbnail( 'wpf-featured' ); ?></a>
			
			<h2><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
			
			<p class="meta"><?php _e("Posted", "bonestheme"); ?> <time datetime="<?php echo the_time('Y-m-j'); ?>" pubdate><?php the_time('F jS, Y'); ?></time> <?php _e("by", "bonestheme"); ?> <?php the_author_posts_link(); ?> <span class="amp">&</span> <?php _e("filed under", "bonestheme"); ?> <?php the_category(', '); ?>.</p>
		
		</header> <!-- end article header -->
	
		<section class="post_content clearfix">
			<?php do_action( 'prso_get_the_excerpt' ); ?>
		</section> <!-- end article section -->
		
		<footer>

			<p class="tags"><?php the_tags('<span class="tags-title">Tags:</span> ', ' ', ''); ?></p>
			
		</footer> <!-- end article footer -->
	
	</article> <!-- end article -->
	
	<?php comments_template(); ?>
	
	<?php endwhile; ?>	
	
	<?php do_action('prso_pagination'); ?>	
	
	<?php else : ?>
	
	<article id="post-not-found">
	    <header>
	    	<h1>Not Found</h1>
	    </header>
	    <section class="post_content">
	    	<p>Sorry, but the requested resource was not found on this site.</p>
	    </section>
	    <footer>
	    </footer>
	</article>
	
	<?php endif; ?>

</div> <!-- end #main -->