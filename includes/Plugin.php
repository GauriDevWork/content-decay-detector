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
 * Class Plugin
 *
 * Bootstraps the plugin. Registers activation, deactivation hooks
 * and loads all components via a single entry point.
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
	 * Run installer if table doesn't exist yet.
	 *
	 * Handles cases where activation hook doesn't fire correctly.
	 *
	 * @return void
	 */
	public function maybe_install(): void {
		if ( ! Installer::table_exists() ) {
			Installer::run();
		}
	}


	/**
	 * Register admin pages and settings.
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
