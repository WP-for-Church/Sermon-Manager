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
		if ( ! apply_filters( 'sm_disable_sidebar', false ) ) {
			$croma        = get_option( 'cromatic' );
			$sidebarrule  = isset( $croma['cro_catsidebar'] ) ? esc_attr( $croma['cro_catsidebar'] ) : 3;
			$sidebarclass = $sidebarrule == 2 ? 'large-12' : 'large-8';
			$padclass     = $sidebarrule == 1 ? 'croma_pad_left' : 'croma_pad_right';
			$padclass     = $sidebarrule == 2 ? '' : $padclass;
		} else {
			$sidebarclass = 'large-12';
			$padclass     = '';
		}

		get_template_part( 'inc/templates/cromaheader' );

		echo '<div class="main singleitem"><div class="row singlepage">';

		if ( ! apply_filters( 'sm_disable_sidebar', false ) ) {
			if ( $sidebarrule == 1 ) {
				echo '<div class="large-4 column">';
				get_sidebar();
				echo '</div>';
			}
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
	case 'kerygma':
		echo '<div id="page-wrap" class="clearfix"><div id="content" class="site-content" role="main">';
		break;
	case 'uncode':
		if ( ! is_single() ) {
			get_header();
			$limit_width          = $limit_content_width = $the_content = $main_content = $layout = $sidebar_style = $sidebar_bg_color = $sidebar = $sidebar_size = $sidebar_sticky = $sidebar_padding = $sidebar_inner_padding = $sidebar_content = $title_content = $navigation_content = $page_custom_width = $row_classes = $main_classes = $footer_classes = $generic_body_content_block = '';
			$index_has_navigation = false;

			if ( isset( $post->post_type ) ) {
				$post_type = $post->post_type . '_index';
			} else {
				global $wp_taxonomies;
				if ( isset( $wp_taxonomies[ $wp_query->get_queried_object()->taxonomy ] ) ) {
					$get_object = $wp_taxonomies[ $wp_query->get_queried_object()->taxonomy ];
					$post_type  = $get_object->object_type[0] . '_index';
				}
			}

			$tax                = ( isset( get_queried_object()->term_id ) ) ? get_queried_object()->term_id : '';
			$single_post_width  = ot_get_option( '_uncode_' . $post_type . '_single_width' );
			$single_text_length = ot_get_option( '_uncode_' . $post_type . '_single_text_length' );
			set_query_var( 'single_post_width', $single_post_width );
			if ( $single_text_length !== '' ) {
				set_query_var( 'single_text_length', $single_text_length );
			}

			/** Get general datas **/
			$style    = ot_get_option( '_uncode_general_style' );
			$bg_color = ot_get_option( '_uncode_general_bg_color' );
			$bg_color = ( $bg_color == '' ) ? ' style-' . $style . '-bg' : ' style-' . $bg_color . '-bg';

			/** Get page width info **/
			$generic_content_full = ot_get_option( '_uncode_' . $post_type . '_layout_width' );
			if ( $generic_content_full === '' ) {
				$main_content_full = ot_get_option( '_uncode_body_full' );
				if ( $main_content_full === '' || $main_content_full === 'off' ) {
					$limit_content_width = ' limit-width';
				}
			} else {
				if ( $generic_content_full === 'limit' ) {
					$generic_custom_width = ot_get_option( '_uncode_' . $post_type . '_layout_width_custom' );
					if ( isset( $generic_custom_width[0] ) && isset( $generic_custom_width[1] ) ) {
						if ( $generic_custom_width[1] === 'px' ) {
							$page_custom_width[0] = 12 * round( ( $generic_custom_width[0] ) / 12 );
						}
						if ( is_array( $generic_custom_width ) && ! empty( $generic_custom_width ) ) {
							$page_custom_width = ' style="max-width: ' . implode( '', $generic_custom_width ) . '; margin: auto;"';
						}
					} else {
						$limit_content_width = ' limit-width';
					}
				}
			}

			/** Collect header data **/
			$page_header_type = ot_get_option( '_uncode_' . $post_type . '_header' );
			if ( $page_header_type !== '' && $page_header_type !== 'none' ) {
				$metabox_data['_uncode_header_type'] = array( $page_header_type );
				$term_back                           = get_option( '_uncode_taxonomy_' . $tax );

				$author = get_user_by( 'slug', get_query_var( 'author_name' ) );
				if ( is_author() ) {
					$user_uncode_meta = get_the_author_meta( 'user_uncode_meta', $author->ID );
				}

				if ( isset( $term_back['term_media'] ) && $term_back['term_media'] !== '' ) {
					$featured_image = $term_back['term_media'];
				} elseif ( isset( $user_uncode_meta['term_media'] ) && $user_uncode_meta['term_media'] !== '' ) {
					$featured_image = $user_uncode_meta['term_media'];
				} else {
					$featured_image = '';
				}
				$meta_data    = uncode_get_general_header_data( $metabox_data, $post_type, $featured_image );
				$metabox_data = $meta_data['meta'];
				$show_title   = $meta_data['show_title'];
			}

			/** Get layout info **/
			$activate_sidebar = ot_get_option( '_uncode_' . $post_type . '_activate_sidebar' );
			$sidebar_name     = ot_get_option( '_uncode_' . $post_type . '_sidebar' );

			if ( $activate_sidebar !== 'off' && is_active_sidebar( $sidebar_name ) ) {
				$layout = ot_get_option( '_uncode_' . $post_type . '_sidebar_position' );
				if ( $layout === '' ) {
					$layout = 'sidebar_right';
				}
				$sidebar          = ot_get_option( '_uncode_' . $post_type . '_sidebar' );
				$sidebar_style    = ot_get_option( '_uncode_' . $post_type . '_sidebar_style' );
				$sidebar_size     = ot_get_option( '_uncode_' . $post_type . '_sidebar_size' );
				$sidebar_sticky   = ot_get_option( '_uncode_' . $post_type . '_sidebar_sticky' );
				$sidebar_sticky   = ( $sidebar_sticky === 'on' ) ? ' sticky-element sticky-sidebar' : '';
				$sidebar_fill     = ot_get_option( '_uncode_' . $post_type . '_sidebar_fill' );
				$sidebar_bg_color = ot_get_option( '_uncode_' . $post_type . '_sidebar_bgcolor' );
				$sidebar_bg_color = ( $sidebar_bg_color !== '' ) ? ' style-' . $sidebar_bg_color . '-bg' : '';
				if ( $sidebar_style === '' ) {
					$sidebar_style = $style;
				}
			}

			/** Get breadcrumb info **/
			$generic_breadcrumb = ot_get_option( '_uncode_' . $post_type . '_breadcrumb' );
			$show_breadcrumb    = ( $generic_breadcrumb === 'off' ) ? false : true;
			if ( $show_breadcrumb ) {
				$breadcrumb_align = ot_get_option( '_uncode_' . $post_type . '_breadcrumb_align' );
			}

			/** Get title info **/
			$generic_show_title = ot_get_option( '_uncode_' . $post_type . '_title' );
			$show_title         = ( $generic_show_title === 'off' ) ? false : true;

			$posts_counter = $wp_query->post_count;

			/** Build header **/
			if ( $page_header_type !== '' && $page_header_type !== 'none' ) {
				$get_title    = uncode_archive_title();
				$get_subtitle = isset( get_queried_object()->description ) ? get_queried_object()->description : '';

				if ( ot_get_option( '_uncode_' . $post_type . '_custom_title_activate' ) === 'on' && ! is_category() && ! is_tax() ) {
					$get_title    = ot_get_option( '_uncode_' . $post_type . '_custom_title_text' );
					$get_subtitle = ot_get_option( '_uncode_' . $post_type . '_custom_subtitle_text' );
				}

				$get_title    = apply_filters( 'uncode_archive_title', $get_title );
				$get_subtitle = apply_filters( 'uncode_archive_subtitle', $get_subtitle );
				$page_header  = new unheader( $metabox_data, $get_title, $get_subtitle );

				$header_html = $page_header->html;
				if ( $header_html !== '' ) {
					echo '<div id="page-header">';
					echo uncode_remove_p_tag( $page_header->html );
					echo '</div>';
				}
			}
			echo '<script type="text/javascript">UNCODE.initHeader();</script>';

			/** Build breadcrumb **/

			if ( $show_breadcrumb ) {
				if ( $breadcrumb_align === '' ) {
					$breadcrumb_align = 'right';
				}
				$breadcrumb_align = ' text-' . $breadcrumb_align;

				$content_breadcrumb = uncode_breadcrumbs();
				$breadcrumb_title   = '<div class="breadcrumb-title h5 text-bold">' . uncode_archive_title() . '</div>';
				echo uncode_get_row_template( $breadcrumb_title . $content_breadcrumb, '', ( $page_custom_width !== '' ? ' limit-width' : $limit_content_width ), $style, ' row-breadcrumb row-breadcrumb-' . $style . $breadcrumb_align, 'half', true, 'half' );
			}

			/** Build title **/

			if ( $show_title ) {
				$get_title     = uncode_archive_title();
				$title_content = '<div class="post-title-wrapper"><h1 class="post-title">' . $get_title . '</h1></div>';
			}

			$the_content                .= $title_content;
			$generic_body_content_block = ot_get_option( '_uncode_' . $post_type . '_content_block' );

			$the_content .=
				'<div id="index-' . rand() . '" class="isotope-system">
				<div class="isotope-wrapper single-gutter">
					<div class="isotope-container isotope-layout style-masonry isotope-pagination" data-type="masonry" data-layout="masonry" data-lg="800">';

			ob_start();
		} else {
			get_header();

			/**
			 * DATA COLLECTION - START
			 *
			 */

			/** Init variables **/
			$limit_width  = $limit_content_width = $the_content = $main_content = $layout = $bg_color = $sidebar_style = $sidebar_bg_color = $sidebar = $sidebar_size = $sidebar_sticky = $sidebar_padding = $sidebar_inner_padding = $sidebar_content = $title_content = $media_content = $navigation_content = $page_custom_width = $row_classes = $main_classes = $footer_content = $footer_classes = $content_after_body = '';
			$with_builder = false;

			$post_type = $post->post_type;

			/** Get general datas **/
			if ( isset( $metabox_data ) ) {
				if ( isset( $metabox_data['_uncode_specific_style'][0] ) && $metabox_data['_uncode_specific_style'][0] !== '' ) {
					$style = $metabox_data['_uncode_specific_style'][0];
					if ( isset( $metabox_data['_uncode_specific_bg_color'][0] ) && $metabox_data['_uncode_specific_bg_color'][0] !== '' ) {
						$bg_color = $metabox_data['_uncode_specific_bg_color'][0];
					}
				} else {
					$style = ot_get_option( '_uncode_general_style' );
					if ( isset( $metabox_data['_uncode_specific_bg_color'][0] ) && $metabox_data['_uncode_specific_bg_color'][0] !== '' ) {
						$bg_color = $metabox_data['_uncode_specific_bg_color'][0];
					} else {
						$bg_color = ot_get_option( '_uncode_general_bg_color' );
					}
				}
			}
			$bg_color = ( $bg_color == '' ) ? ' style-' . $style . '-bg' : ' style-' . $bg_color . '-bg';


			/** Get page width info **/
			$boxed = ot_get_option( '_uncode_boxed' );

			$page_content_full = ( isset( $metabox_data['_uncode_specific_layout_width'][0] ) ) ? $metabox_data['_uncode_specific_layout_width'][0] : '';
			if ( $page_content_full === '' ) {

				/** Use generic page width **/
				$generic_content_full = ot_get_option( '_uncode_' . $post_type . '_layout_width' );
				if ( $generic_content_full === '' ) {
					$main_content_full = ot_get_option( '_uncode_body_full' );
					if ( $main_content_full === '' || $main_content_full === 'off' ) {
						$limit_content_width = ' limit-width';
					}
				} else {
					if ( $generic_content_full === 'limit' ) {
						$generic_custom_width = ot_get_option( '_uncode_' . $post_type . '_layout_width_custom' );
						if ( $generic_custom_width[1] === 'px' ) {
							$generic_custom_width[0] = 12 * round( ( $generic_custom_width[0] ) / 12 );
						}
						if ( is_array( $generic_custom_width ) && ! empty( $generic_custom_width ) ) {
							$page_custom_width = ' style="max-width: ' . implode( "", $generic_custom_width ) . '; margin: auto;"';
						}
					}
				}
			} else {

				/** Override page width **/
				if ( $page_content_full === 'limit' ) {
					$limit_content_width = ' limit-width';
					$page_custom_width   = ( isset( $metabox_data['_uncode_specific_layout_width_custom'][0] ) ) ? unserialize( $metabox_data['_uncode_specific_layout_width_custom'][0] ) : '';
					if ( is_array( $page_custom_width ) && ! empty( $page_custom_width ) && $page_custom_width[0] !== '' ) {
						if ( $page_custom_width[1] === 'px' ) {
							$page_custom_width[0] = 12 * round( ( $page_custom_width[0] ) / 12 );
						}
						$page_custom_width = ' style="max-width: ' . implode( "", $page_custom_width ) . '; margin: auto;"';
					} else {
						$page_custom_width = '';
					}
				}
			}

			$media          = get_post_meta( $post->ID, '_uncode_featured_media', 1 );
			$media_display  = get_post_meta( $post->ID, '_uncode_featured_media_display', 1 );
			$featured_image = get_post_thumbnail_id( $post->ID );
			if ( $featured_image === '' ) {
				$featured_image = $media;
			}

			/** Collect header data **/
			if ( isset( $metabox_data['_uncode_header_type'][0] ) && $metabox_data['_uncode_header_type'][0] !== '' ) {
				$page_header_type = $metabox_data['_uncode_header_type'][0];
				if ( $page_header_type !== 'none' ) {
					$meta_data    = uncode_get_specific_header_data( $metabox_data, $post_type, $featured_image );
					$metabox_data = $meta_data['meta'];
					$show_title   = $meta_data['show_title'];
				}
			} else {
				$page_header_type = ot_get_option( '_uncode_' . $post_type . '_header' );
				if ( $page_header_type !== '' && $page_header_type !== 'none' ) {
					$metabox_data['_uncode_header_type'] = array( $page_header_type );
					$meta_data                           = uncode_get_general_header_data( $metabox_data, $post_type, $featured_image );
					$metabox_data                        = $meta_data['meta'];
					$show_title                          = $meta_data['show_title'];
				}
			}

			/** Get layout info **/
			if ( isset( $metabox_data['_uncode_active_sidebar'][0] ) && $metabox_data['_uncode_active_sidebar'][0] !== '' ) {
				if ( $metabox_data['_uncode_active_sidebar'][0] !== 'off' ) {
					$layout           = ( isset( $metabox_data['_uncode_sidebar_position'][0] ) ) ? $metabox_data['_uncode_sidebar_position'][0] : '';
					$sidebar          = ( isset( $metabox_data['_uncode_sidebar'][0] ) ) ? $metabox_data['_uncode_sidebar'][0] : '';
					$sidebar_size     = ( isset( $metabox_data['_uncode_sidebar_size'][0] ) ) ? $metabox_data['_uncode_sidebar_size'][0] : 4;
					$sidebar_sticky   = ( isset( $metabox_data['_uncode_sidebar_sticky'][0] ) && $metabox_data['_uncode_sidebar_sticky'][0] === 'on' ) ? ' sticky-element sticky-sidebar' : '';
					$sidebar_fill     = ( isset( $metabox_data['_uncode_sidebar_fill'][0] ) ) ? $metabox_data['_uncode_sidebar_fill'][0] : '';
					$sidebar_style    = ( isset( $metabox_data['_uncode_sidebar_style'][0] ) ) ? $metabox_data['_uncode_sidebar_style'][0] : $style;
					$sidebar_bg_color = ( isset( $metabox_data['_uncode_sidebar_bgcolor'][0] ) && $metabox_data['_uncode_sidebar_bgcolor'][0] !== '' ) ? ' style-' . $metabox_data['_uncode_sidebar_bgcolor'][0] . '-bg' : '';
				}
			} else {
				$activate_sidebar = ot_get_option( '_uncode_' . $post_type . '_activate_sidebar' );
				$sidebar_name     = ot_get_option( '_uncode_' . $post_type . '_sidebar' );

				if ( $activate_sidebar !== 'off' && is_active_sidebar( $sidebar_name ) ) {
					$layout = ot_get_option( '_uncode_' . $post_type . '_sidebar_position' );
					if ( $layout === '' ) {
						$layout = 'sidebar_right';
					}
					$sidebar          = ot_get_option( '_uncode_' . $post_type . '_sidebar' );
					$sidebar_style    = ot_get_option( '_uncode_' . $post_type . '_sidebar_style' );
					$sidebar_size     = ot_get_option( '_uncode_' . $post_type . '_sidebar_size' );
					$sidebar_sticky   = ot_get_option( '_uncode_' . $post_type . '_sidebar_sticky' );
					$sidebar_sticky   = ( $sidebar_sticky === 'on' ) ? ' sticky-element sticky-sidebar' : '';
					$sidebar_fill     = ot_get_option( '_uncode_' . $post_type . '_sidebar_fill' );
					$sidebar_bg_color = ot_get_option( '_uncode_' . $post_type . '_sidebar_bgcolor' );
					$sidebar_bg_color = ( $sidebar_bg_color !== '' ) ? ' style-' . $sidebar_bg_color . '-bg' : '';
				}
			}
			if ( $sidebar_style === '' ) {
				$sidebar_style = $style;
			}

			/** Get breadcrumb info **/
			$generic_breadcrumb = ot_get_option( '_uncode_' . $post_type . '_breadcrumb' );
			$page_breadcrumb    = ( isset( $metabox_data['_uncode_specific_breadcrumb'][0] ) ) ? $metabox_data['_uncode_specific_breadcrumb'][0] : '';
			if ( $page_breadcrumb === '' ) {
				$breadcrumb_align = ot_get_option( '_uncode_' . $post_type . '_breadcrumb_align' );
				$show_breadcrumb  = ( $generic_breadcrumb === 'off' ) ? false : true;
			} else {
				$breadcrumb_align = ( isset( $metabox_data['_uncode_specific_breadcrumb_align'][0] ) ) ? $metabox_data['_uncode_specific_breadcrumb_align'][0] : '';
				$show_breadcrumb  = ( $page_breadcrumb === 'off' ) ? false : true;
			}

			/** Get title info **/
			$generic_show_title = ot_get_option( '_uncode_' . $post_type . '_title' );
			$page_show_title    = ( isset( $metabox_data['_uncode_specific_title'][0] ) ) ? $metabox_data['_uncode_specific_title'][0] : '';
			if ( $page_show_title === '' ) {
				$show_title = ( $generic_show_title === 'off' ) ? false : true;
			} else {
				$show_title = ( $page_show_title === 'off' ) ? false : true;
			}

			/** Get media info **/
			$generic_show_media = ot_get_option( '_uncode_' . $post_type . '_media' );
			$page_show_media    = ( isset( $metabox_data['_uncode_specific_media'][0] ) ) ? $metabox_data['_uncode_specific_media'][0] : '';
			if ( $page_show_media === '' ) {
				$show_media = ( $generic_show_media === 'off' ) ? false : true;
			} else {
				$show_media = ( $page_show_media === 'off' ) ? false : true;
			}

			if ( ! $show_media && $featured_image !== '' ) {
				$generic_show_featured_media = ot_get_option( '_uncode_' . $post_type . '_featured_media' );
				$page_show_featured_media    = ( isset( $metabox_data['_uncode_specific_featured_media'][0] ) && $metabox_data['_uncode_specific_featured_media'][0] !== '' ) ? $metabox_data['_uncode_specific_featured_media'][0] : $generic_show_featured_media;

				if ( $page_show_featured_media === 'on' ) {
					$media = $featured_image;
				}
			} else {
				$page_show_featured_media = false;
			}

			$show_media = $page_show_featured_media && $page_show_featured_media !== 'off' ? true : $show_media;


			/** Build header **/
			if ( $page_header_type !== '' && $page_header_type !== 'none' ) {
				$page_header = new unheader( $metabox_data, $post->post_title, $post->post_excerpt );

				$header_html = $page_header->html;
				if ( $header_html !== '' ) {
					echo '<div id="page-header">';
					echo uncode_remove_p_tag( $page_header->html );
					echo '</div>';
				}

				if ( ! empty( $page_header->poster_id ) && $page_header->poster_id !== false && $media !== '' ) {
					$media = $page_header->poster_id;
				}
			}
			echo '<script type="text/javascript">UNCODE.initHeader();</script>';
			/** Build breadcrumb **/

			if ( $show_breadcrumb && ! is_front_page() && ! is_home() ) {
				if ( $breadcrumb_align === '' ) {
					$breadcrumb_align = 'right';
				}
				$breadcrumb_align = ' text-' . $breadcrumb_align;

				$content_breadcrumb = uncode_breadcrumbs();
				$breadcrumb_title   = '<div class="breadcrumb-title h5 text-bold">' . get_the_title() . '</div>';
				echo uncode_get_row_template( $breadcrumb_title . $content_breadcrumb, '', ( $page_custom_width !== '' ? ' limit-width' : $limit_content_width ), $style, ' row-breadcrumb row-breadcrumb-' . $style . $breadcrumb_align, 'half', true, 'half' );
			}

			/** Build title **/

			if ( $show_title ) {
				$title_content .= apply_filters( 'uncode_before_body_title', '' );
				$title_content .= '<div class="post-title-wrapper"><h1 class="post-title">' . get_the_title() . '</h1>';
				$title_content .= uncode_post_info() . '</div>';
				$title_content .= apply_filters( 'uncode_after_body_title', '' );
			}

			/** JetPack related posts **/

			if ( shortcode_exists( 'jetpack-related-posts' ) ) {
				$related_content = do_shortcode( '[jetpack-related-posts]' );
				if ( $related_content !== '' ) {
					if ( ! $with_builder ) {
						$the_content .= $related_content;
					} else {
						$the_content .= uncode_get_row_template( $related_content, $limit_width, $limit_content_width, $style, '', false, true, 'double', $page_custom_width );
					}
				}
			}

			/** Build post after block **/
			$content_after_body        = '';
			$page_content_blocks_after = array(
				'above' => '_pre',
				'below' => ''
			);

			foreach ( $page_content_blocks_after as $order => $pre ) {

				$content_after_body_build = '';

				$page_content_block_after = ( isset( $metabox_data[ '_uncode_specific_content_block_after' . $pre ][0] ) ) ? $metabox_data[ '_uncode_specific_content_block_after' . $pre ][0] : '';
				if ( $page_content_block_after === '' ) {
					$generic_content_block_after = ot_get_option( '_uncode_' . $post_type . '_content_block_after' . $pre );
					$content_block_after         = $generic_content_block_after !== '' ? $generic_content_block_after : false;
				} else {
					$content_block_after = $page_content_block_after !== 'none' ? $page_content_block_after : false;
				}

				if ( $content_block_after !== false ) {
					$content_block_after      = apply_filters( 'wpml_object_id', $content_block_after, 'post' );
					$content_after_body_build = get_post_field( 'post_content', $content_block_after );
					if ( class_exists( 'Vc_Base' ) ) {
						$vc = new Vc_Base();
						$vc->addShortcodesCustomCss( $content_block_after );
					}
					if ( has_shortcode( $content_after_body_build, 'vc_row' ) ) {
						$content_after_body_build = '<div class="post-after row-container">' . $content_after_body_build . '</div>';
					} else {
						$content_after_body_build = '<div class="post-after row-container">' . uncode_get_row_template( $content_after_body_build, $limit_width, $limit_content_width, $style, '', false, true, 'double', $page_custom_width ) . '</div>';
					}
					if ( class_exists( 'RP4WP_Post_Link_Manager' ) ) {
						if ( is_array( RP4WP::get()->settings ) ) {
							$automatic_linking_post_amount = RP4WP::get()->settings[ 'general_' . $post_type ]->get_option( 'automatic_linking_post_amount' );
						} else {
							$automatic_linking_post_amount = RP4WP::get()->settings->get_option( 'automatic_linking_post_amount' );
						}
						$uncode_related    = new RP4WP_Post_Link_Manager();
						$related_posts     = $uncode_related->get_children( $post->ID, false );
						$related_posts_ids = array();
						foreach ( $related_posts as $key => $value ) {
							if ( isset( $value->ID ) ) {
								$related_posts_ids[] = $value->ID;
							}
						}
						$archive_query = '';
						$regex         = '/\[uncode_index(.*?)\]/';
						$regex_attr    = '/(.*?)=\"(.*?)\"/';
						preg_match_all( $regex, $content_after_body_build, $matches, PREG_SET_ORDER );
						foreach ( $matches as $key => $value ) {
							$index_found = false;
							if ( isset( $value[1] ) ) {
								preg_match_all( $regex_attr, trim( $value[1] ), $matches_attr, PREG_SET_ORDER );
								foreach ( $matches_attr as $key_attr => $value_attr ) {
									switch ( trim( $value_attr[1] ) ) {
										case 'auto_query':
											if ( $value_attr[2] === 'yes' ) {
												$index_found = true;
											}
											break;
										case 'loop':
											$archive_query = $value_attr[2];
											break;
									}
								}
							}
							if ( $index_found ) {
								if ( $archive_query === '' ) {
									$archive_query = ' loop="size:10|by_id:' . implode( ',', $related_posts_ids ) . '|post_type:' . $post->post_type . '"';
								} else {
									$parse_query          = uncode_parse_loop_data( $archive_query );
									$parse_query['by_id'] = implode( ',', $related_posts_ids );
									if ( ! isset( $parse_query['order'] ) ) {
										$parse_query['order'] = 'none';
									}
									$archive_query = ' loop="' . uncode_unparse_loop_data( $parse_query ) . '"';
								}
								$value[1] = preg_replace( '#\s(loop)="([^"]+)"#', $archive_query, $value[1], - 1, $index_count );
								if ( $index_count === 0 ) {
									$value[1] .= $archive_query;
								}
								$replacement              = '[uncode_index' . $value[1] . ']';
								$content_after_body_build = str_replace( $value[0], $replacement, $content_after_body_build );
							}
						}
					}
				}

				$content_after_body .= $content_after_body_build;

			}

			/** Build post footer **/

			$page_show_share = ( isset( $metabox_data['_uncode_specific_share'][0] ) ) ? $metabox_data['_uncode_specific_share'][0] : '';
			if ( $page_show_share === '' ) {
				$generic_show_share = ot_get_option( '_uncode_' . $post_type . '_share' );
				$show_share         = ( $generic_show_share === 'off' ) ? false : true;
			} else {
				$show_share = ( $page_show_share === 'off' ) ? false : true;
			}

			if ( $show_share ) {
				$footer_content = '<div class="post-share">
	          						<div class="detail-container margin-auto">
													<div class="share-button share-buttons share-inline only-icon"></div>
												</div>
											</div>';
			}

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
							if ( ! $with_builder ) {
								$main_classes .= ' std-block-padding';
							}
						} else {
							$row_classes .= ' no-top-padding';
							if ( ! $with_builder ) {
								$main_classes .= ' double-top-padding';
							}
						}
					} else {
						$row_classes           .= ' double-top-padding';
						$row_classes           .= ' double-bottom-padding';
						$sidebar_inner_padding .= $sidebar_bg_color . ' single-block-padding';
					}
				} else {
					if ( $with_builder ) {
						if ( $limit_content_width === '' ) {
							$row_classes .= ' col-std-gutter no-top-padding';
							if ( $boxed !== 'on' ) {
								$row_classes .= ' no-h-padding';
							}
						} else {
							$row_classes .= ' col-std-gutter no-top-padding';
						}
						$sidebar_inner_padding .= ' double-top-padding';
					} else {
						$row_classes  .= ' col-std-gutter double-top-padding';
						$main_classes .= ' double-bottom-padding';
					}
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

				if ( $footer_content !== '' ) {
					if ( $limit_content_width === '' ) {
						$footer_content = uncode_get_row_template( $footer_content, $limit_width, $limit_content_width, $style, '', false, true, '' );
					}
					$footer_content = '<div class="post-footer post-footer-' . $style . ' style-' . $style . $footer_classes . '">' . $footer_content . '</div>';
				}

				$the_content = '<div class="post-content style-' . $style . $main_classes . '">' . $the_content . '</div>';

				$main_content = '<div class="col-lg-' . $main_size . '">
											' . $the_content . $content_after_body . $footer_content . '
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
			}

			$the_content = '<div class="post-content un-no-sidebar-layout"' . $page_custom_width . '>';

			/** Build and display navigation html **/
			$navigation_option = ot_get_option( '_uncode_' . $post_type . '_navigation_activate' );
			if ( $navigation_option !== 'off' ) {
				$generic_index = true;
				if ( isset( $metabox_data['_uncode_specific_navigation_index'][0] ) && $metabox_data['_uncode_specific_navigation_index'][0] !== '' ) {
					$navigation_index = $metabox_data['_uncode_specific_navigation_index'][0];
					$generic_index    = false;
				} else {
					$navigation_index = ot_get_option( '_uncode_' . $post_type . '_navigation_index' );
				}
				if ( $navigation_index !== '' ) {
					$navigation_index_label = ot_get_option( '_uncode_' . $post_type . '_navigation_index_label' );
					$navigation_index_link  = get_permalink( $navigation_index );
					$navigation_index_btn   = '<a class="btn btn-link text-default-color" href="' . esc_url( $navigation_index_link ) . '">' . ( $navigation_index_label === '' ? get_the_title( $navigation_index ) : esc_html( $navigation_index_label ) ) . '</a>';
				} else {
					$navigation_index_btn = '';
				}
				$navigation_nextprev_title = ot_get_option( '_uncode_' . $post_type . '_navigation_nextprev_title' );
				$navigation                = uncode_post_navigation( $navigation_index_btn, $navigation_nextprev_title, $navigation_index, $generic_index );
				if ( $page_custom_width !== '' ) {
					$limit_content_width = ' limit-width';
				}
				if ( ! empty( $navigation ) && $navigation !== '' ) {
					$navigation_content = uncode_get_row_template( $navigation, '', $limit_content_width, $style, ' row-navigation row-navigation-' . $style, true, true, true );
				}
			}

			/** Display post html **/
			echo '<article id="post-' . get_the_ID() . '" class="' . implode( ' ', get_post_class( 'page-body' . $bg_color ) ) . '">
          <div class="post-wrapper">
          	<div class="post-body">';

			ob_start();
		}
		break;
	default:
		echo apply_filters( 'sm_templates_wrapper_start', '<div class="wrap"><div id="primary" class="content-area"><main id="main" class="site-main wpfc-sermon-container ' . $additional_classes . '">' );
		break;
}
