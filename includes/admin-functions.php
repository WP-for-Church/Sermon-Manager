<?php
/**
 * Change Sermons Upload Directory
 *
 * Hooks the sm_set_upload_dir filter when appropriate. This function works by
 * hooking on the WordPress Media Uploader and moving the uploading files that
 * are used for SM to an sermons directory under wp-content/uploads/ therefore,
 * the new directory is wp-content/uploads/sermons/{year}/{month}.
 *
 * @since 1.9
 * @global $pagenow
 * @return void
 */

class Sermon_Manager_Admin_Functions{

 	/**
 	* Construct.
 	*/
 	function __construct() {
 		// Change upload_dir for Sermons
 		add_action( 'admin_init', array( $this,'sm_change_downloads_upload_dir' ), 999 );
		// Podcast audio validation
		add_filter('wpfc_validate_file', array( $this,'wpfc_sermon_audio_validate' ), 10, 3);
		// Remove Service Type box
		add_action( 'admin_menu', array( $this,'remove_service_type_taxonomy' ) );
		// Sermon updates messages
		add_filter('post_updated_messages', array( $this,'wpfc_sermon_updated_messages' ) );
		// Create custom columns when listing sermon details in the Admin
		add_action('manage_wpfc_sermon_posts_custom_column', array( $this,'wpfc_sermon_columns' ) );
		add_filter('manage_edit-wpfc_sermon_columns', array( $this,'wpfc_sermon_edit_columns' ) );
		add_filter( 'manage_edit-wpfc_sermon_sortable_columns', array( $this,'wpfc_column_register_sortable' ) );
		// Run on edit.php
		add_action( 'load-edit.php', array( $this,'wpfc_column_orderby_function' ) );
		// Taxonomy Descriptions
		add_action( 'admin_init', array( $this,'wpfc_taxonomy_short_description_actions' ) );
		// Dashboard additions
		$wp_version = isset($wp_version) ? $wp_version : '';
		if ( preg_match('/3.(6|7)/', $wp_version) ) {
			add_action('right_now_content_table_end', array( $this, 'wpfc_right_now' ) );
		}
		else {
			add_action('dashboard_glance_items', array( $this, 'wpfc_dashboard' ) );
		}

 	}
	function sm_change_downloads_upload_dir() {
		global $pagenow;

		if ( ! empty( $_REQUEST['post_id'] ) && ( 'async-upload.php' == $pagenow || 'media-upload.php' == $pagenow ) ) {
			if ( 'wpfc_sermon' == get_post_type( $_REQUEST['post_id'] ) ) {
				add_filter( 'upload_dir', array($this, 'sm_set_upload_dir') );
			}
		}
	}

	/**
	 * Set Upload Directory
	 *
	 * Sets the upload dir to sermons. This function is called from
	 * edd_change_downloads_upload_dir()
	 *
	 * @since 1.9
	 * @return array Upload directory information
	 */
	function sm_set_upload_dir( $upload ) {

		// Override the year / month being based on the post publication date, if year/month organization is enabled
		if ( get_option( 'uploads_use_yearmonth_folders' ) ) {
			// Generate the yearly and monthly dirs
			$time = current_time( 'mysql' );
			$y = substr( $time, 0, 4 );
			$m = substr( $time, 5, 2 );
			$upload['subdir'] = "/$y/$m";
		}

		$upload['subdir'] = '/sermons' . $upload['subdir'];
		$upload['path']   = $upload['basedir'] . $upload['subdir'];
		$upload['url']    = $upload['baseurl'] . $upload['subdir'];
		return $upload;
	}

