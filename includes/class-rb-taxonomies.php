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
class RB_Taxonomies extends Taxonomy_Core {
	/**
	 * Parent plugin class
	 *
	 * @var class
	 * @since  NEXT
	 */
	protected $plugin = null;

	/**
	 * Constructor
	 * Register Taxonomy. See documentation in Taxonomy_Core, and in wp-includes/taxonomy.php
	 *
	 * @since NEXT
	 * @param  object $plugin Main plugin object.
	 * @return void
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();

		// Register this taxonomy
		// First parameter should be an array with Singular, Plural, and Registered name
		// Second parameter is the register taxonomy arguments
		// Third parameter is post types to attach to.
		parent::__construct(
			array( __( 'Singular', 'recipe-box' ), __( 'Plural', 'recipe-box' ), 'slug' ),
			array( 'hierarchical' => false ),
			array( 'rb_recipe' )
		);
	}

	/**
	 * Initiate our hooks
	 *
	 * @since NEXT
	 * @return void
	 */
	public function hooks() {
	}
}
