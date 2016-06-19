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
		add_action( 'init', array( $this, 'register_taxonomies' ), 4 );
	}

	/**
	 * Register Taxonomy. See documentation in Taxonomy_Core, and in wp-includes/taxonomy.php
	 *
	 * @since NEXT
	 */
	public function register_taxonomies() {
		// Recipe Category.
		register_via_taxonomy_core( array(
				__( 'Recipe Category', 'recipe-box' ),   // Singular.
				__( 'Recipe Categories', 'recipe-box' ),  // Plural.
				'rb_recipe_category',                    // Registered name.
			),
			array(),             // Array of taxonomy arguments.
			array( 'rb_recipe' ) // Array of post types.
		);

		// Meal Types.
		register_via_taxonomy_core( array(
				__( 'Meal Type', 'recipe-box' ),  // Singular.
				__( 'Meal Types', 'recipe-box' ), // Plural.
				'rb_meal_type',                   // Registered name.
			),
			array(),             // Array of taxonomy arguments.
			array( 'rb_recipe' ) // Array of post types.
		);

		// Recipe Cuisines.
		register_via_taxonomy_core( array(
				__( 'Cuisine', 'recipe-box' ),  // Singular.
				__( 'Cuisines', 'recipe-box' ), // Plural.
				'rb_recipe_cuisine',            // Registered name.
			),
			array(),             // Array of taxonomy arguments.
			array( 'rb_recipe' ) // Array of post types.
		);
	}
}
