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
			if ( ! apply_filters( 'sm_disable_sidebar', false ) ) {
				get_sidebar();
			}
		}
		break;
	case 'twentytwelve':
		echo '</div></div>';
		if ( ! apply_filters( 'sm_disable_sidebar', false ) ) {
			get_sidebar();
		}
		break;
	case 'twentythirteen':
		echo '</div></div>';
		break;
	case 'twentyfourteen':
		echo '</div></div></div>';
		if ( ! apply_filters( 'sm_disable_sidebar', false ) ) {
			get_sidebar( 'content' );
		}
		break;
	case 'twentyfifteen':
		if ( ! apply_filters( 'sm_disable_sidebar', false ) ) {
			get_sidebar();
		}
		echo '</div></div>';
		break;
	case 'twentysixteen':
		echo '</main></div>';
		if ( ! apply_filters( 'sm_disable_sidebar', false ) ) {
			get_sidebar();
		}
		break;
	case 'twentyseventeen':
		echo '</main></div>';
		if ( ! apply_filters( 'sm_disable_sidebar', false ) ) {
			get_sidebar();
		}
		break;
	case 'twentynineteen':
		echo '</main></section>';
		break;
	case 'Divi':
		echo '</main>';
		if ( ! apply_filters( 'sm_disable_sidebar', false ) ) {
			get_sidebar();
		}
		echo '</div></div></div>';
		break;
	case 'salient':
		echo '</div></div></div>';
		if ( ! apply_filters( 'sm_disable_sidebar', false ) ) {
			get_sidebar();
		}
		echo '</div></div>';
		break;
	case 'Avada':
		echo '</div></div>';
		if ( ! apply_filters( 'sm_disable_sidebar', false ) ) {
			get_sidebar();
		}
		echo '</div>';
		break;
	case 'wpfc-morgan':
		echo '</div></section>';
		if ( ! apply_filters( 'sm_disable_sidebar', false ) ) {
			get_sidebar( 'sermon' );
		}
		get_footer();
		break;
	case 'bb-theme':
		echo '</div>';
		if ( ! apply_filters( 'sm_disable_sidebar', false ) ) {
			get_sidebar();
		}
		echo '</div></div>';
		break;
	case 'bb-theme-builder':
		echo '</div>';
		if ( ! apply_filters( 'sm_disable_sidebar', false ) ) {
			get_sidebar();
		}
		echo '</div></div>';
		break;
	case 'oceanwp':
		echo '</div><!-- end of #content -->';
		echo '</div><!-- end of #primary -->';
		if ( ! apply_filters( 'sm_disable_sidebar', false ) ) {
			get_sidebar();
		}
		echo '</div><!-- end of #content-wrap -->';
		break;
	case 'pro':
	case 'x':
		$fullwidth = get_post_meta( get_the_ID(), '_x_post_layout', true ); // phpcs:ignore

		echo '</div>';
		if ( 'on' != $fullwidth ) :
			if ( ! apply_filters( 'sm_disable_sidebar', false ) ) {
				get_sidebar();
			}
		endif;
		echo '</div>';
		break;
	case 'genesis':
		echo '</main>';
		if ( ! apply_filters( 'sm_disable_sidebar', false ) ) {
			get_sidebar();
		}
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
		if ( ! apply_filters( 'sm_disable_sidebar', false ) ) {
			get_sidebar();
		}
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

		if ( ! apply_filters( 'sm_disable_sidebar', false ) ) {
			if ( $sidebarrule != 1 && $sidebarrule != 2 ) {
				echo '<div class="large-4 column">';
				get_sidebar();
				echo '</div>';
			}
		}

		echo '</div></div>';
		break;
	case 'exodoswp':
		echo '</div></div></div></div>';
		break;
	case 'kerygma':
		echo '</div><!-- #content -->';
		if ( ! apply_filters( 'sm_disable_sidebar', false ) ) {
			get_sidebar();
		}
		echo '</div><!-- #page-wrap -->';
		get_footer();
		break;
	case 'uncode':
		if ( ! is_single() ) {
			$limit_width          = $limit_content_width = $the_content = $main_content = $layout = $sidebar_style = $sidebar_bg_color = $sidebar = $sidebar_size = $sidebar_sticky = $sidebar_padding = $sidebar_inner_padding = $sidebar_content = $title_content = $navigation_content = $page_custom_width = $row_classes = $main_classes = $footer_classes = $generic_body_content_block = '';
			$index_has_navigation = false;
			$sidebar_fill         = ot_get_option( '_uncode_' . $post_type . '_sidebar_fill' );
			$sidebar_bg_color     = ot_get_option( '_uncode_' . $post_type . '_sidebar_bgcolor' );
			$style                = ot_get_option( '_uncode_general_style' );
			$bg_color             = ot_get_option( '_uncode_general_bg_color' );
			$bg_color             = ( $bg_color == '' ) ? ' style-' . $style . '-bg' : ' style-' . $bg_color . '-bg';

			$the_content .= ob_get_clean();

			$the_content .=
				'</div>
				</div>
			</div>';

			if ( $layout === 'sidebar_right' || $layout === 'sidebar_left' ) {

				/** Build structure with sidebar **/

				if ( $sidebar_size === '' ) {
					$sidebar_size = 4;
				}
				$main_size  = 12 - $sidebar_size;
				$expand_col = '';

				/** Collect paddings data **/

				$footer_classes = ' no-top-padding double-bottom-padding';

				if ( $sidebar_bg_color !== '' ) {
					if ( $sidebar_fill === 'on' ) {
						$sidebar_inner_padding .= ' std-block-padding';
						$sidebar_padding       .= $sidebar_bg_color;
						$expand_col            = ' unexpand';
						if ( $limit_content_width === '' ) {
							$row_classes    .= ' no-h-padding col-no-gutter no-top-padding';
							$footer_classes = ' std-block-padding no-top-padding';
							$main_classes   .= ' std-block-padding';
						} else {
							$row_classes  .= ' no-top-padding';
							$main_classes .= ' double-top-padding';
						}
					} else {
						$row_classes           .= ' double-top-padding';
						$row_classes           .= ' double-bottom-padding';
						$sidebar_inner_padding .= $sidebar_bg_color . ' single-block-padding';
					}
				} else {
					$row_classes  .= ' col-std-gutter double-top-padding';
					$main_classes .= ' double-bottom-padding';
				}

				$row_classes           .= ' no-bottom-padding';
				$sidebar_inner_padding .= ' double-bottom-padding';

				/** Build sidebar **/

				$sidebar_content = "";
				ob_start();
				if ( $sidebar !== '' ) {
					dynamic_sidebar( $sidebar );
				} else {
					dynamic_sidebar( 1 );
				}
				$sidebar_content = ob_get_clean();

				/** Create html with sidebar **/

				$the_content = '<div class="post-content style-' . $style . $main_classes . '">' . $the_content . '</div>';

				$main_content = '<div class="col-lg-' . $main_size . '">
											' . $the_content . '
										</div>';

				$the_content = '<div class="row-container">
        							<div class="row row-parent un-sidebar-layout' . $row_classes . $limit_content_width . '"' . $page_custom_width . '>
												<div class="row-inner">
													' . ( ( $layout === 'sidebar_right' ) ? $main_content : '' ) . '
													<div class="col-lg-' . $sidebar_size . '">
														<div class="uncol style-' . $sidebar_style . $expand_col . $sidebar_padding . ( ( $sidebar_fill === 'on' && $sidebar_bg_color !== '' ) ? '' : $sidebar_sticky ) . '">
															<div class="uncoltable' . ( ( $sidebar_fill === 'on' && $sidebar_bg_color !== '' ) ? $sidebar_sticky : '' ) . '">
																<div class="uncell' . $sidebar_inner_padding . '">
																	<div class="uncont">
																		' . $sidebar_content . '
																	</div>
																</div>
															</div>
														</div>
													</div>
													' . ( ( $layout === 'sidebar_left' ) ? $main_content : '' ) . '
												</div>
											</div>
										</div>';
			} else {

				/** Create html without sidebar **/
				if ( $generic_body_content_block === '' ) {
					$the_content = '<div class="post-content un-no-sidebar-layout"' . $page_custom_width . '>' . uncode_get_row_template( $the_content, $limit_width, $limit_content_width, $style, '', 'double', true, 'double' ) . '</div>';
				} else {
					$the_content = '<div class="post-content un-no-sidebar-layout"' . $page_custom_width . '>' . $the_content . '</div>';
				}

			}

			/** Build and display navigation html **/
			$remove_pagination = ot_get_option( '_uncode_' . $post_type . '_remove_pagination' );
			if ( ! $index_has_navigation && $remove_pagination !== 'on' ) {
				$navigation_option = ot_get_option( '_uncode_' . $post_type . '_navigation_activate' );
				if ( $navigation_option !== 'off' ) {
					$navigation = uncode_posts_navigation();
					if ( ! empty( $navigation ) && $navigation !== '' ) {
						$navigation_content = uncode_get_row_template( $navigation, '', $limit_content_width, $style, ' row-navigation row-navigation-' . $style, true, true, true );
					}
				}
			}

			/** Display post html **/
			echo '<div class="page-body' . $bg_color . '">
          <div class="post-wrapper">
          	<div class="post-body">' . do_shortcode( $the_content ) . '</div>' .
			     $navigation_content . '
          </div>
        </div>';

			get_footer();
		} else {
			$the_content .= ob_get_clean();
			echo uncode_remove_p_tag( $the_content ) . '</div></div></div></article>';
		}
		break;
	default:
		if ( SM_OB_ENABLED ) {
			ob_start();
			if ( ! apply_filters( 'sm_disable_sidebar', false ) ) {
				get_sidebar();
			}
			$sidebar = ob_get_clean();
		} else {
			$sidebar = '';
		}
		echo apply_filters( 'sm_templates_wrapper_end', '</main></div>' . $sidebar . '</div>' );
		break;
}
