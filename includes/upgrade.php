<?php
/*
 * Sermon Manager Upgrade Functions
 */
 class Sermon_Manager_Upgrade{

 	 /**
 	 * Construct.
 	 */
 	 function __construct() {
 		 add_action('admin_init', array($this, 'wpfc_sermon_update_warning') );
 	 }
		function wpfc_plugin_get_version() {
			$sermon_plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/sermon-manager-for-wordpress/sermons.php' );
			$version = $sermon_plugin_data['Version'];
			return $version;
		}

		function wpfc_sermon_update_warning() {
			$sermon_settings = get_option('wpfc_options');
			$sermon_version = isset($sermon_settings['version']) ? $sermon_settings['version'] : '';
				if( $sermon_version < '1.8' ):
          add_action( 'admin_notices', array( $this, 'wpfc_sermon_warning_html') );
				endif;
		}

		function wpfc_sermon_warning_html() {
			?>
			<div id='wpfc-sermon-update-warning' class='updated fade'>
				<?php $wpfc_settings_url = admin_url( 'edit.php?post_type=wpfc_sermon&page=sermon-manager-for-wordpress/includes/options.php'); ?>
				<p><strong><?php _e('Sermon Manager is almost ready.', 'sermon-manager');?></strong> <?php _e('You must', 'sermon-manager');?> <a href="<?php echo $wpfc_settings_url; ?>"><?php _e('resave your settings for it to function correctly!!!', 'sermon-manager');?></a></p>
			</div>
			<?php
		}

		function wpfc_sermon_update() {

			$sermon_settings = get_option('wpfc_options');
			$sermon_version = isset($sermon_settings['version']) ? $sermon_settings['version'] : '';

			$args = array(
			  'post_type'       => 'wpfc_sermon',
			  'posts_per_page'  => '-0'
			);
			$wpfc_sermon_update_query = new WP_Query($args);

			while ($wpfc_sermon_update_query->have_posts()) : $wpfc_sermon_update_query->the_post();
				global $post;
				if( empty($sermon_version) ):
					$service_type = get_post_meta($post->ID, 'service_type', 'true');
					if( !has_term('wpfc_service_type') ){
						wp_set_object_terms($post->ID, $service_type, 'wpfc_service_type');
					}

					$current = get_post_meta($post->ID, 'sermon_audio', 'true');
					$currentsize = get_post_meta($post->ID, '_wpfc_sermon_size', 'true');

					// only grab if different (getting data from dropbox can be a bit slow)
					if ( empty($currentsize) ) {

						// get file data
						$size =  wpfc_get_filesize( $current );
						$duration = wpfc_mp3_duration( $current );

						// store in hidden custom fields
						update_post_meta( $post->ID, '_wpfc_sermon_duration', $duration );
						update_post_meta( $post->ID, '_wpfc_sermon_size', $size );

					}
					//Alter the options array appropriately
					$sermon_settings['version'] = $this->wpfc_plugin_get_version();

					//Update entire array
					update_option('wpfc_options', $sermon_settings);

				endif;
				if( $sermon_version < '1.8' ):

					$current = get_post_meta($post->ID, 'sermon_audio', 'true');
					$currentsize = get_post_meta($post->ID, '_wpfc_sermon_size', 'true');

					// only grab if different (getting data from dropbox can be a bit slow)
					if ( empty($currentsize) ) {

						// get file data
						$size =  wpfc_get_filesize( $current );
						$duration = wpfc_mp3_duration( $current );

						// store in hidden custom fields
						update_post_meta( $post->ID, '_wpfc_sermon_duration', $duration );
						update_post_meta( $post->ID, '_wpfc_sermon_size', $size );

					}
					//Alter the options array appropriately
					$sermon_settings['version'] = $this->wpfc_plugin_get_version();

					//Update entire array
					update_option('wpfc_options', $sermon_settings);


				endif;
			endwhile;
			wp_reset_query();
		}
}
$Sermon_Manager_Upgrade = new Sermon_Manager_Upgrade();
?>
