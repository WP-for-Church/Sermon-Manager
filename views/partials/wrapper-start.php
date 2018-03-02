<?php defined( 'ABSPATH' ) or exit;

$template = get_option( 'template' );

switch ( $template ) {
	case 'twentyeleven' :
		echo '<div id="primary"><div id="content" role="main" class="wpfc-sermon-container wpfc-twentyeleven">';
		break;
	case 'twentytwelve' :
		echo '<div id="primary" class="site-content"><div id="content" role="main" class="wpfc-sermon-container wpfc-twentytwelve">';
		break;
	case 'twentythirteen' :
		echo '<div id="primary" class="content-area"><div id="content" role="main" class="site-content wpfc-sermon-container wpfc-twentythirteen">';
		break;
	case 'twentyfourteen' :
		echo '<div id="main-content" class="main-content"><div id="primary" class="content-area"><div id="content" class="site-content wpfc-sermon-container wpfc-twentyfourteen" role="main">';
		break;
	case 'twentyfifteen' :
		echo '<div id="primary" class="content-area"><div id="main" role="main" class="site-main wpfc-sermon-container wpfc-twentyfifteen">';
		break;
	case 'twentysixteen' :
		echo '<div id="primary" class="content-area"><main id="main" class="site-main wpfc-sermon-container wpfc-twentysixteen" role="main">';
		break;
	case 'twentyseventeen' :
		echo '<div class="wrap"><div id="primary" class="content-area"><main id="main" class="site-main wpfc-sermon-container wpfc-twentyseventeen">';
		break;
	case 'Divi':
		echo '<div id="main-content"><div class="container"><div id="content-area" class="clearfix"><main id="left-area" class="wpfc-sermon-container wpfc-divi">';
		break;
	case 'salient' :
		echo '<div class="container-wrap"><div class="container main-container"><div class="row"><div class="post-area col span_9"><div class="post-container wpfc-sermon-container wpfc-salient">';
		break;
	case 'Avada' :
		echo '<div class=""><div class=""><div class="wpfc-sermon-container wpfc-avada">';
		break;
	default :
		echo apply_filters( 'sm_templates_wrapper_start', '<div class="wrap"><div id="primary" class="content-area"><main id="main" class="site-main wpfc-sermon-container">' );
		break;
}
