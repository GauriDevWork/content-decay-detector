<?php
/**
 * Plugin installer class.
 *
 * @package ContentDecayDetector
 */

namespace ContentDecayDetector;

defined( 'ABSPATH' ) || exit;

/**
 * Class Installer
 *
 * Handles plugin activation tasks: creates database tables
 * and sets default plugin options.
 */
class Installer {

    /**
     * Run the installer.
     *
     * Called on plugin activation hook.
     *
     * @return void
     */
    public static function run(): void {
        error_log( 'CDD: run function fired' );
        self::create_tables();
        self::set_default_options();
       
    }

    /**
     * Create custom database tables.
     *
     * @return void
     */
    private static function create_tables(): void {
        // Database table creation will be added on Day 3.
    }

    /**
     * Set default plugin options on first activation.
     *
     * @return void
     */
    private static function set_default_options(): void {
        if ( ! get_option( 'cdd_version' ) ) {
            update_option( 'cdd_version', CDD_VERSION );
        }
    }
}