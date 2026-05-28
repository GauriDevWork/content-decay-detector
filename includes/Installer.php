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
        self::create_tables();
        self::set_default_options();
    }

    /**
     * Create custom database tables.
     *
     * Uses dbDelta() which is safe to run multiple times —
     * it only creates or updates, never drops or overwrites data.
     *
     * @return void
     */
    private static function create_tables(): void {
        global $wpdb;

        $table_name      = $wpdb->prefix . 'decay_snapshots';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            post_id BIGINT(20) UNSIGNED NOT NULL,
            snapshot_date DATE NOT NULL,
            traffic_score INT(11) DEFAULT NULL,
            decay_score TINYINT(3) UNSIGNED DEFAULT NULL,
            suggestions LONGTEXT DEFAULT NULL,
            is_reviewed TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY post_id (post_id),
            KEY snapshot_date (snapshot_date)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
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

    /**
     * Check if the database table exists.
     *
     * @return bool
     */
    public static function table_exists(): bool {
        global $wpdb;
        $table_name = $wpdb->prefix . 'decay_snapshots';
        return $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) === $table_name;
    }
}