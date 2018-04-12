<?php
/**
 * Defines CPT and CPT related stuff.
 *
 * @package SM/Core
 */

defined( 'ABSPATH' ) or die;

/**
 * SM_Admin_Post_Types Class
 *
 * Handles the edit posts views and some functionality on the edit post screen for Sermon Manager post types
 *
 * @since 2.9
 */
class SM_Admin_Post_Types {
	/**
	 * SM_Admin_Post_Types constructor.
	 */
	public function __construct() {
		add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );

		// WP List table columns. Defined here so they are always available for events such as inline editing.
		add_filter( 'manage_wpfc_sermon_posts_columns', array( $this, 'sermon_columns' ) );
		add_action( 'manage_wpfc_sermon_posts_custom_column', array( $this, 'render_sermon_columns' ), 2 );
		add_filter( 'manage_edit-wpfc_sermon_sortable_columns', array( $this, 'sermon_sortable_columns' ) );

		add_filter( 'list_table_primary_column', array( $this, 'list_table_primary_column' ), 10, 2 );
		add_filter( 'post_row_actions', array( $this, 'row_actions' ), 100, 2 );

		// Filters.
		add_action( 'restrict_manage_posts', array( $this, 'restrict_manage_posts' ) );
		add_filter( 'request', array( $this, 'request_query' ) );
		add_filter( 'parse_query', array( $this, 'sermon_filters_query' ) );

		// Edit post screens.
		add_filter( 'enter_title_here', array( $this, 'enter_title_here' ), 1, 2 );

