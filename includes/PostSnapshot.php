<?php
/**
 * Post snapshot model class.
 *
 * @package ContentDecayDetector
 */

namespace ContentDecayDetector;

defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.Files.FileName.InvalidClassFileName, WordPress.Files.FileName.NotHyphenatedLowercase
/**
 * Class PostSnapshot
 *
 * Handles all database operations for the wp_decay_snapshots table.
 * All reads and writes to this table go through this class only.
 */
class PostSnapshot {

	/**
	 * The database table name.
	 *
	 * @var string
	 */
	private string $table;

	/**
	 * Constructor. Sets the table name with correct prefix.
	 */
	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'decay_snapshots';
	}

	/**
	 * Save a new snapshot record to the database.
	 *
	 * @param int   $post_id       The post ID.
	 * @param int   $traffic_score Raw traffic score.
	 * @param int   $decay_score   Calculated decay score 0-100.
	 * @param array $suggestions   Array of suggestion strings.
	 *
	 * @return int|false The inserted row ID or false on failure.
	 */
	public function save( int $post_id, int $traffic_score, int $decay_score, array $suggestions = array() ): int|false {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->insert(
			$this->table,
			array(
				'post_id'       => $post_id,
				'snapshot_date' => current_time( 'Y-m-d' ),
				'traffic_score' => $traffic_score,
				'decay_score'   => $decay_score,
				'suggestions'   => wp_json_encode( $suggestions ),
				'is_reviewed'   => 0,
				'created_at'    => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%d', '%d', '%s', '%d', '%s' )
		);

		return $result ? $wpdb->insert_id : false;
	}

	/**
	 * Get all snapshots for a specific post, ordered by date descending.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return array Array of snapshot rows.
	 */
	public function get_by_post( int $post_id ): array {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$results = $wpdb->get_results(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				'SELECT * FROM ' . $this->table . ' WHERE post_id = %d ORDER BY snapshot_date DESC',
				$post_id
			),
			ARRAY_A
		);
		return $results ?? array();
	}

	/**
	 * Get the most recent snapshot for a specific post.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return array|null The latest snapshot row or null if none found.
	 */
	public function get_latest( int $post_id ): ?array {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->get_row(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				'SELECT * FROM ' . $this->table . ' WHERE post_id = %d ORDER BY snapshot_date DESC LIMIT 1',
				$post_id
			),
			ARRAY_A
		);
		return $result ?? null;
	}

	/**
	 * Get all snapshots flagged as decaying and not yet reviewed.
	 *
	 * @param int $threshold Decay score threshold. Posts below this are flagged.
	 *
	 * @return array Array of snapshot rows.
	 */
	public function get_flagged( int $threshold = 50 ): array {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$results = $wpdb->get_results(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				'SELECT * FROM ' . $this->table . ' WHERE decay_score < %d AND is_reviewed = 0 ORDER BY decay_score ASC',
				$threshold
			),
			ARRAY_A
		);
		return $results ?? array();
	}

	/**
	 * Delete all snapshots for a specific post.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return int|false Number of rows deleted or false on failure.
	 */
	public function delete( int $post_id ): int|false {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->delete(
			$this->table,
			array( 'post_id' => $post_id ),
			array( '%d' )
		);
	}

	/**
	 * Mark a snapshot as reviewed.
	 *
	 * @param int $id Snapshot ID.
	 *
	 * @return bool True on success.
	 */
	public function mark_reviewed( int $id ): bool {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->update(
			$this->table,
			array( 'is_reviewed' => 1 ),
			array( 'id' => $id ),
			array( '%d' ),
			array( '%d' )
		);
		return false !== $result;
	}

	/**
	 * Delete all snapshots for a post to exclude it from future scans.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return bool True on success.
	 */
	public function exclude_post( int $post_id ): bool {
		return false !== $this->delete( $post_id );
	}
}
