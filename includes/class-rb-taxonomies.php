<?php
/**
 * Recipe Taxonomies
 *
 * @since NEXT
 * @package Recipe Box
 */

require_once dirname( __FILE__ ) . '/../vendor/taxonomy-core/Taxonomy_Core.php';

/**
 * Taxonomies class.
 *
 * @see https://github.com/WebDevStudios/Taxonomy_Core
 * @since NEXT
 */
class RB_Taxonomies {
	/**
	 * Parent plugin class
	 *
	 * @var class
	 * @since  NEXT
	 */
	protected $plugin = null;

	/**
	 * Constructor
	 *
	 * @since NEXT
	 * @param  object $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();
	}

	/**
	 * Initiate our hooks
	 *
	 * @since NEXT
	 */
	public function hooks() {
	}
}
