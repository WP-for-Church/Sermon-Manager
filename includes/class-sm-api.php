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
		// Add query vars
		add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );

		// Register API endpoints
		add_action( 'init', array( $this, 'add_endpoint' ), 0 );

		// Handle sm-api endpoint requests
		add_action( 'parse_request', array( $this, 'handle_api_requests' ), 0 );

		// WP REST API
		$this->rest_api_init();
	}

	/**
	 * Init WP REST API
	 */
	private function rest_api_init() {
		// REST API was included starting WordPress 4.4
		if ( ! class_exists( 'WP_REST_Server' ) ) {
			return;
		}

		$this->rest_api_includes();

		// Init REST API routes
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ), 10 );
	}

	/**
	 * Include REST API classes.
	 *
	 * @since 3.0.0
	 */
	private function rest_api_includes() {
		include_once 'api/class-sm-rest-sermons-controller.php';

		if ( ! class_exists( 'WP_REST_Controller' ) ) {
			include_once ABSPATH . 'wp-includes/rest-api/endpoints/class-wp-rest-controller.php';
		}
	}

	/**
	 * Sermon Manager API
	 */
	public static function add_endpoint() {
		add_rewrite_endpoint( 'sm-api', EP_ALL );
	}

	/**
	 * Add new query vars
	 *
	 * @param array $vars
	 *
	 * @return string[]
	 */
	public function add_query_vars( $vars ) {
		$vars[] = 'sm-api';

		return $vars;
	}

	/**
	 * API request - Trigger any API requests
	 */
	public function handle_api_requests() {
		global $wp;

		if ( ! empty( $_GET['sm-api'] ) ) {
			$wp->query_vars['sm-api'] = $_GET['sm-api'];
		}

		// sm-api endpoint requests.
		if ( ! empty( $wp->query_vars['sm-api'] ) ) {

			// Buffer, we won't want any output here.
			ob_start();

			// No cache headers.
			nocache_headers();

			// Clean the API request.
			$api_request = strtolower( sm_clean( $wp->query_vars['sm-api'] ) );

			// Trigger generic action before request hook.
			do_action( 'sm_api_request', $api_request );

			// Is there actually something hooked into this API request? If not trigger 400 - Bad request.
			status_header( has_action( 'sm_api_' . $api_request ) ? 200 : 400 );

			// Trigger an action which plugins can hook into to fulfill the request.
			do_action( 'sm_api_' . $api_request );

			// Done, clear buffer and exit.
			ob_end_clean();
			die( '-1' );
		}
	}

	/**
	 * Register REST API routes
	 */
	public function register_rest_routes() {
		$controllers = array(
			'SM_REST_Sermons_Controller',
		);

		foreach ( $controllers as $controller ) {
			$this->$controller = new $controller();
			$this->$controller->register_routes();
		}
	}
}

new SM_API();