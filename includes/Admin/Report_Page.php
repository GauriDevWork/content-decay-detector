<?php
/**
 * Admin report page.
 *
 * @package ContentDecayDetector
 */

namespace ContentDecayDetector\Admin;

// phpcs:disable WordPress.Files.FileName.InvalidClassFileName, WordPress.Files.FileName.NotHyphenatedLowercase

defined( 'ABSPATH' ) || exit;

use ContentDecayDetector\PostSnapshot;
use ContentDecayDetector\Settings;

/**
 * Class Report_Page
 *
 * Registers and renders the decay report admin page under Tools menu.
 */
class Report_Page {

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
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'admin_init', array( $this, 'process_bulk_actions' ) );
	}

	/**
	 * Add report page under Tools menu.
	 *
	 * @return void
	 */
	public function add_menu_page(): void {
		add_management_page(
			__( 'Decay Report', 'content-decay-detector' ),
			__( 'Decay Report', 'content-decay-detector' ),
			'manage_options',
			'cdd-decay-report',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Enqueue styles only on this page.
	 *
	 * @param string $hook Current admin page hook.
	 *
	 * @return void
	 */
	public function enqueue_styles( string $hook ): void {
		if ( 'tools_page_cdd-decay-report' !== $hook ) {
			return;
		}
		wp_enqueue_style(
			'cdd-report',
			CDD_PLUGIN_URL . 'assets/css/report.css',
			array(),
			CDD_VERSION
		);
	}

	/**
	 * Render the report page.
	 *
	 * @return void
	 */
	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Handle score filter.
		$min_score = isset( $_GET['min_score'] ) ? absint( $_GET['min_score'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
		$max_score = isset( $_GET['max_score'] ) ? absint( $_GET['max_score'] ) : 100; // phpcs:ignore WordPress.Security.NonceVerification

		$table = new Report_Table( $this->snapshot, $this->settings, $min_score, $max_score );
		$table->prepare_items();
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Content Decay Report', 'content-decay-detector' ); ?></h1>
			<p><?php echo esc_html__( 'Posts flagged as decaying based on traffic drop threshold.', 'content-decay-detector' ); ?></p>

			<form method="get" style="margin-bottom:16px;">
				<input type="hidden" name="page" value="cdd-decay-report" />
				<label for="min_score"><?php echo esc_html__( 'Score between:', 'content-decay-detector' ); ?></label>
				<input type="number" id="min_score" name="min_score" value="<?php echo esc_attr( $min_score ); ?>" min="0" max="100" style="width:60px;" />
				<span> &ndash; </span>
				<input type="number" name="max_score" value="<?php echo esc_attr( $max_score ); ?>" min="0" max="100" style="width:60px;" />
				<?php submit_button( __( 'Filter', 'content-decay-detector' ), 'secondary', 'filter', false ); ?>
			</form>

			<form method="get">
				<input type="hidden" name="page" value="cdd-decay-report" />
				<?php $table->display(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Process bulk actions submitted from the report table.
	 *
	 * @return void
	 */
	public function process_bulk_actions(): void {
		if ( ! isset( $_GET['action'] ) || ! isset( $_GET['snapshot_ids'] ) ) {
			return;
		}

		// Verify nonce.
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'bulk-decaying_posts' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'content-decay-detector' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'content-decay-detector' ) );
		}

		$action       = sanitize_text_field( wp_unslash( $_GET['action'] ) );
		$snapshot_ids = array_map( 'absint', $_GET['snapshot_ids'] );

		foreach ( $snapshot_ids as $id ) {
			if ( 'mark_reviewed' === $action ) {
				$this->snapshot->mark_reviewed( $id );
			} elseif ( 'exclude' === $action ) {
				$this->snapshot->delete( $id );
			}
		}

		wp_safe_redirect( admin_url( 'tools.php?page=cdd-decay-report&bulk_action=done' ) );
		exit;
	}
}