		// include_once 'class-sm-admin-meta-boxes.php'; - @todo.
		do_action( 'after_sm_admin_post_types' );
	}

	/**
	 * Change messages when a post type is updated.
	 *
	 * @param array $messages Existing messages.
	 *
	 * @return array
	 */
	public function post_updated_messages( $messages ) {
		global $post_ID;

		$messages['wpfc_sermon'] = array(
			0  => '', // Unused. Messages start at index 1.
			// translators: %s: The URL to the sermon.
			1  => wp_sprintf( esc_html__( 'Sermon updated. %s', 'sermon-manager-for-wordpress' ), '<a href="' . esc_url( get_permalink( $post_ID ) ) . '">' . esc_html__( 'View sermon', 'sermon-manager-for-wordpress' ) . '</a>' ),
			2  => esc_html__( 'Custom field updated.', 'sermon-manager-for-wordpress' ),
			3  => esc_html__( 'Custom field deleted.', 'sermon-manager-for-wordpress' ),
			4  => esc_html__( 'Sermon updated.', 'sermon-manager-for-wordpress' ),
			// translators: %s: Date and time of the revision.
			5  => isset( $_GET['revision'] ) ? wp_sprintf( esc_html__( 'Sermon restored to revision from %s', 'sermon-manager-for-wordpress' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			// translators: %s: The URL to the sermon.
			6  => wp_sprintf( esc_html__( 'Sermon published. %s', 'sermon-manager-for-wordpress' ), '<a href="' . esc_url( get_permalink( $post_ID ) ) . '">' . esc_html__( 'View sermon', 'sermon-manager-for-wordpress' ) . '</a>' ),
			7  => esc_html__( 'Sermon saved.', 'sermon-manager-for-wordpress' ),
			// translators: %s: The URL to the sermon.
			8  => wp_sprintf( esc_html__( 'Sermon submitted. %s', 'sermon-manager-for-wordpress' ), '<a target="_blank" href="' . esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) . '">' . esc_html__( 'Preview sermon', 'sermon-manager-for-wordpress' ) . '</a>' ),
			// translators: %1$s: The date and time. %2$s: The preview sermon URL.
			9  => wp_sprintf( esc_html__( 'Sermon scheduled for: %1$s. %2$s', 'sermon-manager-for-wordpress' ),
				// translators: %1$s: Date. %2$s: Time.
				'<strong>' . wp_sprintf( esc_html__( '%1$s at %2$s', 'sermon-manager-for-wordpress' ), get_post_time( get_option( 'date_format' ), false, null, true ), get_post_time( get_option( 'time_format' ), false, null, true ) ) . '</strong>',
				// translators: %s: The preview sermon URL.
				'<a target="_blank" href="' . esc_url( get_permalink( $post_ID ) ) . '">' . esc_html__( 'Preview sermon', 'sermon-manager-for-wordpress' ) . '</a>'
			),
			// translators: %s The URL to the sermon.
			10 => wp_sprintf( esc_html__( 'Sermon draft updated. %s', 'sermon-manager-for-wordpress' ), '<a target="_blank" href="' . esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) . '">' . esc_html__( 'View sermon', 'sermon-manager-for-wordpress' ) . '</a>' ),
		);

		return $messages;
	}

	/**
	 * Define custom columns for sermons.
	 *
	 * @param array $existing_columns Existing columns.
	 *
	 * @return array
	 */
	public function sermon_columns( $existing_columns ) {
		if ( empty( $existing_columns ) && ! is_array( $existing_columns ) ) {
			$existing_columns = array();
		}

		$columns             = array();
		$columns['cb']       = '<input type="checkbox" />';
		$columns['title']    = __( 'Sermon Title', 'sermon-manager-for-wordpress' );
		$columns['preacher'] = ucwords( \SermonManager::getOption( 'preacher_label' ) ) ?: __( 'Preacher', 'sermon-manager-for-wordpress' );
		$columns['series']   = __( 'Sermon Series', 'sermon-manager-for-wordpress' );
		$columns['topics']   = __( 'Topics', 'sermon-manager-for-wordpress' );
		$columns['views']    = __( 'Views', 'sermon-manager-for-wordpress' );
		$columns['comments'] = $existing_columns['comments'];
		$columns['preached'] = __( 'Date' );

		unset( $existing_columns['date'] );

		return array_merge( $columns, $existing_columns );
	}

	/**
	 * Output custom columns for sermons.
	 *
	 * @param string $column The column to render.
	 */
	public function render_sermon_columns( $column ) {
		global $post;

		if ( empty( $post->ID ) ) {
			return;
		}

		switch ( $column ) {
			case 'preacher':
				$data = get_the_term_list( $post->ID, 'wpfc_preacher', '', ', ', '' );
				break;
			case 'series':
				$data = get_the_term_list( $post->ID, 'wpfc_sermon_series', '', ', ', '' );
				break;
			case 'topics':
				$data = get_the_term_list( $post->ID, 'wpfc_sermon_topics', '', ', ', '' );
				break;
			case 'views':
				$data = wpfc_entry_views_get( array( 'post_id' => $post->ID ) );
				break;
			case 'preached':
				/**
				 * Modified from code in wp-admin/includes/class-wp-posts-list-table.php
				 */
				global $mode;

				$data = '';

				if ( '0000-00-00 00:00:00' === $post->post_date ) {
					$t_time    = __( 'Unpublished' );
					$h_time    = __( 'Unpublished' );
					$time_diff = 0;
				} else {
					$t_time = sm_get_the_date( __( 'Y/m/d g:i:s a' ) );
					$m_time = sm_get_the_date( 'Y-m-d H:i:s' );
					$time   = sm_get_the_date( 'U' );

					$time_diff = time() - $time;

					if ( $time_diff > 0 && $time_diff < DAY_IN_SECONDS ) {
						// translators: %s: The time. Such as "12 hours".
						$h_time = sprintf( __( '%s ago' ), human_time_diff( $time ) );
					} else {
						$h_time = mysql2date( __( 'Y/m/d' ), $m_time );
					}
				}

				if ( 'publish' === $post->post_status ) {
					$status = __( 'Published' );
				} elseif ( 'future' === $post->post_status ) {
					if ( $time_diff > 0 ) {
						$status = '<strong class="error-message">' . __( 'Missed schedule' ) . '</strong>';
					} else {
						$status = __( 'Scheduled' );
					}
				} else {
					$status = __( 'Last Modified' );
				}

				if ( $status ) {
					$data .= $status . '<br />';
				}

				if ( 'excerpt' === $mode ) {
					/**
					 * Filters the published time of the post.
					 *
					 * If `$mode` equals 'excerpt', the published time and date are both displayed.
					 * If `$mode` equals 'list' (default), the publish date is displayed, with the
					 * time and date together available as an abbreviation definition.
					 *
					 * @since 2.9
					 *
					 * @param string  $t_time      The published time.
					 * @param WP_Post $post        Post object.
					 * @param string  $column_name The column name.
					 * @param string  $mode        The list display mode ('excerpt' or 'list').
					 */
					$data .= apply_filters( 'wpfc_sermon_preached_column_time', $t_time, $post, 'date', $mode );
				} else {

					/** This filter is documented above */
					$data .= '<abbr title="' . $t_time . '">' . apply_filters( 'wpfc_sermon_preached_column_time', $h_time, $post, 'date', $mode ) . '</abbr>';
				}

				break;
			default:
				$data = '';
				break;
		}

		if ( $data instanceof WP_Error ) {
			$data = __( 'Error' );
		}

		echo $data;
	}

	/**
	 * Make columns sortable
	 *
	 * @param array $columns The existing columns.
	 *
	 * @return array
	 */
	public function sermon_sortable_columns( $columns ) {
		$custom = array(
			'title'    => 'title',
			'preached' => 'preached',
			'views'    => 'views',
		);

		return wp_parse_args( $custom, $columns );
	}

	/**
	 * Set list table primary column
	 * Support for WordPress 4.3.
	 *
	 * @param string $default   Existing primary column.
	 * @param string $screen_id Current screen ID.
	 *
	 * @return string
	 */
	public function list_table_primary_column( $default, $screen_id ) {
		if ( 'edit-wpfc_sermon' === $screen_id ) {
			return 'title';
		}

		return $default;
	}

	/**
	 * Set row actions for sermons
	 *
	 * @param  array   $actions The existing actions.
	 * @param  WP_Post $post    Sermon or other post instance.
	 *
	 * @return array
	 */
	public function row_actions( $actions, $post ) {
		if ( 'wpfc_sermon' === $post->post_type ) {
			return array_merge( array( 'id' => 'ID: ' . $post->ID ), $actions );
		}

		return $actions;
	}

	/**
	 * Filters and sorting handler.
	 *
	 * @param  array $vars Current filtering arguments.
	 *
	 * @return array
	 */
	public function request_query( $vars ) {
		global $typenow;

		if ( 'wpfc_sermon' === $typenow ) {
			// Sorting.
			if ( isset( $vars['orderby'] ) ) {
				switch ( $vars['orderby'] ) {
					case 'preached':
						$vars = array_merge( $vars, array(
							'meta_key'       => 'sermon_date',
							'orderby'        => 'meta_value_num',
							'meta_value_num' => time(),
							'meta_compare'   => '<=',
						) );
						break;

					case 'views':
						$vars = array_merge( $vars, array(
							'meta_key' => 'Views',
							'orderby'  => 'meta_value_num',
						) );
						break;
				}
			}

			if ( isset( $vars['wpfc_service_type'] ) && trim( $vars['wpfc_service_type'] ) === '' ) {
				unset( $vars['wpfc_service_type'] );
			}
		}

		return $vars;
	}

	/**
	 * Change title boxes in admin.
	 *
	 * @param  string $text The title.
	 * @param  object $post The post.
	 *
	 * @return string
	 */
	public function enter_title_here( $text, $post ) {
		if ( 'wpfc_sermon' === $post->post_type ) {
			$text = __( 'Sermon title', 'sermon-manager-for-wordpress' );
		}

		return $text;
	}

	/**
	 * Filter the sermons in admin based on options
	 *
	 * @param mixed $query The query.
	 */
	public function sermon_filters_query( $query ) {
		global $typenow;

		if ( 'wpfc_sermon' == $typenow ) {
			if ( isset( $query->query_vars['wpfc_service_type'] ) ) {
				$query->query_vars['tax_query'] = array(
					array(
						'taxonomy' => 'wpfc_service_type',
						'field'    => 'slug',
						'terms'    => $query->query_vars['wpfc_service_type'],
					)
				);
			}
		}
	}

	/**
	 * Filters for post types.
	 */
	public function restrict_manage_posts() {
		global $typenow;

		if ( 'wpfc_sermon' === $typenow ) {
			$this->sermon_filters();
		}
	}

	/**
	 * Show a service type filter box.
	 */
	public function sermon_filters() {
		global $wp_query;

		// Type filtering.
		$terms  = get_terms( 'wpfc_service_type' );
		$output = '';

		$output .= '<select name="wpfc_service_type" id="dropdown_wpfc_service_type">';
		$output .= '<option value="">' . __( 'Filter by Service Type', 'sermon-manager-for-wordpress' ) . '</option>';

		foreach ( $terms as $term ) {
			$output .= '<option value="' . sanitize_title( $term->name ) . '" ';

			if ( isset( $wp_query->query['wpfc_service_type'] ) ) {
				$output .= selected( $term->slug, $wp_query->query['wpfc_service_type'], false );
			}

			$output .= '>';

			$output .= ucfirst( $term->name );

			$output .= '</option>';
		}

		$output .= '</select>';

		echo apply_filters( 'sm_sermon_filters', $output );
	}
}

new SM_Admin_Post_Types();
