<?php
/**
 * Archive wrapper, for theme compatibility.
 *
 * @package SM/Views/Partials
 */

defined( 'ABSPATH' ) or exit;

$template = get_option( 'template' );

/**
 * Allows to add additional classes to container div.
 *
 * @param string $template The theme ID.
 *
 * @return array The classes array.
 *
 * @since 2.15.0
 */
$additional_classes = apply_filters( 'sm_templates_additional_classes', array(), $template );
$additional_classes = implode( ' ', $additional_classes );

switch ( $template ) {
	case 'twentyeleven':
		echo '<div id="primary"><div id="content" role="main" class="wpfc-sermon-container wpfc-twentyeleven ' . $additional_classes . '">';
		break;
	case 'twentytwelve':
		echo '<div id="primary" class="site-content"><div id="content" role="main" class="wpfc-sermon-container wpfc-twentytwelve ' . $additional_classes . '">';
		break;
	case 'twentythirteen':
		echo '<div id="primary" class="content-area"><div id="content" role="main" class="site-content wpfc-sermon-container wpfc-twentythirteen ' . $additional_classes . '">';
		break;
	case 'twentyfourteen':
		echo '<div id="main-content" class="main-content"><div id="primary" class="content-area"><div id="content" class="site-content wpfc-sermon-container wpfc-twentyfourteen ' . $additional_classes . '" role="main">';
		break;
	case 'twentyfifteen':
		echo '<div id="primary" class="content-area"><div id="main" role="main" class="site-main wpfc-sermon-container wpfc-twentyfifteen ' . $additional_classes . '">';
		break;
	case 'twentysixteen':
		echo '<div id="primary" class="content-area"><main id="main" class="site-main wpfc-sermon-container wpfc-twentysixteen ' . $additional_classes . '" role="main">';
		break;
	case 'twentyseventeen':
		echo '<div class="wrap"><div id="primary" class="content-area"><main id="main" class="site-main wpfc-sermon-container wpfc-twentyseventeen ' . $additional_classes . '">';
		break;
	case 'twentynineteen':
		echo '<section id="primary" class="content-area"><main id="main" class="site-main wpfc-twentynineteen ' . $additional_classes . '">';
		break;
	case 'Divi':
		echo '<div id="main-content"><div class="container"><div id="content-area" class="clearfix"><main id="left-area" class="wpfc-sermon-container wpfc-divi ' . $additional_classes . '">';
		break;
	case 'salient':
		echo '<div class="container-wrap"><div class="container main-container"><div class="row"><div class="post-area col span_9"><div class="post-container wpfc-sermon-container wpfc-salient ' . $additional_classes . '">';
		break;
	case 'Avada':
		echo '<div class=""><div class=""><div class="wpfc-sermon-container wpfc-avada ' . $additional_classes . '">';
		break;
	case 'wpfc-morgan':
		echo '<section id="primary" class="content-area"><div id="content" class="wpfc-sermon-container site-content wpfc-morgan ' . $additional_classes . '" role="main">';
		break;
	case 'bb-theme':
		echo '<div class="container"><div class="row"><div class="wpfc-sermon-container fl-content fl-content-left col-md-8 wpfc-bb-theme' . $additional_classes . '">';
		break;
	case 'bb-theme-builder':
		echo '<div class="container"><div class="row"><div class="wpfc-sermon-container fl-content fl-content-left col-md-8 wpfc-bb-theme-builder ' . $additional_classes . '">';
		break;
	case 'oceanwp':
		echo '<div id="content-wrap" class="container clr"><div id="primary" class="content-area clr"><div id="content" class="wpfc-sermon-container site-content clr wpfc-oceanwp ' . $additional_classes . '">';
		break;
	case 'pro':
	case 'x':
		if ( function_exists( 'x_main_content_class' ) ) {
			ob_start();
			x_main_content_class();
			$additional_classes .= ob_get_clean();
		} else {
			$additional_classes .= 'x-main left'; // Use some default.
		}

		echo '<div class="x-container max width offset"><div class="' . $additional_classes . '" role="main">';
		break;
	case 'genesis':
		echo '<div class="content-sidebar-wrap"><main class="content wpfc-sermon-container wpfc-genesis ' . $additional_classes . '" id="genesis-content">';
		break;
	case 'maranatha':
		echo '<main id="maranatha-content"><div id="maranatha-content-inner" class="maranatha-centered-large maranatha-entry-content"><div id="maranatha-loop-multiple" class="maranatha-clearfix maranatha-loop-two-columns wpfc-sermon-container wpfc-maranatha ' . $additional_classes . '">';
		break;
	case 'saved':
		echo '<main id="saved-content" class="saved-bg-contrast"><div id="saved-content-inner" class="saved-centered-large saved-entry-content"><div id="saved-loop-multiple" class="saved-clearfix saved-loop-entries saved-loop-three-columns wpfc-sermon-container wpfc-saved ' . $additional_classes . '">';
		break;
	case 'brandon':
		echo '<div id="Content"><div class="content_wrapper clearfix wpfc-sermon-container wpfc-brandon ' . $additional_classes . '">';
		break;
	case 'hueman':
	case 'hueman-pro':
		echo '<section class="content"><div class="pad group wpfc-sermon-container wpfc-hueman ' . $additional_classes . '">';
		break;
	case 'NativeChurch':
		echo '<div id="content" class="content full"><div class="container"><div class="row"><div class="col-md-12 posts-archive wpfc-sermon-container wpfc-NativeChurch ' . $additional_classes . '" id="content-col">';
		break;
	case 'betheme':
		echo '<div id="Content"><div class="content_wrapper clearfix"><!-- .sections_group --><div class="sections_group"><div class="section "><div class="section_wrapper clearfix"><div class="column one column_blog"><div class="blog_wrapper isotope_wrapper wpfc-sermon-container wpfc-betheme ' . $additional_classes . '">';
		break;
	case 'dt-the7':
	case 'the7':
		echo '<div id="content" class="content" role="main">';
		the_archive_description( '<div class="taxonomy-description">', '</div>' );
		break;
	case 'dunamis':
		$croma        = get_option( 'cromatic' );
		$sidebarrule  = isset( $croma['cro_catsidebar'] ) ? esc_attr( $croma['cro_catsidebar'] ) : 3;
		$sidebarclass = $sidebarrule == 2 ? 'large-12' : 'large-8';
		$padclass     = $sidebarrule == 1 ? 'croma_pad_left' : 'croma_pad_right';
		$padclass     = $sidebarrule == 2 ? '' : $padclass;

		get_template_part( 'inc/templates/cromaheader' );

		echo '<div class="main singleitem"><div class="row singlepage">';

		if ( $sidebarrule == 1 ) {
			echo '<div class="large-4 column">';
			get_sidebar();
			echo '</div>';
		}

		echo '<div class="', $sidebarclass, ' column">';
		echo '<div class=', $padclass, '">';

		break;
	case 'exodoswp':
		if ( function_exists( 'exodoswp_redux' ) ) {
			$class = '';
			if ( exodoswp_redux( 'mt_blog_layout' ) == 'mt_blog_fullwidth' ) {
				$class = 'vc_row';
			} elseif ( exodoswp_redux( 'mt_blog_layout' ) == 'mt_blog_right_sidebar' or exodoswp_redux( 'mt_blog_layout' ) == 'mt_blog_left_sidebar' ) {
				$class = 'vc_col-md-9';
			}
			$sidebar = exodoswp_redux( 'mt_blog_layout_sidebar' );
		}
		echo '<div class="high-padding"><div class="container blog-posts"><div class="vc_row"><div class="col-md-12 main-content">';
		break;
	default:
		echo apply_filters( 'sm_templates_wrapper_start', '<div class="wrap"><div id="primary" class="content-area"><main id="main" class="site-main wpfc-sermon-container ' . $additional_classes . '">' );
		break;
}
