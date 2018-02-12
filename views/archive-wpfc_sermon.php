<?php
get_header(); ?>

<?php if ( wp_get_theme() == 'Divi' ) : ?>

    <div id="main-content">
        <div class="container">
        	<div id="content-area" class="clearfix">
                <main id="left-area" class="wpfc-sermon-archive">

        	        <?php echo render_wpfc_sorting(); ?>
            
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
                <?php get_sidebar(); ?>
            </div>
        </div>
    </div>
   
<?php else: ?>

    <div class="wrap">
        <div id="primary">
            <main class="wpfc-sermon-archive">

        	    <?php echo render_wpfc_sorting(); ?>
            
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

<?php endif; ?>

<?php get_footer();