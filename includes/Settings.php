<?php
/**
 * Plugin settings model.
 *
 * @package ContentDecayDetector
 */

namespace ContentDecayDetector;

defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.Files.FileName.InvalidClassFileName, WordPress.Files.FileName.NotHyphenatedLowercase
/**
 * Class Settings
 *
 * Wraps all get_option() and update_option() calls for this plugin.
 * No other class should ever call get_option( 'cdd_...' ) directly.
 */
class Settings {

	/**
	 * Get the decay threshold percentage.
	 *
	 * Posts that have lost more than this percentage of traffic are flagged.
	 *
	 * @return int Threshold percentage between 1 and 100. Default 30.
	 */
	public function get_threshold(): int {
		return (int) get_option( 'cdd_decay_threshold', 30 );
	}

	/**
	 * Get the scan frequency setting.
	 *
	 * @return string One of: daily, weekly, monthly. Default weekly.
	 */
	public function get_scan_frequency(): string {
		return (string) get_option( 'cdd_scan_frequency', 'weekly' );
	}

	/**
	 * Check if email notifications are enabled.
	 *
	 * @return bool True if enabled, false otherwise.
	 */
	public function is_email_enabled(): bool {
		return (bool) get_option( 'cdd_email_notifications', true );
	}

	/**
	 * Get the plugin version stored in options.
	 *
	 * @return string Version string. Default empty string.
	 */
	public function get_version(): string {
		return (string) get_option( 'cdd_version', '' );
	}

	/**
	 * Update the decay threshold.
	 *
	 * @param int $value New threshold value.
	 *
	 * @return bool True if updated successfully.
	 */
	public function update_threshold( int $value ): bool {
		return update_option( 'cdd_decay_threshold', max( 1, min( 100, $value ) ) );
	}

	/**
	 * Update the scan frequency.
	 *
	 * @param string $value One of: daily, weekly, monthly.
	 *
	 * @return bool True if updated successfully.
	 */
	public function update_scan_frequency( string $value ): bool {
		$allowed = array( 'daily', 'weekly', 'monthly' );
		if ( ! in_array( $value, $allowed, true ) ) {
			return false;
		}
		return update_option( 'cdd_scan_frequency', $value );
	}

	/**
	 * Update email notifications toggle.
	 *
	 * @param bool $value True to enable, false to disable.
	 *
	 * @return bool True if updated successfully.
	 */
	public function update_email_enabled( bool $value ): bool {
		return update_option( 'cdd_email_notifications', $value );
	}
}
