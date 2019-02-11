<?php get_header(); ?>

<section class="grid-container">
	<div class="grid-x">

		<?php get_template_part( 'loop', 'search' ); ?>

		<?php get_sidebar(); // sidebar 1 ?>

	</div>
</section>

<?php get_footer(); ?>