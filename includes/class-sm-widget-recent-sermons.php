<?php
/**
 * Loads widgets.
 *
 * @package SM/Core/Widgets
 */

defined( 'ABSPATH' ) or die;

/**
 * Recent Sermons Widget.
 */
class SM_Widget_Recent_Sermons extends WP_Widget {
	/**
	 * Constructor.
	 */
	public function __construct() {
		$widget_ops = array(
			'classname'   => 'widget_recent_sermons',
			'description' => __( 'The most recent sermons on your site', 'sermon-manager-for-wordpress' ),
		);
		parent::__construct( 'recent-sermons', __( 'Recent Sermons', 'sermon-manager-for-wordpress' ), $widget_ops );
		$this->alt_option_name = 'widget_recent_entries';

		add_action( 'save_post', array( $this, 'flush_widget_cache' ) );
		add_action( 'deleted_post', array( $this, 'flush_widget_cache' ) );
		add_action( 'switch_theme', array( $this, 'flush_widget_cache' ) );
	}

	/**
	 * Renders the widget.
	 *
	 * @param array $args     Render arguments.
	 * @param array $instance Widget instance.
	 */
	function widget( $args, $instance ) {
		// Enqueue scripts and styles.
		if ( ! defined( 'SM_ENQUEUE_SCRIPTS_STYLES' ) ) {
			define( 'SM_ENQUEUE_SCRIPTS_STYLES', true );
		}

		$cache = wp_cache_get( 'widget_recent_sermons', 'widget' );

		if ( ! is_array( $cache ) ) {
			$cache = array();
		}

		/**
		 * Filter: Allows to override the cache.
		 *
		 * @since 2.13.0
		 */
		if ( isset( $cache[ $args['widget_id'] ] ) && ! apply_filters( 'sm_recent_sermons_widget_override_cache', false ) ) {
			echo $cache[ $args['widget_id'] ];

			return;
		}

		if ( SM_OB_ENABLED ) {
			ob_start();

			$title         = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Recent Sermons', 'sermon-manager-for-wordpress' ) : $instance['title'], $instance, $this->id_base );
			$number        = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
			$before_widget = isset( $instance['before_widget'] ) ? wp_kses_post( $instance['before_widget'] ) : '';
			$after_widget  = isset( $instance['after_widget'] ) ? wp_kses_post( $instance['after_widget'] ) : '';

			$r = new WP_Query( array(
				'post_type'           => 'wpfc_sermon',
				'posts_per_page'      => $number,
				'no_found_rows'       => true,
				'post_status'         => 'publish',
				'ignore_sticky_posts' => true,
				'meta_key'            => 'sermon_date',
				'meta_value_num'      => time(),
				'meta_compare'        => '<=',
				'orderby'             => 'meta_value_num',
				'order'               => 'desc',
			) );
			if ( $r->have_posts() ) {
				?>
				<?php echo $args['before_widget']; ?>
				<?php if ( $title ) : ?>
					<?php echo $args['before_title'] . $title . $args['after_title']; ?>
				<?php endif; ?>
				<?php if ( $before_widget ) : ?>
					<div class="sm-before-widget">
						<?php echo $before_widget; ?>
					</div>
				<?php endif; ?>
				<ul>
					<?php while ( $r->have_posts() ) : ?>
						<?php
						$r->the_post();
						global $post;

						$terms          = get_the_terms( $post->ID, 'wpfc_preacher' );
						$preacher_links = array();
						if ( $terms ) {
							foreach ( $terms as $term ) {
								$preacher_links[] = $term->name;
							}
						}
						?>
						<li>
							<div class="widget_recent_sermons_meta">
								<a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( get_the_title() ); ?>"
										class="title-link">
									<span class="dashicons dashicons-microphone"></span>
									<span class="title">
									<?php echo get_the_title(); ?>
								</span>
								</a>
								<div class="meta">
									<?php if ( $preacher_links ) : ?>
										<span class="preachers"><?php echo join( ', ', $preacher_links ); ?></span><span
												class="separator">, </span>
									<?php endif; ?>
									<span class="date">
									<?php echo sm_get_the_date(); ?>
								</span>

									<?php if ( \SermonManager::getOption( 'widget_show_key_verse' ) ) : ?>
										<span class="bible-passage"><br><?php echo __( 'Bible Text: ', 'sermon-manager-for-wordpress' ), get_wpfc_sermon_meta( 'bible_passage' ); ?></span>
									<?php endif; ?>
								</div>
							</div>
						</li>
					<?php endwhile; ?>
				</ul>
				<?php if ( $after_widget ) : ?>
					<div class="sm-after-widget">
						<?php echo $after_widget; ?>
					</div>
				<?php endif; ?>
				<?php echo $args['after_widget']; ?>
				<?php
				wp_reset_postdata();
			}
			$output = ob_get_flush();
		} else {
			$output = '';
		}

		/**
		 * Allows to filter widget contents.
		 *
		 * @param string   $output The current HTML.
		 * @param array    $args   Output arguments.
		 * @param WP_Query $r      Sermons.
		 *
		 * @since 2.13.0
		 */
		$output = apply_filters( 'sm_recent_sermons_widget_content', $output, $args, $r );

		$cache[ $args['widget_id'] ] = $output;
		wp_cache_set( 'widget_recent_sermons', $cache, 'widget' );
	}

