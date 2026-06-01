<?php
/**
 * Content decay scanner.
 *
 * @package ContentDecayDetector
 */

namespace ContentDecayDetector;

// phpcs:disable WordPress.Files.FileName.InvalidClassFileName, WordPress.Files.FileName.NotHyphenatedLowercase

defined( 'ABSPATH' ) || exit;

/**
 * Class Scanner
 *
 * Registers and handles the WP Cron job that scans all published
 * posts for content decay. Processes posts in batches to avoid timeouts.
 */
class Scanner {

	/**
	 * The cron action hook name.
	 *
	 * @var string
	 */
	const CRON_HOOK = 'cdd_weekly_decay_scan';

	/**
	 * Number of posts to process per batch.
	 *
	 * @var int
	 */
	const BATCH_SIZE = 50;

	/**
	 * Settings instance.
	 *
	 * @var Settings
	 */
	private Settings $settings;

	/**
	 * PostSnapshot instance.
	 *
	 * @var PostSnapshot
	 */
	private PostSnapshot $snapshot;

	/**
	 * Constructor.
	 *
	 * @param Settings     $settings Settings instance.
	 * @param PostSnapshot $snapshot PostSnapshot instance.
	 */
	public function __construct( Settings $settings, PostSnapshot $snapshot ) {
		$this->settings = $settings;
		$this->snapshot = $snapshot;
	}

	/**
	 * Register cron hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( self::CRON_HOOK, array( $this, 'run_scan' ) );
	}

	/**
	 * Schedule the cron event if not already scheduled.
	 *
	 * Called on plugin activation.
	 *
	 * @return void
	 */
	public static function schedule(): void {
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time(), 'weekly', self::CRON_HOOK );
		}
	}

	/**
	 * Unschedule the cron event.
	 *
	 * Called on plugin deactivation.
	 *
	 * @return void
	 */
	public static function unschedule(): void {
		$timestamp = wp_next_scheduled( self::CRON_HOOK );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, self::CRON_HOOK );
		}
	}

	/**
	 * Run the full decay scan across all published posts.
	 *
	 * Processes posts in batches of 50 to avoid memory and timeout issues.
	 *
	 * @return void
	 */
	public function run_scan(): void {
		$page       = 1;
		$post_count = 0;

		do {
			$posts      = $this->get_posts_batch( $page );
			$post_count = count( $posts );

			foreach ( $posts as $post ) {
				$this->process_post( $post->ID );
			}

			++$page;
		} while ( self::BATCH_SIZE === $post_count );
	}

	/**
	 * Get a batch of published posts.
	 *
	 * @param int $page Page number for pagination.
	 *
	 * @return \WP_Post[] Array of WP_Post objects.
	 */
	private function get_posts_batch( int $page ): array {
		return get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => self::BATCH_SIZE,
				'paged'          => $page,
				'fields'         => 'all',
				'no_found_rows'  => true,
			)
		);
	}

	/**
	 * Process a single post — take a snapshot and calculate decay score.
	 *
	 * @param int $post_id The post ID to process.
	 *
	 * @return void
	 */
	private function process_post( int $post_id ): void {
		// Get mock traffic score for now.
		// Phase 3 will replace this with real GSC data.
		$traffic_score = $this->get_mock_traffic_score( $post_id );

		// Get previous snapshot to calculate decay.
		$previous = $this->snapshot->get_latest( $post_id );

		// Calculate decay score.
		$decay_score = $this->calculate_decay_score( $traffic_score, $previous );

		// Generate suggestions.
		$suggestions     = new Suggestions( $post_id, $decay_score, $this->settings );
		$suggestion_list = $suggestions->generate();

		// Save snapshot.
		$this->snapshot->save( $post_id, $traffic_score, $decay_score, $suggestion_list );
	}

	/**
	 * Generate a mock traffic score for testing purposes.
	 *
	 * Returns a realistic-looking score that slightly varies each time
	 * to simulate real traffic fluctuations.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return int Mock traffic score.
	 */
	private function get_mock_traffic_score( int $post_id ): int {
		// Use post ID as seed for consistency, with small random variation.
		$base      = ( $post_id * 137 ) % 1000;
		$variation = wp_rand( -50, 50 );
		return max( 0, $base + $variation );
	}

	/**
	 * Calculate the decay score for a post.
	 *
	 * Compares current traffic score against previous snapshot.
	 * Returns 100 if no previous snapshot exists (post is healthy by default).
	 *
	 * @param int        $current_score Current traffic score.
	 * @param array|null $previous      Previous snapshot row or null.
	 *
	 * @return int Decay score between 0 and 100.
	 */
	public function calculate_decay_score( int $current_score, ?array $previous ): int {
		if ( null === $previous || 0 === (int) $previous['traffic_score'] ) {
			return 100;
		}

		$previous_score = (int) $previous['traffic_score'];
		$score          = (int) round( ( $current_score / $previous_score ) * 100 );

		return max( 0, min( 100, $score ) );
	}
}
