<?php defined( 'ABSPATH' ) or exit;

$template = get_option( 'template' );

switch ( $template ) {
	case 'twentyeleven' :
		echo '</div></div>';
		if ( is_archive() ) {
			get_sidebar();
		}
		break;
	case 'twentytwelve' :
		echo '</div></div>';
		get_sidebar();
		break;
	case 'twentythirteen' :
		echo '</div></div>';
		break;
	case 'twentyfourteen' :
		echo '</div></div></div>';
		get_sidebar( 'content' );
		break;
	case 'twentyfifteen' :
		get_sidebar();
		echo '</div></div>';
		break;
	case 'twentysixteen' :
		echo '</main></div>';
		get_sidebar();
		break;
	case 'twentyseventeen' :
		echo '</main></div>';
		get_sidebar();
		break;
	case 'Divi':
		echo '</main>';
		get_sidebar();
		echo '</div></div></div>';
		break;
	case 'salient':
		echo '</div></div></div>';
		get_sidebar();
		echo '</div></div>';
		break;
	case 'Avada':
		echo '</div></div>';
		get_sidebar();
		echo '</div>';
		break;
	default :
		ob_start();
		get_sidebar();
		$sidebar = ob_get_clean();
		echo apply_filters( 'sm_templates_wrapper_end', '</main></div>' . $sidebar . '</div>' );
		break;
}
