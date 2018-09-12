<?php
/**
 * API.
 *
 * @package SM/Core/API
 */

defined( 'ABSPATH' ) or die;

/**
 * Sermon Manager API.
 *
 * Handles SM-API endpoint requests.
 *
 * @since 2.7
 */
class SM_API {
	/**
	 * Init class.
	 */
	public function __construct() {
		// Add filters for wpfc_sermon post type.
		add_action( 'rest_wpfc_sermon_collection_params', array( $this, 'modify_query_params' ) );

		// Add custom data to the response.
		add_filter( 'rest_prepare_wpfc_sermon', array( $this, 'add_custom_data' ) );

		// Fix ordering.
		add_filter( 'rest_wpfc_sermon_query', array( $this, 'fix_ordering' ) );

		// Save custom data.
		add_action( 'rest_insert_wpfc_sermon', array( $this, 'save_custom_data' ), 10, 2 );
	}

	/**
	 * Saves custom Sermon Manager data passed through REST API into database.
	 *
	 * @param WP_Post         $post    Post object.
	 * @param WP_REST_Request $request The request.
	 */
	public function save_custom_data( $post, $request ) {
		if ( ! defined( 'REST_REQUEST' ) || ( defined( 'REST_REQUEST' ) && REST_REQUEST !== true ) ) {
			return;
		}

		$params = $request->get_params();

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
			$data = isset( $params[ $key ] ) ? $params[ $key ] : null;

			if ( ! $data ) {
				if ( 'sermon_date' === $key ) {
					update_post_meta( $post->ID, 'sermon_date', strtotime( $post->post_date ) );
					update_post_meta( $post->ID, 'sermon_date_auto', 1 );
				} else {
					continue;
				}
			}

			update_post_meta( $post->ID, $key, $data );

			if ( 'sermon_date' === $key ) {
				update_post_meta( $post->ID, 'sermon_date_auto', 0 );
			}

			add_filter( "cmb2_override_{$key}_meta_remove", '__return_true' );
			add_filter( "cmb2_override_{$key}_meta_save", '__return_true' );
		}
	}

	/**
	 * Fixes ordering by date to use `sermon_date` meta (aka "Preached Date").
	 * Use "wpdate" for original WordPress "date" ordering.
	 *
	 * @param array $args Query parameters.
	 *
	 * @return mixed Modified arguments
	 */
	public function fix_ordering( $args ) {
		if ( 'date' === $args['orderby'] ) {
			$args['orderby']        = 'meta_value_num';
			$args['meta_key']       = 'sermon_date';
			$args['meta_value_num'] = time();
			$args['meta_compare']   = '<=';
		} elseif ( 'wpdate' === $args['orderby'] ) {
			$args['orderby'] = 'date';
		}

		return $args;
	}

	/**
	 * Currently, it only replaces "post" string with "sermon", but we can add more query parameters here if needed.
	 *
	 * @param array $query_params Query parameters.
	 *
	 * @return array Modified query params
	 */
	public function modify_query_params( $query_params ) {
		// Replace "post" to "sermon".
		$query_params['slug']['description']   = str_replace( 'post', 'sermon', $query_params['slug']['description'] );
		$query_params['status']['description'] = str_replace( 'post', 'sermon', $query_params['status']['description'] );
		$query_params['after']['description']  = str_replace( 'post', 'sermon', $query_params['after']['description'] );
		$query_params['before']['description'] = str_replace( 'post', 'sermon', $query_params['before']['description'] );

		return $query_params;
	}

	/**
	 * Add custom data to the response, such as audio, passage, etc.
	 *
	 * @param WP_REST_Response $response The response object.
	 *
	 * @return WP_REST_Response Modified response,
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

		$audio_id     = isset( $post_meta['sermon_audio_id'][0] ) ? $post_meta['sermon_audio_id'][0] : null;
		$audio_url_wp = $audio_id ? wp_get_attachment_url( intval( $audio_id ) ) : null;
		$audio_url    = $post_meta['sermon_audio'][0];

		$data['sermon_audio']          = $audio_id && $audio_url_wp ? $audio_url_wp : $audio_url;
		$data['sermon_audio_duration'] = $post_meta['_wpfc_sermon_duration'][0];
		$data['_views']                = $post_meta['Views'][0];
		$data['bible_passage']         = $post_meta['bible_passage'][0];
		$data['sermon_description']    = $post_meta['sermon_description'][0];
		$data['sermon_video_embed']    = $post_meta['sermon_video'][0];
		$data['sermon_video_url']      = $post_meta['sermon_video_link'][0];
		$data['sermon_bulletin']       = $post_meta['sermon_bulletin'][0];
		$data['_featured_url']         = wp_get_attachment_url( $post_meta['_thumbnail_id'][0] );

		$date = SM_Dates::get( 'U', $data['id'] );
		if ( $date ) {
			$data['sermon_date']       = intval( $date );
			$data['_sermon_date_auto'] = 1 == $post_meta['sermon_date_auto'][0] ? true : false;
		}

		return $response;
	}
}

new SM_API();
