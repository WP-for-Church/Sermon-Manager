<?php
get_header();

$wpfc_sermon_wrap_class = 'wrap';
$wpfc_sermon_primary_class = 'primary';
$wpfc_sermon_main_class = 'wpfc-sermon-archive';

?>

<div class="<?php echo $wpfc_sermon_wrap_class ?>">
    <div id="primary" class="<?php echo $wpfc_sermon_primary_class ?>">
        <main class="<?php echo $wpfc_sermon_main_class ?>">
        	
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

<?php get_footer();