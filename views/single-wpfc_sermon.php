<?php
get_header(); ?>

<?php if ( wp_get_theme() == 'Divi' ) : ?>

    <div id="main-content">
    	<div class="container">
    		<div id="content-area" class="clearfix">
    			<main id="left-area" class="wpfc-sermon-singular">
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
    	</div>
    </div>

<?php else: ?>

    <div class="wrap">
	    <div id="primary">
		    <main class="wpfc-sermon-singular">

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

<?php endif; ?>

<?php get_footer();