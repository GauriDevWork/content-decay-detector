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

		$table = new Report_Table( $this->snapshot, $this->settings );
		$table->prepare_items();
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Content Decay Report', 'content-decay-detector' ); ?></h1>
			<p><?php echo esc_html__( 'Posts flagged as decaying based on traffic drop threshold.', 'content-decay-detector' ); ?></p>
			<form method="get">
				<input type="hidden" name="page" value="cdd-decay-report" />
				<?php $table->display(); ?>
			</form>
		</div>
		<?php
	}
}
