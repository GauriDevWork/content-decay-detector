<?php
/**
 * Email digest handler.
 *
 * @package ContentDecayDetector
 */

namespace ContentDecayDetector;

// phpcs:disable WordPress.Files.FileName.InvalidClassFileName, WordPress.Files.FileName.NotHyphenatedLowercase

defined( 'ABSPATH' ) || exit;

/**
 * Class Email
 *
 * Builds and sends the weekly decay digest email to the site admin.
 * Includes top 5 decaying posts with scores and links.
 */
class Email {

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
		add_action( 'cdd_weekly_decay_scan', array( $this, 'maybe_send_digest' ) );
	}

	/**
	 * Send digest only if email notifications are enabled.
	 *
	 * @return void
	 */
	public function maybe_send_digest(): void {
		if ( ! $this->settings->is_email_enabled() ) {
			return;
		}
		$this->send_digest();
	}

	/**
	 * Build and send the weekly digest email.
	 *
	 * @return bool True if email was sent successfully.
	 */
	public function send_digest(): bool {
		$threshold = $this->settings->get_threshold();
		$snapshots = $this->snapshot->get_flagged( $threshold );
		$snapshots = array_slice( $snapshots, 0, 5 );

		if ( empty( $snapshots ) ) {
			return false;
		}

		$to      = get_option( 'admin_email' );
		$subject = sprintf(
			/* translators: %s: site name */
			__( '[%s] Weekly Content Decay Report', 'content-decay-detector' ),
			get_bloginfo( 'name' )
		);

		$body    = $this->build_email_body( $snapshots );
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		return wp_mail( $to, $subject, $body, $headers );
	}

	/**
	 * Build the HTML email body.
	 *
	 * @param array $snapshots Array of snapshot rows.
	 *
	 * @return string HTML email content.
	 */
	private function build_email_body( array $snapshots ): string {
		$site_name   = esc_html( get_bloginfo( 'name' ) );
		$report_url  = esc_url( admin_url( 'tools.php?page=cdd-decay-report' ) );
		$report_link = '<a href="' . $report_url . '">' . esc_html__( 'View Full Report', 'content-decay-detector' ) . '</a>';

		$rows = '';
		foreach ( $snapshots as $snapshot ) {
			$post = get_post( (int) $snapshot['post_id'] );
			if ( ! $post ) {
				continue;
			}

			$score    = (int) $snapshot['decay_score'];
			$color    = $score >= 70 ? '#46b450' : ( $score >= 40 ? '#ffb900' : '#dc3232' );
			$edit_url = esc_url( get_edit_post_link( $post->ID ) );
			$title    = esc_html( $post->post_title );

			$rows .= '<tr>';
			$rows .= '<td style="padding:10px;border-bottom:1px solid #eee;">';
			$rows .= '<a href="' . $edit_url . '" style="color:#0073aa;text-decoration:none;">' . $title . '</a>';
			$rows .= '</td>';
			$rows .= '<td style="padding:10px;border-bottom:1px solid #eee;text-align:center;">';
			$rows .= '<span style="background:' . $color . ';color:#fff;padding:3px 10px;border-radius:3px;font-weight:bold;">' . esc_html( $score ) . '</span>';
			$rows .= '</td>';
			$rows .= '</tr>';
		}

		return '<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family:Arial,sans-serif;color:#333;max-width:600px;margin:0 auto;padding:20px;">
    <h2 style="color:#0073aa;">Content Decay Report — ' . $site_name . '</h2>
    <p>The following posts have been flagged as decaying and need your attention:</p>
    <table style="width:100%;border-collapse:collapse;margin:20px 0;">
        <thead>
            <tr style="background:#f0f0f0;">
                <th style="padding:10px;text-align:left;">Post Title</th>
                <th style="padding:10px;text-align:center;">Decay Score</th>
            </tr>
        </thead>
        <tbody>' . $rows . '</tbody>
    </table>
    <p>' . $report_link . '</p>
    <p style="color:#999;font-size:12px;">This email was sent by the Content Decay Detector plugin.</p>
</body>
</html>';
	}
}
