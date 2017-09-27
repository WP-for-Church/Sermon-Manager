<?php
defined( 'ABSPATH' ) or die; // exit if accessed directly

/**
 * Recent Sermons Widget
 */
class WP4C_Recent_Sermons extends WP_Widget {

	public function __construct() {
		$widget_ops = array(
			'classname'   => 'widget_recent_sermons',
			'description' => __( 'The most recent sermons on your site', 'sermon-manager-for-wordpress' )
		);
		parent::__construct( 'recent-sermons', __( 'Recent Sermons', 'sermon-manager-for-wordpress' ), $widget_ops );
		$this->alt_option_name = 'widget_recent_entries';

		add_action( 'save_post', array( $this, 'flush_widget_cache' ) );
		add_action( 'deleted_post', array( $this, 'flush_widget_cache' ) );
		add_action( 'switch_theme', array( $this, 'flush_widget_cache' ) );
	}

	function widget( $args, $instance ) {
		// enqueue scripts and styles
		if ( ! defined( 'SM_ENQUEUE_SCRIPTS_STYLES' ) ) {
			define( 'SM_ENQUEUE_SCRIPTS_STYLES', true );
		}

		$cache = wp_cache_get( 'widget_recent_sermons', 'widget' );

		if ( ! is_array( $cache ) ) {
			$cache = array();
		}

		if ( isset( $cache[ $args['widget_id'] ] ) ) {
			echo $cache[ $args['widget_id'] ];

			return;
		}

		ob_start();
		extract( $args );

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Recent Sermons', 'sermon-manager-for-wordpress' ) : $instance['title'], $instance, $this->id_base );
		if ( ! $number = absint( $instance['number'] ) ) {
			$number = 10;
		}

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
		) );
		if ( $r->have_posts() ) :
			?>
			<?php echo $before_widget; ?>
			<?php if ( $title ) {
			echo $before_title . $title . $after_title;
		} ?>
            <ul>
				<?php while ( $r->have_posts() ) : $r->the_post(); ?>
					<?php global $post; ?>

                    <li>
                        <div class="widget_recent_sermons_meta">
                            <a href="<?php the_permalink() ?>"
                               title="<?php echo esc_attr( get_the_title() ? get_the_title() : get_the_ID() ); ?>">
                                <span class="dashicons dashicons-microphone"></span>
								<?php if ( get_the_title() ) {
									the_title();
								} else {
									the_ID();
								} ?></a>
                            <span class="meta">
					<?php
					if ( $terms = get_the_terms( $post->ID, 'wpfc_preacher' ) ) {
						$preacher_links = array();

						foreach ( $terms as $term ) {
							$preacher_links[] = $term->name;
						}

						echo '<span class="preachers">', join( ", ", $preacher_links ), '</span>';

						echo '<span class="separator">, </span>';
					}

					echo '<span class="date-preached">', sm_get_the_date(), '</span>';

					if ( \SermonManager::getOption( 'widget_show_key_verse' ) ) {
						echo '<span class="bible-passage"><br>', __( 'Bible Text: ', 'sermon-manager-for-wordpress' ), get_wpfc_sermon_meta( 'bible_passage' ), '</span>';
					}
					?>
				</span>
                        </div>
                    </li>
				<?php endwhile; ?>
            </ul>
			<?php echo $after_widget;
			// Reset the global $the_post as this query will have stomped on it
			wp_reset_postdata();
		endif;
		$cache[ $args['widget_id'] ] = ob_get_flush();
		wp_cache_set( 'widget_recent_sermons', $cache, 'widget' );
	}

	function update( $new_instance, $old_instance ) {
		$instance           = $old_instance;
		$instance['title']  = strip_tags( $new_instance['title'] );
		$instance['number'] = (int) $new_instance['number'];
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset( $alloptions['widget_recent_entries'] ) ) {
			delete_option( 'widget_recent_entries' );
		}

		return $instance;
	}

	function flush_widget_cache() {
		wp_cache_delete( 'widget_recent_sermons', 'widget' );
	}

	function form( $instance ) {
		$title  = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$number = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
		?>
        <p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
                   name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>"/>
        </p>

        <p><label
                    for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of sermons to show:' ); ?></label>
            <input id="<?php echo $this->get_field_id( 'number' ); ?>"
                   name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>"
                   size="3"/></p>
	<?php }

}

function register_recent_sermons() {
	register_widget( 'WP4C_Recent_Sermons' );
}

add_action( 'widgets_init', 'register_recent_sermons' );

?>
