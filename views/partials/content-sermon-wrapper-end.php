<?php
/**
 * Archive wrapper, for theme compatibility.
 *
 * @package SM/Views/Partials
 */

defined( 'ABSPATH' ) or exit;

$template = get_option( 'template' );

switch ( $template ) {
	case 'twentyeleven':
		echo '</div></div>';
		if ( is_archive() ) {
			get_sidebar();
		}
		break;
	case 'twentytwelve':
		echo '</div></div>';
		get_sidebar();
		break;
	case 'twentythirteen':
		echo '</div></div>';
		break;
	case 'twentyfourteen':
		echo '</div></div></div>';
		get_sidebar( 'content' );
		break;
	case 'twentyfifteen':
		get_sidebar();
		echo '</div></div>';
		break;
	case 'twentysixteen':
		echo '</main></div>';
		get_sidebar();
		break;
	case 'twentyseventeen':
		echo '</main></div>';
		get_sidebar();
		break;
	case 'twentynineteen':
		echo '</main></section>';
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
	case 'wpfc-morgan':
		echo '</div></section>';
		get_sidebar( 'sermon' );
		get_footer();
		break;
	case 'bb-theme':
		echo '</div>';
		get_sidebar();
		echo '</div></div>';
		break;
	case 'bb-theme-builder':
		echo '</div>';
		get_sidebar();
		echo '</div></div>';
		break;
	case 'oceanwp':
		echo '</div><!-- end of #content -->';
		echo '</div><!-- end of #primary -->';
		get_sidebar();
		echo '</div><!-- end of #content-wrap -->';
		break;
	case 'x':
		$fullwidth = get_post_meta( get_the_ID(), '_x_post_layout', true ); // phpcs:ignore

		echo '</div>';
		if ( 'on' != $fullwidth ) :
			get_sidebar();
		endif;
		echo '</div>';
		break;
	case 'genesis':
		echo '</main>';
		get_sidebar();
		echo '</div>';
		break;
	case 'maranatha':
		echo '</div></div></main>';
		break;
	case 'saved':
		echo '</div></div></main>';
		break;
	case 'brandon':
		echo '</div></div>';
		break;
	case 'hueman':
	case 'hueman-pro':
		echo '</div></section>';
		get_sidebar();
		break;
	case 'NativeChurch':
		echo '</div></div></div>';
		break;
	case 'betheme':
		echo '</div></div></div></div></div></div></div>';
		break;
	case 'dt-the7':
	case 'the7':
		echo '</div>';
		do_action( 'presscore_after_content' );
		break;
	case 'dunamis':
		$croma       = get_option( 'cromatic' );
		$sidebarrule = ( isset( $croma['cro_catsidebar'] ) ) ? esc_attr( $croma['cro_catsidebar'] ) : 3;

		echo '</div></div>';

		if ( $sidebarrule != 1 && $sidebarrule != 2 ) {
			echo '<div class="large-4 column">';
			get_sidebar();
			echo '</div>';
		}

		echo '</div></div>';
		break;
	case 'exodoswp':
		echo '</div></div></div></div>';
		break;
	default:
		if ( SM_OB_ENABLED ) {
			ob_start();
			get_sidebar();
			$sidebar = ob_get_clean();
		} else {
			$sidebar = '';
		}
		echo apply_filters( 'sm_templates_wrapper_end', '</main></div>' . $sidebar . '</div>' );
		break;
}
