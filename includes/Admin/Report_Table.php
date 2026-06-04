<?php
/**
 * Admin report table.
 *
 * @package ContentDecayDetector
 */

namespace ContentDecayDetector\Admin;

// phpcs:disable WordPress.Files.FileName.InvalidClassFileName, WordPress.Files.FileName.NotHyphenatedLowercase

defined( 'ABSPATH' ) || exit;

use ContentDecayDetector\PostSnapshot;
use ContentDecayDetector\Settings;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class Report_Table
 *
 * Extends WP_List_Table to display decaying posts in a sortable
 * admin table with bulk actions.
 */
class Report_Table extends \WP_List_Table {

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
	 * Minimum score filter.
	 *
	 * @var int
	 */
	private int $min_score = 0;

	/**
	 * Maximum score filter.
	 *
	 * @var int
	 */
	private int $max_score = 100;

	/**
	 * Constructor.
	 *
	 * @param PostSnapshot $snapshot PostSnapshot instance.
	 * @param Settings     $settings Settings instance.
	 * @param int          $min_score Minimum score filter.
	 * @param int          $max_score Maximum score filter.
	 */
	public function __construct( PostSnapshot $snapshot, Settings $settings, int $min_score = 0, int $max_score = 100 ) {
		parent::__construct(
			array(
				'singular' => 'decaying_post',
				'plural'   => 'decaying_posts',
				'ajax'     => false,
			)
		);

		$this->snapshot  = $snapshot;
		$this->settings  = $settings;
		$this->min_score = $min_score;
		$this->max_score = $max_score;
	}

	/**
	 * Define table columns.
	 *
	 * @return array Column slugs and labels.
	 */
	public function get_columns(): array {
		return array(
			'cb'            => '<input type="checkbox" />',
			'post_title'    => __( 'Post Title', 'content-decay-detector' ),
			'decay_score'   => __( 'Decay Score', 'content-decay-detector' ),
			'traffic_score' => __( 'Traffic Score', 'content-decay-detector' ),
			'snapshot_date' => __( 'Last Scanned', 'content-decay-detector' ),
			'suggestions'   => __( 'Suggestions', 'content-decay-detector' ),
			'status'        => __( 'Status', 'content-decay-detector' ),
		);
	}

	/**
	 * Define sortable columns.
	 *
	 * @return array Sortable column definitions.
	 */
	protected function get_sortable_columns(): array {
		return array(
			'decay_score'   => array( 'decay_score', true ),
			'snapshot_date' => array( 'snapshot_date', false ),
			'traffic_score' => array( 'traffic_score', false ),
		);
	}

	/**
	 * Define bulk actions.
	 *
	 * @return array Bulk action slugs and labels.
	 */
	protected function get_bulk_actions(): array {
		return array(
			'mark_reviewed' => __( 'Mark as Reviewed', 'content-decay-detector' ),
			'exclude'       => __( 'Exclude from Scanning', 'content-decay-detector' ),
		);
	}

	/**
	 * Render the checkbox column.
	 *
	 * @param array $item Row data.
	 *
	 * @return string Checkbox HTML.
	 */
	protected function column_cb( $item ): string {
		return '<input type="checkbox" name="snapshot_ids[]" value="' . esc_attr( $item['id'] ) . '" />';
	}

	/**
	 * Render the post title column.
	 *
	 * @param array $item Row data.
	 *
	 * @return string Post title with edit link.
	 */
	protected function column_post_title( array $item ): string {
		$post = get_post( (int) $item['post_id'] );
		if ( ! $post ) {
			return esc_html__( '(Post not found)', 'content-decay-detector' );
		}

		$title    = esc_html( $post->post_title );
		$edit_url = esc_url( get_edit_post_link( $post->ID ) );
		$view_url = esc_url( get_permalink( $post->ID ) );

		$actions = array(
			'edit' => '<a href="' . $edit_url . '">' . esc_html__( 'Edit', 'content-decay-detector' ) . '</a>',
			'view' => '<a href="' . $view_url . '" target="_blank">' . esc_html__( 'View', 'content-decay-detector' ) . '</a>',
		);

		return '<strong>' . $title . '</strong>' . $this->row_actions( $actions );
	}

