<?php
/**
 * Plugin Name: Decaywatcher
 * Plugin URI:  https://github.com/GauriDevWork/content-decay-detector
 * Description: Detects decaying WordPress content by tracking traffic snapshots, scoring posts, and suggesting actionable fixes before rankings drop.
 * Version:     0.1.0
 * Author:      Gauri Kaushik
 * Author URI:  https://profiles.wordpress.org/gauri87/
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: decaywatcher
 * Requires at least: 6.0
 * Requires PHP: 8.0
 *
 * @package ContentDecayDetector
 */

defined( 'ABSPATH' ) || exit;

// Plugin constants.
define( 'WDCY_VERSION', '0.1.0' );
define( 'WDCY_PLUGIN_FILE', __FILE__ );
define( 'WDCY_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WDCY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Autoloader.
if ( file_exists( WDCY_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
	require_once WDCY_PLUGIN_DIR . 'vendor/autoload.php';
}

/**
 * Runs on plugin activation.
 *
 * Fires before plugins_loaded so must be registered here, not inside Plugin class.
 *
 * @return void
 */
function wdcy_activate(): void {
	\ContentDecayDetector\Installer::run();
	\ContentDecayDetector\Scanner::schedule();
}

/**
 * Runs on plugin deactivation.
 *
 * @return void
 */
function wdcy_deactivate(): void {
	\ContentDecayDetector\Scanner::unschedule();
}

register_activation_hook( __FILE__, 'wdcy_activate' );
register_deactivation_hook( __FILE__, 'wdcy_deactivate' );

/**
 * Bootstrap the plugin.
 *
 * Hooked late on plugins_loaded so all WordPress APIs are available.
 *
 * @return void
 */
function wdcy_init(): void {
	\ContentDecayDetector\Plugin::get_instance();
}

add_action( 'plugins_loaded', 'wdcy_init' );