<?php
/**
 * REST API endpoint for decay reports.
 *
 * @package ContentDecayDetector
 */

namespace ContentDecayDetector\REST;

// phpcs:disable WordPress.Files.FileName.InvalidClassFileName, WordPress.Files.FileName.NotHyphenatedLowercase

defined( 'ABSPATH' ) || exit;

use ContentDecayDetector\PostSnapshot;
use ContentDecayDetector\Settings;

/**
 * Class Reports
 *
 * Registers and handles the REST API endpoint for decay reports.
 * Endpoint: GET /wp-json/content-decay/v1/reports
 */
class Reports {

	/**
	 * API namespace.
	 *
	 * @var string
	 */
	const NAMESPACE = 'content-decay/v1';

	/**
	 * Endpoint route.
	 *
	 * @var string
	 */
	const ROUTE = '/reports';

	/**
	 * PostSnapshot instance.
	 *
	 * @var PostSnapshot
	 */
	private PostSnapshot $snapshot;

	/**
	 * Settings instance.
	 *
	 * @var Settings
	 */
	private Settings $settings;

	/**
	 * Constructor.
	 *
	 * @param PostSnapshot $snapshot PostSnapshot instance.
	 * @param Settings     $settings Settings instance.
	 */
	public function __construct( PostSnapshot $snapshot, Settings $settings ) {
		$this->snapshot = $snapshot;
		$this->settings = $settings;
	}

	/**
	 * Register REST API hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register all REST API routes.
	 *
	 * @return void
	 */
	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			self::ROUTE,
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_reports' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => $this->get_route_args(),
			)
		);
	}



	/**
	 * Check if request is from a logged in user with valid nonce.
	 *
	 * Provides more detailed error response than default permission callback.
	 *
	 * @return bool|\WP_Error True if authorized, WP_Error if not.
	 */
	public function check_permission(): bool|\WP_Error {
		if ( ! is_user_logged_in() ) {
			return new \WP_Error(
				'rest_not_logged_in',
				__( 'You must be logged in to access decay reports.', 'content-decay-detector' ),
				array( 'status' => 401 )
			);
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return new \WP_Error(
				'rest_forbidden',
				__( 'You do not have permission to access decay reports.', 'content-decay-detector' ),
				array( 'status' => 403 )
			);
		}

		return true;
	}

	/**
	 * Define and return the route arguments schema.
	 *
	 * @return array Route arguments with validation and sanitization.
	 */
	private function get_route_args(): array {
		return array(
			'threshold' => array(
				'description'       => 'Decay score threshold. Posts below this score are returned.',
				'type'              => 'integer',
				'default'           => null,
				'sanitize_callback' => 'absint',
				'validate_callback' => function ( $value ) {
					return is_numeric( $value ) && $value >= 0 && $value <= 100;
				},
			),
			'limit'     => array(
				'description'       => 'Maximum number of results to return.',
				'type'              => 'integer',
				'default'           => 20,
				'sanitize_callback' => 'absint',
				'validate_callback' => function ( $value ) {
					return is_numeric( $value ) && $value > 0 && $value <= 100;
				},
			),
		);
	}

	/**
	 * Handle GET /reports request.
	 *
	 * @param \WP_REST_Request $request The REST request object.
	 *
	 * @return \WP_REST_Response JSON response with flagged posts.
	 */
	public function get_reports( \WP_REST_Request $request ): \WP_REST_Response {
		$threshold = $request->get_param( 'threshold' ) ?? $this->settings->get_threshold();
		$limit     = $request->get_param( 'limit' ) ?? 20;

		$snapshots = $this->snapshot->get_flagged( $threshold );

		if ( empty( $snapshots ) ) {
			return new \WP_REST_Response( array(), 200 );
		}

		// Limit results.
		$snapshots = array_slice( $snapshots, 0, $limit );

		$data = array_map( array( $this, 'format_snapshot' ), $snapshots );

		return new \WP_REST_Response( $data, 200 );
	}

	/**
	 * Format a snapshot row for the API response.
	 *
	 * @param array $snapshot Raw snapshot row from DB.
	 *
	 * @return array Formatted response data.
	 */
	private function format_snapshot( array $snapshot ): array {
		$post = get_post( (int) $snapshot['post_id'] );

		return array(
			'post_id'       => (int) $snapshot['post_id'],
			'post_title'    => $post ? $post->post_title : '',
			'post_url'      => $post ? get_permalink( $post ) : '',
			'decay_score'   => (int) $snapshot['decay_score'],
			'traffic_score' => (int) $snapshot['traffic_score'],
			'snapshot_date' => $snapshot['snapshot_date'],
			'suggestions'   => json_decode( $snapshot['suggestions'], true ) ?? array(),
			'is_reviewed'   => (bool) $snapshot['is_reviewed'],
		);
	}
}