	/**
	 * Render the decay score column with a colored badge.
	 *
	 * @param array $item Row data.
	 *
	 * @return string Score badge HTML.
	 */
	protected function column_decay_score( array $item ): string {
		$score = (int) $item['decay_score'];
		$color = $score >= 70 ? '#46b450' : ( $score >= 40 ? '#ffb900' : '#dc3232' );
		return '<span style="background:' . esc_attr( $color ) . ';color:#fff;padding:2px 8px;border-radius:3px;font-weight:bold;">'
			. esc_html( $score )
			. '</span>';
	}

	/**
	 * Render the traffic score column.
	 *
	 * @param array $item Row data.
	 *
	 * @return string Traffic score.
	 */
	protected function column_traffic_score( array $item ): string {
		return esc_html( $item['traffic_score'] );
	}

	/**
	 * Render the snapshot date column.
	 *
	 * @param array $item Row data.
	 *
	 * @return string Formatted date.
	 */
	protected function column_snapshot_date( array $item ): string {
		return esc_html( $item['snapshot_date'] );
	}

	/**
	 * Render the suggestions column.
	 *
	 * @param array $item Row data.
	 *
	 * @return string Suggestions list HTML.
	 */
	protected function column_suggestions( array $item ): string {
		$suggestions = json_decode( $item['suggestions'], true );
		if ( empty( $suggestions ) ) {
			return '<em>' . esc_html__( 'None', 'content-decay-detector' ) . '</em>';
		}
		$items = array_map( 'esc_html', $suggestions );
		return '<ul style="margin:0;padding-left:16px;"><li>' . implode( '</li><li>', $items ) . '</li></ul>';
	}

	/**
	 * Render the status column.
	 *
	 * @param array $item Row data.
	 *
	 * @return string Status badge HTML.
	 */
	protected function column_status( array $item ): string {
		if ( (int) $item['is_reviewed'] ) {
			return '<span style="color:#46b450;font-weight:bold;">' . esc_html__( 'Reviewed', 'content-decay-detector' ) . '</span>';
		}
		return '<span style="color:#dc3232;font-weight:bold;">' . esc_html__( 'Flagged', 'content-decay-detector' ) . '</span>';
	}

	/**
	 * Default column renderer.
	 *
	 * @param array  $item        Row data.
	 * @param string $column_name Column slug.
	 *
	 * @return string Column value.
	 */
	protected function column_default( $item, $column_name ): string {
		return isset( $item[ $column_name ] ) ? esc_html( $item[ $column_name ] ) : '';
	}

	/**
	 * Prepare table items — fetch data and set pagination.
	 *
	 * @return void
	 */
	public function prepare_items(): void {
		$threshold = $this->settings->get_threshold();
		$data      = $this->snapshot->get_flagged( $threshold );
		// Apply score range filter.
		$data = array_filter(
			$data,
			function ( $item ) {
				$score = (int) $item['decay_score'];
				return $score >= $this->min_score && $score <= $this->max_score;
			}
		);
		$data = array_values( $data );
		// Handle sorting.
		$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : 'decay_score'; // phpcs:ignore WordPress.Security.NonceVerification
		$order   = isset( $_GET['order'] ) && 'asc' === $_GET['order'] ? 'asc' : 'desc'; // phpcs:ignore WordPress.Security.NonceVerification

		usort(
			$data,
			function ( $a, $b ) use ( $orderby, $order ) {
				$result = $a[ $orderby ] <=> $b[ $orderby ];
				return 'asc' === $order ? $result : -$result;
			}
		);

		// Pagination.
		$per_page     = 20;
		$current_page = $this->get_pagenum();
		$total_items  = count( $data );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
			)
		);

		$this->items = array_slice( $data, ( $current_page - 1 ) * $per_page, $per_page );

		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			$this->get_sortable_columns(),
		);
	}
}
