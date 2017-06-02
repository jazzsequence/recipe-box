<?php
/**
 * Recipe Box Import.
 *
 * Class for syndicating/importing recipes from a remote Recipe Box site.
 *
 * @since   0.2
 * @package Recipe_Box
 */

/**
 * Recipe Box Import.
 *
 * @since 0.2
 */
class RB_Import {
	/**
	 * Parent plugin class.
	 *
	 * @since 0.2
	 *
	 * @var   Recipe_Box
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
	 * @since  0.2
	 *
	 * @param  Recipe_Box $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  0.2
	 */
	public function hooks() {

	}
}