	/**
	 * Enclose audio file for podcast on save and store in custom fields.
	 * Using meta boxes validation filter.
	 * Added by T Hyde 9 Oct 2013; Updated by Jack 4/4/14
	 *
	 * @param $new
	 * @param $post_id
	 * @param $field
	 * @return $new unchanged
	 */
	function wpfc_sermon_audio_validate( $new, $post_id, $field ) {
	    // only for sermon audio
	    if ( $field['id'] != 'sermon_audio' )
	        return $new;
	    $audio = get_post_meta($post_id, 'sermon_audio', 'true');
		// Stop if PowerPress plugin is active
		// Solves conflict regarding enclosure field: http://wordpress.org/support/topic/breaks-blubrry-powerpress-plugin?replies=6
		if ( defined( 'POWERPRESS_VERSION' ) ) {
			return false;
		}
		// Populate enclosure field with URL, length and format, if valid URL found
		// This will set the length of the enclosure automatically
		do_enclose( $audio, $post_id );
		// Set duration as post meta
		$current = get_post_meta($post_id, 'sermon_audio', 'true');
	    $currentduration = get_post_meta($post_id, '_wpfc_sermon_duration', 'true');
	    // only grab if different (getting data from dropbox can be a bit slow)
	    if ( $new != '' && ( $new != $current || empty($currentduration) ) ) {
	        // get file data
					$Sermon_Manager_Podcast_Functions = new Sermon_Manager_Podcast_Functions();
	        $duration = $Sermon_Manager_Podcast_Functions->wpfc_mp3_duration( $new );
	        // store in hidden custom fields
	        update_post_meta( $post_id, '_wpfc_sermon_duration', $duration );
	    } elseif ($new == '') {
	        // clean up if file removed
	        delete_post_meta( $post_id, '_wpfc_sermon_duration');
	    }
	    return $new;
	}

	//Remove service type box (since we already have a method for selecting it)
	function remove_service_type_taxonomy() {
		$custom_taxonomy_slug = 'wpfc_service_type';
		$custom_post_type = 'wpfc_sermon';
		remove_meta_box('tagsdiv-wpfc_service_type', 'wpfc_sermon', 'side' );
	}

