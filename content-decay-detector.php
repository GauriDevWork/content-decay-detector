<?php
/**
 * Plugin Name: Content Decay Detector
 * Plugin URI:  https://github.com/GauriDevWork/content-decay-detector
 * Description: Detects decaying WordPress content by tracking traffic snapshots, scoring posts, and suggesting actionable fixes before rankings drop.
 * Version:     0.1.0
 * Author:      Gauri
 * Author URI:  https://github.com/GauriDevWork
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: content-decay-detector
 * Requires at least: 6.0
 * Requires PHP: 8.0
 */

defined( 'ABSPATH' ) || exit;

// Plugin constants.
define( 'CDD_VERSION', '0.1.0' );
define( 'CDD_PLUGIN_FILE', __FILE__ );
define( 'CDD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CDD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Autoloader.
if ( file_exists( CDD_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
    require_once CDD_PLUGIN_DIR . 'vendor/autoload.php';
}

/**
 * Bootstrap the plugin.
 *
 * Hooked late on plugins_loaded so all WordPress APIs are available.
 *
 * @return void
 */
function cdd_init(): void {
    \ContentDecayDetector\Plugin::get_instance();
}
add_action( 'plugins_loaded', 'cdd_init' );