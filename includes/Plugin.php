<?php
/**
 * Main plugin class.
 *
 * @package ContentDecayDetector
 */

namespace ContentDecayDetector;

defined( 'ABSPATH' ) || exit;

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
        register_activation_hook( CDD_PLUGIN_FILE, [ $this, 'activate' ] );
        register_deactivation_hook( CDD_PLUGIN_FILE, [ $this, 'deactivate' ] );
    }

    /**
     * Runs on plugin activation.
     *
     * @return void
     */
    public function activate(): void {
        Installer::run();
        error_log( 'CDD: Installer class loaded via PSR-4 autoloader.' );
    }

    /**
     * Runs on plugin deactivation.
     *
     * @return void
     */
    public function deactivate(): void {
        // Future: clear scheduled cron events.
    }
}