<?php
defined( 'ABSPATH' ) or die; // exit if accessed directly

/**
 * Sermon Manager API
 *
 * Handles SM-API endpoint requests.
 *
 * @since 2.7
 */
class SM_API {
	/**
	 * Init class
	 */
	public function __construct() {
		// Add filters for wpfc_sermon post type
		add_action( 'rest_wpfc_sermon_collection_params', array( $this, 'modify_query_params' ) );

		// Add custom data to the response
		add_filter( 'rest_prepare_wpfc_sermon', array( $this, 'add_custom_data' ) );

		// Fix ordering
		add_filter( 'rest_wpfc_sermon_query', array( $this, 'fix_ordering' ) );

		// Save custom data
		add_action( 'save_post_wpfc_sermon', array( $this, 'save_custom_data' ), 10, 3 );
	}

	/**
	 * Saves custom Sermon Manager data passed through REST API into database
	 *
	 * @param int     $post_ID Post ID.
	 * @param WP_Post $post    Post object.
	 * @param bool    $update  Whether this is an existing post being updated or not.
	 */
	public function save_custom_data( $post_ID, $post, $update ) {
		if ( ! defined( 'REST_REQUEST' ) || ( defined( 'REST_REQUEST' ) && REST_REQUEST !== true ) ) {
			return;
		}

		$keys = array(
			'sermon_audio',
			'sermon_audio_duration',
			'bible_passage',
			'sermon_description',
			'sermon_video_embed',
			'sermon_video_url',
			'sermon_bulletin',
			'sermon_date',
		);

		foreach ( $keys as $key ) {
			if ( ! $data = isset( $_POST[ $key ] ) ? $_POST[ $key ] : null ) {
				continue;
			}

			update_post_meta( $post_ID, $key, $data );

			if ( $key === 'sermon_date' ) {
				update_post_meta( $post_ID, 'sermon_date_auto', $data === '' );
			}

			add_filter( "cmb2_override_{$key}_meta_remove", '__return_true' );
		}
	}

	/**
	 * Fixes ordering by date to use `sermon_date` meta (aka "Preached Date")
	 * Use "wpdate" for original WordPress "date" ordering
	 *
	 * @param array $args WP_Query arguments
	 *
	 * @return mixed Modified arguments
	 */
	public function fix_ordering( $args ) {
		if ( $args['orderby'] === 'date' ) {
			$args['orderby']      = 'meta_value_num';
			$args['meta_key']     = 'sermon_date';
			$args['meta_value_num']   = time();
			$args['meta_compare'] = '<=';
		} elseif ( $args['orderby'] === 'wpdate' ) {
			$args['orderby'] = 'date';
		}

		return $args;
	}

	/**
	 * Currently, it only replaces "post" string with "sermon", but we can add more query parameters here if needed
	 *
	 * @param array $query_params
	 *
	 * @return array Modified query params
	 */
	public function modify_query_params( $query_params ) {
		// Replace "post" to "sermon"
		$query_params['slug']['description']   = str_replace( 'post', 'sermon', $query_params['slug']['description'] );
		$query_params['status']['description'] = str_replace( 'post', 'sermon', $query_params['status']['description'] );
		$query_params['after']['description']  = str_replace( 'post', 'sermon', $query_params['after']['description'] );
		$query_params['before']['description'] = str_replace( 'post', 'sermon', $query_params['before']['description'] );

		return $query_params;
	}

	/**
	 * Add custom data to the response, such as audio, passage, etc
	 *
	 * @param WP_REST_Response $response The response object.
	 *
	 * @return WP_REST_Response Modified response
	 */
	public function add_custom_data( $response ) {
		$data = &$response->data;

		$post_meta = wp_parse_args( get_post_meta( $data['id'] ), array(
			'sermon_audio'          => array( '' ),
			'_wpfc_sermon_duration' => array( '' ),
			'Views'                 => array( '' ),
			'bible_passage'         => array( '' ),
			'sermon_description'    => array( '' ),
			'sermon_video'          => array( '' ),
			'sermon_video_link'     => array( '' ),
			'sermon_bulletin'       => array( '' ),
			'_thumbnail_id'         => array( '' ),
			'sermon_date_auto'      => array( '' ),
		) );

		$data['sermon_audio']          = $post_meta['sermon_audio'][0];
		$data['sermon_audio_duration'] = $post_meta['_wpfc_sermon_duration'][0];
		$data['_views']                = $post_meta['Views'][0];
		$data['bible_passage']         = $post_meta['bible_passage'][0];
		$data['sermon_description']    = $post_meta['sermon_description'][0];
		$data['sermon_video_embed']    = $post_meta['sermon_video'][0];
		$data['sermon_video_url']      = $post_meta['sermon_video_link'][0];
		$data['sermon_bulletin']       = $post_meta['sermon_bulletin'][0];
		$data['_featured_url']         = wp_get_attachment_url( $post_meta['_thumbnail_id'][0] );

		if ( $date = SM_Dates::get( 'U', $data['id'] ) ) {
			$data['sermon_date']       = intval( $date );
			$data['_sermon_date_auto'] = $post_meta['sermon_date_auto'][0] == 1 ? true : false;
		}

		return $response;
	}
}

new SM_API();