<?php defined( 'ABSPATH' ) or exit;

$template = get_option( 'template' );

switch ( $template ) {
	case 'twentyeleven' :
		echo '</div>';
		get_sidebar();
		echo '</div>';
		break;
	case 'twentytwelve' :
		echo '</div></div>';
		break;
	case 'twentythirteen' :
		echo '</div></div>';
		break;
	case 'twentyfourteen' :
		echo '</div></div></div>';
		get_sidebar( 'content' );
		break;
	case 'twentyfifteen' :
		echo '</div></div>';
		break;
	case 'twentysixteen' :
		echo '</main></div>';
		break;
	case 'Divi':
		echo '</main>';
		get_sidebar();
		echo '</div></div></div>';
		break;
	default :
		echo '</div>';
		get_sidebar();
		echo '</div>';
		break;
}
