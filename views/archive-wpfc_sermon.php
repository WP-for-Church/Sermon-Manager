<?php
/**
 * Template used for displaying archive pages
 *
 * @package SM/Views
 */

get_header(); ?>

<?php echo wpfc_get_partial( 'content-sermon-wrapper-start' ); ?>

<?php echo render_wpfc_sorting(); ?>
<?php
if ( have_posts() ) :
	if ( function_exists( 'wpfc_sm_pro_is_templating_being_used' ) && wpfc_sm_pro_is_templating_being_used() ) :
		// Get SM PRO settings.
		$smpro_settings       = \SermonManagerPro\Templating\Settings::get_settings();
		$smpro_layout_columns = $smpro_settings['layout_columns'];

		echo '<style>.smpro-items {--smpro-layout-columns: ' . $smpro_layout_columns . '}</style>';
		echo '<div class="smpro-items">';
	endif;

	while ( have_posts() ) :
		the_post();
		wpfc_sermon_excerpt_v2(); // You can edit the content of this function in `partials/content-sermon-archive.php`.
	endwhile;

	if ( function_exists( 'wpfc_sm_pro_is_templating_being_used' ) && wpfc_sm_pro_is_templating_being_used() ) :
		echo '</div>';
	endif;

	if ( function_exists( 'wp_pagenavi' ) ) :
		wp_pagenavi();
	else :
		the_posts_pagination();
	endif;
else :
	__( 'Sorry, but there aren\'t any posts matching your query.' );
endif;
?>

<?php echo wpfc_get_partial( 'content-sermon-wrapper-end' ); ?>

<?php
get_footer();
