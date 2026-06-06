<?php
/**
 * Block editor integration.
 *
 * @package ContentDecayDetector
 */

namespace ContentDecayDetector\Admin;

// phpcs:disable WordPress.Files.FileName.InvalidClassFileName, WordPress.Files.FileName.NotHyphenatedLowercase

defined( 'ABSPATH' ) || exit;

/**
 * Class Block_Editor
 *
 * Enqueues the Gutenberg sidebar panel script in the block editor.
 */
class Block_Editor {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Enqueue block editor assets.
	 *
	 * @return void
	 */
	public function enqueue_assets(): void {
		$asset_file = CDD_PLUGIN_DIR . 'build/index.asset.php';

		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset = require $asset_file;

		wp_enqueue_script(
			'cdd-block-editor',
			CDD_PLUGIN_URL . 'build/index.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);
	}
}
