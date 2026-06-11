<?php
/**
 * Main plugin class.
 *
 * @package ContentDecayDetector
 */

namespace ContentDecayDetector;

defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.Files.FileName.InvalidClassFileName, WordPress.Files.FileName.NotHyphenatedLowercase
/**
 * Main plugin class.
 *
 * Wires everything together — admin pages, scanner, REST API, and email.
 * Only one instance ever exists thanks to the singleton pattern.
 */
class Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static ?Plugin $instance = null;

	/**
	 * Get or create the singleton instance.
	 *
	 * @return Plugin
	 */
	public static function get_instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor. Registers activation and deactivation hooks.
	 */
	private function __construct() {
		register_activation_hook( CDD_PLUGIN_FILE, array( $this, 'activate' ) );
		register_deactivation_hook( CDD_PLUGIN_FILE, array( $this, 'deactivate' ) );
		$this->maybe_install();
		$this->register_admin();
		$this->register_scanner();
		$this->register_rest_api();
		$this->register_email();
	}

	/**
	 * Runs on plugin activation.
	 *
	 * @return void
	 */
	public function activate(): void {
		Installer::run();
		Scanner::schedule();
	}

	/**
	 * Runs on plugin deactivation.
	 *
	 * @return void
	 */
	public function deactivate(): void {
		Scanner::unschedule();
	}

	/**
	 * Safety net for environments where the activation hook doesn't fire.
	 *
	 * Checks for the DB table on every load and creates it if missing.
	 *
	 * @return void
	 */
	public function maybe_install(): void {
		if ( ! Installer::table_exists() ) {
			Installer::run();
		}
	}


	/**
	 * Boot all admin-facing components.
	 *
	 * Settings page, report table, dashboard widget, and block editor panel.
	 *
	 * @return void
	 */
	private function register_admin(): void {
		$settings_page = new \ContentDecayDetector\Admin\Settings_Page();
		$settings_page->register();
		$report_page = new \ContentDecayDetector\Admin\Report_Page(
			new PostSnapshot(),
			new Settings()
		);
		$report_page->register();
		$dashboard_widget = new \ContentDecayDetector\Admin\Dashboard_Widget(
			new PostSnapshot(),
			new Settings()
		);
		$dashboard_widget->register();
		$block_editor = new \ContentDecayDetector\Admin\Block_Editor();
		$block_editor->register();
	}

	/**
	 * Register the scanner and cron hooks.
	 *
	 * @return void
	 */
	private function register_scanner(): void {
		$scanner = new Scanner( new Settings(), new PostSnapshot() );
		$scanner->register();
	}

	/**
	 * Register REST API endpoints.
	 *
	 * @return void
	 */
	private function register_rest_api(): void {
		$reports = new \ContentDecayDetector\REST\Reports(
			new PostSnapshot(),
			new Settings()
		);
		$reports->register();
	}

	/**
	 * Register email digest hooks.
	 *
	 * @return void
	 */
	private function register_email(): void {
		$email = new Email( new PostSnapshot(), new Settings() );
		$email->register();
	}
}
