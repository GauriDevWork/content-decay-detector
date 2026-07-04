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
 * Runs the weekly decay scan via WP Cron.
 *
 * Fetches all published posts in batches of 50, scores each one,
 * generates suggestions, and saves a snapshot to the database.
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
		// Calculate content health score from real WordPress post data.
		$traffic_score = $this->get_content_health_score( $post_id );

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
	 * Calculate a content health score based on real WordPress data.
	 *
	 * Replaces the mock traffic score with a real score derived from:
	 * - Post age (max 50 points) — recently updated content scores higher
	 * - Word count (max 30 points) — longer content scores higher
	 * - Comment count (max 20 points) — more engagement scores higher
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return int Content health score between 0 and 100.
	 */
	private function get_content_health_score( int $post_id ): int {
		$post = get_post( $post_id );

		if ( ! $post ) {
			return 0;
		}

		// Age score (max 50 points).
		// A post updated today scores 50. After 730 days (2 years) scores 0.
		$days_old  = ( time() - strtotime( $post->post_modified ) ) / DAY_IN_SECONDS;
		$age_score = (int) max( 0, 50 - ( $days_old / 730 * 50 ) );

		// Word count score (max 30 points).
		// 1000+ words scores 30. Under 100 words scores 0.
		$word_count       = str_word_count( wp_strip_all_tags( $post->post_content ) );
		$word_score       = (int) min( 30, ( $word_count / 1000 ) * 30 );

		// Comment score (max 20 points).
		// 10+ comments scores 20. 0 comments scores 0.
		$comment_count    = (int) $post->comment_count;
		$comment_score    = (int) min( 20, ( $comment_count / 10 ) * 20 );

		return $age_score + $word_score + $comment_score;
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
