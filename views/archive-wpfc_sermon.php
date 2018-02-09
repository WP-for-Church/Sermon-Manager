<?php
get_header(); ?>

<div id="" class="wrap">
    <div id="primary" class="">
        <main id="" class="wpfc-sermon-archive">
            
			<?php
			if ( have_posts() ) :
				while ( have_posts() ) : the_post();
					wpfc_sermon_excerpt_v2();
				endwhile;
				the_posts_pagination();
			else :
				__('Sorry, but there aren\'t any posts matching your query.', 'placeholder');
			endif;
			?>

        </main>
    </div>
    
    <?php get_sidebar(); ?>
</div>

<?php get_footer();