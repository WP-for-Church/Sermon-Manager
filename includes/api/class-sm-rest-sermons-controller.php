<?php
/**
 * Created by PhpStorm.
 * User: nikola
 * Date: 9/21/17
 * Time: 3:27 PM
 */

class SM_REST_Sermons_Controller extends WP_REST_Controller {
	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'sm/v1';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'sermons';

	/**
	 * Register the routes for /sermons/*.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );
	}
}