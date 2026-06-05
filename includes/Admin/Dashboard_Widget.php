<?php
/**
 * Admin dashboard widget.
 *
 * @package ContentDecayDetector
 */

namespace ContentDecayDetector\Admin;

// phpcs:disable WordPress.Files.FileName.InvalidClassFileName, WordPress.Files.FileName.NotHyphenatedLowercase

defined( 'ABSPATH' ) || exit;

use ContentDecayDetector\PostSnapshot;
use ContentDecayDetector\Settings;

/**
 * Class Dashboard_Widget
 *
 * Registers a WordPress dashboard widget showing the top 5
 * most decayed posts with links to the full report.
 */
class Dashboard_Widget {

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
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'wp_dashboard_setup', array( $this, 'add_widget' ) );
	}

	/**
	 * Register the dashboard widget.
	 *
	 * @return void
	 */
	public function add_widget(): void {
		wp_add_dashboard_widget(
			'cdd_decay_widget',
			__( 'Content Decay — Top 5 Decaying Posts', 'content-decay-detector' ),
			array( $this, 'render_widget' )
		);
	}

	/**
	 * Render the dashboard widget content.
	 *
	 * @return void
	 */
	public function render_widget(): void {
		$threshold = $this->settings->get_threshold();
		$snapshots = $this->snapshot->get_flagged( $threshold );
		$snapshots = array_slice( $snapshots, 0, 5 );

		if ( empty( $snapshots ) ) {
			echo '<p>' . esc_html__( 'No decaying posts detected. Great job!', 'content-decay-detector' ) . '</p>';
			return;
		}

		echo '<table style="width:100%;border-collapse:collapse;">';
		echo '<thead><tr>';
		echo '<th style="text-align:left;padding:6px 8px;border-bottom:1px solid #ddd;">' . esc_html__( 'Post', 'content-decay-detector' ) . '</th>';
		echo '<th style="text-align:center;padding:6px 8px;border-bottom:1px solid #ddd;">' . esc_html__( 'Score', 'content-decay-detector' ) . '</th>';
		echo '</tr></thead>';
		echo '<tbody>';

		foreach ( $snapshots as $snapshot ) {
			$post = get_post( (int) $snapshot['post_id'] );
			if ( ! $post ) {
				continue;
			}

			$score = (int) $snapshot['decay_score'];
			$color = $score >= 70 ? '#46b450' : ( $score >= 40 ? '#ffb900' : '#dc3232' );

			echo '<tr>';
			echo '<td style="padding:6px 8px;border-bottom:1px solid #f0f0f0;">';
			echo '<a href="' . esc_url( get_edit_post_link( $post->ID ) ) . '">' . esc_html( $post->post_title ) . '</a>';
			echo '</td>';
			echo '<td style="text-align:center;padding:6px 8px;border-bottom:1px solid #f0f0f0;">';
			echo '<span style="background:' . esc_attr( $color ) . ';color:#fff;padding:2px 8px;border-radius:3px;font-weight:bold;">' . esc_html( $score ) . '</span>';
			echo '</td>';
			echo '</tr>';
		}

		echo '</tbody></table>';

		echo '<p style="margin-top:12px;text-align:right;">';
		echo '<a href="' . esc_url( admin_url( 'tools.php?page=cdd-decay-report' ) ) . '">' . esc_html__( 'View full report &rarr;', 'content-decay-detector' ) . '</a>';
		echo '</p>';
	}
}
