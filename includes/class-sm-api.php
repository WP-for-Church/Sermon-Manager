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
	}

	public function fix_ordering( $args ) {
		if ( $args['orderby'] === 'date' ) {
			$args['orderby']      = 'meta_value_num';
			$args['meta_key']     = 'sermon_date';
			$args['meta_value']   = time();
			$args['meta_compare'] = '<=';

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
		) );

		$data['sermon_audio']          = $post_meta['sermon_audio'][0];
		$data['sermon_audio_duration'] = $post_meta['_wpfc_sermon_duration'][0];
		$data['views']                 = $post_meta['Views'][0];
		$data['bible_passage']         = $post_meta['bible_passage'][0];
		$data['sermon_description']    = $post_meta['sermon_description'][0];
		$data['sermon_video_embed']    = $post_meta['sermon_video'][0];
		$data['sermon_video_url']      = $post_meta['sermon_video_link'][0];
		$data['sermon_bulletin']       = $post_meta['sermon_bulletin'][0];
		$data['featured_url']          = wp_get_attachment_url( $post_meta['_thumbnail_id'][0] );

		if ( SM_Dates::get( 'Y-m-d H:m:s', $data['id'] ) !== false ) {
			$data['date']     = mysql_to_rfc3339( SM_Dates::get( 'Y-m-d H:m:s', $data['id'] ) );
			$data['date_gmt'] = mysql_to_rfc3339( SM_Dates::get( 'Y-m-d H:m:s', $data['id'] ) );
		}

		return $response;
	}
}

new SM_API();