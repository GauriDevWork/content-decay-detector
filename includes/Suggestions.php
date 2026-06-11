<?php
/**
 * Suggestion generator.
 *
 * @package ContentDecayDetector
 */

namespace ContentDecayDetector;

// phpcs:disable WordPress.Files.FileName.InvalidClassFileName, WordPress.Files.FileName.NotHyphenatedLowercase

defined( 'ABSPATH' ) || exit;

/**
 * Figures out what's wrong with a decaying post and suggests fixes.
 *
 * Each private method checks one condition. Add new rules here as
 * the detection logic grows.
 */
class Suggestions {

	/**
	 * The post ID.
	 *
	 * @var int
	 */
	private int $post_id;

	/**
	 * The decay score for this post.
	 *
	 * @var int
	 */
	private int $decay_score;

	/**
	 * Settings instance.
	 *
	 * @var Settings
	 */
	private Settings $settings;

	/**
	 * Constructor.
	 *
	 * @param int      $post_id     The post ID.
	 * @param int      $decay_score The calculated decay score.
	 * @param Settings $settings    Settings instance.
	 */
	public function __construct( int $post_id, int $decay_score, Settings $settings ) {
		$this->post_id     = $post_id;
		$this->decay_score = $decay_score;
		$this->settings    = $settings;
	}

	/**
	 * Generate suggestions for this post.
	 *
	 * Runs all rules and returns an array of suggestion strings.
	 *
	 * @return array Array of suggestion strings.
	 */
	public function generate(): array {
		$suggestions = array();

		if ( $this->is_title_stale() ) {
			$suggestions[] = 'Refresh the post title with current keywords.';
		}

		if ( $this->is_content_old() ) {
			$suggestions[] = 'Update statistics and outdated information in the content.';
		}

		if ( $this->has_no_internal_links() ) {
			$suggestions[] = 'Add internal links to related posts.';
		}

		if ( $this->has_no_images() ) {
			$suggestions[] = 'Add images or media to improve engagement.';
		}

		if ( $this->is_content_short() ) {
			$suggestions[] = 'Expand content — post is below 600 words.';
		}

		return $suggestions;
	}

	/**
	 * Check if the post title has not been updated recently.
	 *
	 * @return bool True if title may be stale.
	 */
	private function is_title_stale(): bool {
		$post = get_post( $this->post_id );
		if ( ! $post ) {
			return false;
		}
		$modified     = strtotime( $post->post_modified );
		$one_year_ago = strtotime( '-1 year' );
		return $modified < $one_year_ago && $this->decay_score < $this->settings->get_threshold();
	}

	/**
	 * Check if the post content has not been updated in over a year.
	 *
	 * @return bool True if content is old.
	 */
	private function is_content_old(): bool {
		$post = get_post( $this->post_id );
		if ( ! $post ) {
			return false;
		}
		$modified = strtotime( $post->post_modified );
		return $modified < strtotime( '-1 year' );
	}

	/**
	 * Check if the post has no internal links in its content.
	 *
	 * @return bool True if no internal links found.
	 */
	private function has_no_internal_links(): bool {
		$post = get_post( $this->post_id );
		if ( ! $post ) {
			return false;
		}
		$home_url = home_url();
		return strpos( $post->post_content, $home_url ) === false;
	}

	/**
	 * Check if the post has no images.
	 *
	 * @return bool True if no images found in content.
	 */
	private function has_no_images(): bool {
		$post = get_post( $this->post_id );
		if ( ! $post ) {
			return false;
		}
		return strpos( $post->post_content, '<img' ) === false;
	}

	/**
	 * Check if the post content is too short.
	 *
	 * @return bool True if content is under 600 words.
	 */
	private function is_content_short(): bool {
		$post = get_post( $this->post_id );
		if ( ! $post ) {
			return false;
		}
		$word_count = str_word_count( wp_strip_all_tags( $post->post_content ) );
		return $word_count < 600;
	}
}
