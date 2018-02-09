<?php
get_header(); ?>

<div class="wrap">
	<div id="primary" class="">
		<main id="" class="wpfc-sermon-singular">

			<?php
			while ( have_posts() ) : the_post();

				wpfc_sermon_single_v2();

				if ( comments_open() || get_comments_number() ) :
					comments_template();
				endif;

			endwhile;
			?>

		</main>
	</div>

	<?php get_sidebar(); ?>
</div>

<?php get_footer();
