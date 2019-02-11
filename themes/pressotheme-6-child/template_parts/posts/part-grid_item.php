<?php
/**
 * Template part for REST requests for posts, rendered in REST output under html node
 */
?>
<div class="posts-grid-item">

	<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">

		<?php if( has_post_thumbnail() ): ?>
			<div class="post-thumbnail">
				<?php the_post_thumbnail(); ?>
			</div>
		<?php endif; ?>

		<div class="inner-content">

			<h5><?php the_title(); ?></h5>

			<div class="post-meta">
				<?php the_date( 'l jS, Y' ); ?>
			</div>

			<?php the_excerpt(); ?>

		</div>

		<div class="post-categories">
			<?php echo get_the_category_list( ', ', '', get_the_ID() ); ?>
		</div>

	</a>

</div>
