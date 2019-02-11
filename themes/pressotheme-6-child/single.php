<?php get_header(); ?>

<section class="grid-container">
	<div class="grid-x">

		<?php get_template_part( 'loop', 'single' ); ?>

		<?php get_sidebar(); ?>

	</div>
</section>

<?php get_footer(); ?>