	/**
	 * Updates widget settings.
	 *
	 * @param array $new_instance New widget instance.
	 * @param array $old_instance Old widget instance.
	 *
	 * @return array
	 */
	function update( $new_instance, $old_instance ) {
		$instance                  = $old_instance;
		$instance['title']         = strip_tags( $new_instance['title'] );
		$instance['number']        = (int) $new_instance['number'];
		$instance['before_widget'] = wp_kses_post( $new_instance['before_widget'] );
		$instance['after_widget']  = wp_kses_post( $new_instance['after_widget'] );
		$this->flush_widget_cache();

		$all_options = wp_cache_get( 'alloptions', 'options' );
		if ( isset( $all_options['widget_recent_entries'] ) ) {
			delete_option( 'widget_recent_entries' );
		}

		return $instance;
	}

	/**
	 * Clears widget cache.
	 */
	function flush_widget_cache() {
		wp_cache_delete( 'widget_recent_sermons', 'widget' );
	}

	/**
	 * Outputs widget settings.
	 *
	 * @param array $instance Widget instance.
	 *
	 * @return void
	 */
	function form( $instance ) {
		$title         = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$number        = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
		$before_widget = isset( $instance['before_widget'] ) ? wp_kses_post( $instance['before_widget'] ) : '';
		$after_widget  = isset( $instance['after_widget'] ) ? wp_kses_post( $instance['after_widget'] ) : '';
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title:', 'sermon-manager-for-wordpress' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
					name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>"/>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php esc_html_e( 'Number of sermons to show:', 'sermon-manager-for-wordpress' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'number' ); ?>"
					name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>"
					size="3"/>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'before_widget' ); ?>"><?php esc_html_e( 'HTML to show before the widget:', 'sermon-manager-for-wordpress' ); ?></label>
			<textarea id="<?php echo $this->get_field_id( 'before_widget' ); ?>"
					name="<?php echo $this->get_field_name( 'before_widget' ); ?>"><?php echo $before_widget; ?></textarea>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'after_widget' ); ?>"><?php esc_html_e( 'HTML to show after the widget:', 'sermon-manager-for-wordpress' ); ?></label>
			<textarea id="<?php echo $this->get_field_id( 'after_widget' ); ?>"
					name="<?php echo $this->get_field_name( 'after_widget' ); ?>"><?php echo $after_widget; ?></textarea>
		</p>
		<?php
	}
}

add_action( 'widgets_init', function () {
	register_widget( 'SM_Widget_Recent_Sermons' );
} );