	//add filter to insure the text Sermon, or sermon, is displayed when user updates a sermon
	function wpfc_sermon_updated_messages( $messages ) {
	  global $post, $post_ID;

	  $messages['wpfc_sermon'] = array(
	    0 => '', // Unused. Messages start at index 1.
	    1 => sprintf( __('Sermon updated. <a href="%s">View sermon</a>', 'sermon-manager'), esc_url( get_permalink($post_ID) ) ),
	    2 => __('Custom field updated.', 'sermon-manager'),
	    3 => __('Custom field deleted.', 'sermon-manager'),
	    4 => __('Sermon updated.', 'sermon-manager'),
	    /* translators: %s: date and time of the revision */
	    5 => isset($_GET['revision']) ? sprintf( __('Sermon restored to revision from %s', 'sermon-manager'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
	    6 => sprintf( __('Sermon published. <a href="%s">View sermon</a>', 'sermon-manager'), esc_url( get_permalink($post_ID) ) ),
	    7 => __('Sermon saved.', 'sermon-manager'),
	    8 => sprintf( __('Sermon submitted. <a target="_blank" href="%s">Preview sermon</a>', 'sermon-manager'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
	    9 => sprintf( __('Sermon scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview sermon</a>', 'sermon-manager'),
	      // translators: Publish box date format, see http://php.net/date
	      date_i18n( __( 'M j, Y @ G:i', 'sermon-manager' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
	    10 => sprintf( __('Sermon draft updated. <a target="_blank" href="%s">Preview sermon</a>', 'sermon-manager'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
	  );

	  return $messages;
	}

	// TO DO: Add more help information
	//display contextual help for Sermons
	//add_action( 'contextual_help', 'add_wpfc_sermon_help_text', 10, 3 );

	//only run on edit.php page
	function wpfc_column_orderby_function()
	{
		add_filter( 'request', array( $this, 'wpfc_column_orderby' ) );
	}

	function wpfc_sermon_edit_columns($columns) {
		$columns = array(
			"cb"       => "<input type=\"checkbox\" />",
			"title"    => __('Sermon Title', 'sermon-manager'),
			"preacher" => __('Preacher', 'sermon-manager'),
			"series"   => __('Sermon Series', 'sermon-manager'),
			"topics"   => __('Topics', 'sermon-manager'),
			"views"    => __('Views', 'sermon-manager'),
			"preached" => __('Date Preached', 'sermon-manager'),
			"passage"  => __('Bible Passage', 'sermon-manager'),
		);
		return $columns;
	}

	function wpfc_sermon_columns($column){
		global $post;

		switch ($column){
			case "preacher":
				echo get_the_term_list($post->ID, 'wpfc_preacher', '', ', ','');
				break;
			case "series":
				echo get_the_term_list($post->ID, 'wpfc_sermon_series', '', ', ','');
				break;
			case "topics":
				echo get_the_term_list($post->ID, 'wpfc_sermon_topics', '', ', ','');
				break;
			case "views":
				$Sermon_Manager_Entry_Views = new Sermon_Manager_Entry_Views();
				$getviews = $Sermon_Manager_Entry_Views->wpfc_entry_views_get( array( 'post_id' => $post->ID ) );
				echo $getviews;
				break;
			case "preached":
				//$Sermon_Manager_Template_Tags = new Sermon_Manager_Template_Tags();
				$getdate = wpfc_sermon_date_filter();
				echo $getdate;
				break;
			case "passage":
				echo get_post_meta( $post->ID, 'bible_passage', true );
				break;
		}
	}

	// Register the column as sortable
	// @url https://gist.github.com/scribu/906872
	function wpfc_column_register_sortable( $columns ) {
		$columns = array(
			"title"    => "title",
			"preached" => "preached",
			"preacher" => "preacher",
			"series"   => "series",
			"topics"   => "topics",
			"views"    => "views",
			"passage"  => "passage"
		);
		return $columns;
	}

	function wpfc_column_orderby( $vars ) {
		if ( isset( $vars['post_type'] ) && $vars['post_type'] == 'wpfc_sermon' ) {
			if ( isset( $vars['orderby'] ) && $vars['orderby'] == 'passage' ) {
				$vars = array_merge( $vars, array(
					'meta_key' => 'bible_passage',
					'orderby' => 'meta_value'
				) );
			}
			if ( isset( $vars['orderby'] ) && $vars['orderby'] == 'preached' ) {
				$vars = array_merge( $vars, array(
					'meta_key' => 'sermon_date',
					'orderby' => 'meta_value'
				) );
			}
		}

		return $vars;
	}

	/*
	Taxonomy Short Description
	http://wordpress.mfields.org/plugins/taxonomy-short-description/
	Shortens the description shown in the administration panels for all categories, tags and custom taxonomies.
	V: 1.3.1
	Copyright 2011  Michael Fields  michael@mfields.org

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License version 2 as published by
	the Free Software Foundation.

	Function names have been modified to prevent conflicts.
	*/

	// Actions.
	function wpfc_taxonomy_short_description_actions() {
		$taxonomies = get_taxonomies();
		foreach ( $taxonomies as $taxonomy ) {
			$config = get_taxonomy( $taxonomy );
			if ( isset( $config->show_ui ) && true == $config->show_ui ) {
				add_action( 'manage_' . $taxonomy . '_custom_column', array( $this,'wpfc_taxonomy_short_description_rows'), 10, 3 );
				add_action( 'manage_edit-' . $taxonomy . '_columns',  array( $this,'wpfc_taxonomy_short_description_columns') );
				add_filter( 'manage_edit-' . $taxonomy . '_sortable_columns', array( $this,'wpfc_taxonomy_short_description_columns') );
			}
		}
	}

	// Term Columns.
	// Remove the default "Description" column. Add a custom "Short Description" column.
	function wpfc_taxonomy_short_description_columns( $columns ) {
		$position = 0;
		$iterator = 1;
		foreach( $columns as $column => $display_name ) {
			if ( 'name' == $column ) {
				$position = $iterator;
			}
			$iterator++;
		}
		if ( 0 < $position ) {
			/* Store all columns up to and including "Name". */
			$before = $columns;
			array_splice( $before, $position );

			/* All of the other columns are stored in $after. */
			$after  = $columns;
			$after = array_diff ( $columns, $before );

			/* Prepend a custom column for the short description. */
			$after = array_reverse( $after, true );
			$after['mfields_short_description'] = $after['description'];
			$after = array_reverse( $after, true );

			/* Remove the original description column. */
			unset( $after['description'] );

			/* Join all columns back together. */
			$columns = $before + $after;
		}
		return $columns;
	}


	// Term Rows. - Display the shortened description in each row's custom column.
	function wpfc_taxonomy_short_description_rows( $string, $column_name, $term ) {
		if ( 'mfields_short_description' == $column_name ) {
			global $taxonomy;
			$string = term_description( $term, $taxonomy );
			$string = $this->wpfc_taxonomy_short_description_shorten( $string, apply_filters( 'mfields_taxonomy_short_description_length', 130 ) );
		}
		return $string;
	}

	// Shorten a string to a given length.
	function wpfc_taxonomy_short_description_shorten( $string, $max_length = 23, $append = '&#8230;', $encoding = 'utf8' ) {

		/* Sanitize $string. */
		$string = strip_tags( $string );
		$string = trim( $string );
		$string = html_entity_decode( $string, ENT_QUOTES, 'UTF-8' );
		$string = rtrim( $string, '-' );

		/* Sanitize $max_length */
		if ( 0 == abs( (int) $max_length ) ) {
			$max_length = 23;
		}

		/* Return early if the php "mbstring" extension is not installed. */
		if ( ! function_exists( 'mb_substr' ) ) {
			$length = strlen( $string );
			if ( $length > $max_length ) {
				return substr_replace( $string, $append, $max_length );
			}
			return $string;
		}

		/* Count how many characters are in the string. */
		$length = strlen( utf8_decode( $string ) );

		/* String is longer than max-length. It needs to be shortened. */
		if ( $length > $max_length ) {

			/* Shorten the string to max-length */
			$short = mb_substr( $string, 0, $max_length, $encoding );

			/*
			 * A word has been cut in half during shortening.
			 * If the shortened string contains more than one word
			 * the last word in the string will be removed.
			 */
			if ( 0 !== mb_strpos( $string, $short . ' ', 0, $encoding ) ) {
				$pos = mb_strrpos( $short, ' ', $encoding );
				if ( false !== $pos ) {
					$short = mb_substr( $short, 0, $pos, $encoding );
				}
			}

			/* Append shortened string with the value of $append preceeded by a non-breaking space. */
			$string = $short . ' ' . $append;
		}

		return $string;
	}

	/*
	 * @since 2014-01-08
	 * Add the number of sermons to the Right Now / At a Glance on the Dashboard
	 */
	function wpfc_right_now() {
	    $num_posts = wp_count_posts('wpfc_sermon');
	    $num = number_format_i18n($num_posts->publish);
	    $text = _n('Sermon', 'Sermons', intval($num_posts->publish));
		if ( current_user_can('edit_posts') ) {
		    $num = "<a href='edit.php?post_type=wpfc_sermon'>$num</a>";
		    $text = "<a href='edit.php?post_type=wpfc_sermon'>$text</a>";
		}
		echo '<td class="first b b-sermon">' . $num . '</td><td class="t sermons">' . $text . '</td></tr>';
	}

	function wpfc_dashboard() {
	    $num_posts = wp_count_posts('wpfc_sermon');
	    $num = number_format_i18n($num_posts->publish);
	    $text = _n('Sermon', 'Sermons', intval($num_posts->publish));
	    /*
	     * Not pretty but works.
	     * Alt version using WP book icon commented out below.
	     * content is the the icon
	     * margin-left aligns the icon
	     * margin-right aligns the text
	     */
	    if ( current_user_can('edit_posts') ) {
	    	$link = '<a href="edit.php?post_type=wpfc_sermon">' . $num . ' ' . $text . '</a>';
	    }
	    else {
	    	$link = $num . ' ' . $text;
	    }
	    $items = '<li class="sermon-count">' . $link . '</li>';
	    echo "<style>.sermon-count a:before { content: url('" . SM_PLUGIN_URL.'includes/img/book-open-bookmark.png' . "') !important; margin-left: 2px !important; margin-right: 7px !important;}</style>";
		// echo "<style>.sermon-count a:before { content: '\\f330' !important;}</style>";
	    echo $items;
	}
}
$Sermon_Manager_Admin_Functions = new Sermon_Manager_Admin_Functions();
?>
