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
		echo '<section id="primary" class="content-area"><div id="content" class="wpfc-sermon-container site-content ' . $additional_classes . '" role="main">';
		break;
	case 'bb-theme':
		echo '<div class="container"><div class="row"><div class="wpfc-sermon-container fl-content fl-content-left col-md-8 ' . $additional_classes . '">';
		break;
	case 'bb-theme-builder':
		echo '<div class="container"><div class="row"><div class="wpfc-sermon-container fl-content fl-content-left col-md-8 ' . $additional_classes . '">';
		break;
	case 'oceanwp':
		echo '<div id="content-wrap" class="container clr"><div id="primary" class="content-area clr"><div id="content" class="wpfc-sermon-container site-content clr ' . $additional_classes . '">';
		break;
	case 'x':
		echo '<div class="x-container max width offset"><div class="wpfc-sermon-container x-main left ' . $additional_classes . '" role="main">';
		break;
	case 'genesis':
		echo '<div class="content-sidebar-wrap"><main class="content" id="genesis-content">';
		break;
	case 'maranatha':
		echo '<main id="maranatha-content"><div id="maranatha-content-inner" class="maranatha-centered-large maranatha-entry-content"><div id="maranatha-loop-multiple" class="maranatha-clearfix maranatha-loop-two-columns">';
		break;
	case 'saved':
		echo '<main id="saved-content" class="saved-bg-contrast"><div id="saved-content-inner" class="saved-centered-large saved-entry-content"><div id="saved-loop-multiple" class="saved-clearfix saved-loop-entries saved-loop-three-columns">';
		break;
	case 'brandon':
		echo '<div id="Content"><div class="content_wrapper clearfix">';
		break;
	default:
		echo apply_filters( 'sm_templates_wrapper_start', '<div class="wrap"><div id="primary" class="content-area"><main id="main" class="site-main wpfc-sermon-container ' . $additional_classes . '">' );
		break;
}
