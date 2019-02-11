<div id="sidebar" class="sidebar small-12 medium-4 cell" role="complementary">

	<div class="panel">

		<?php if ( is_active_sidebar( 'sidebar_main' ) ) : ?>

			<?php dynamic_sidebar( 'sidebar_main' ); ?>

		<?php else : ?>

			<!-- This content shows up if there are no widgets defined in the backend. -->

			<div class="alert-box">Please activate some Widgets.</div>

		<?php endif; ?>

	</div>

</div>