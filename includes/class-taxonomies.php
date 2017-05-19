<?php
/**
 * Recipe Taxonomies
 *
 * @since 0.1
 * @package Recipe Box
 */

require_once dirname( __FILE__ ) . '/../vendor/taxonomy-core/Taxonomy_Core.php';

/**
 * Taxonomies class.
 *
 * @see https://github.com/WebDevStudios/Taxonomy_Core
 * @since 0.1
 */
class RB_Taxonomies {
	/**
	 * Parent plugin class
	 *
	 * @var class
	 * @since  0.1
	 */
	protected $plugin = null;

	/**
	 * Constructor
	 *
	 * @since 0.1
	 * @param  object $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();
	}

	/**
	 * Initiate our hooks
	 *
	 * @since 0.1
	 */
	public function hooks() {
		add_action( 'init', array( $this, 'register_taxonomies' ), 4 );
	}

	/**
	 * Register Taxonomy. See documentation in Taxonomy_Core, and in wp-includes/taxonomy.php
	 *
	 * @since 0.1
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

	public function get_the_recipe_terms( $post = false, $tax = 'recipe_category' ) {
		// Check for an error.
		if ( is_wp_error( $post ) ) {
			return ( $post instanceof WP_Error );
		}

		// Get the post ID.
		if ( $post && is_int( $post ) ) {
			$post_id = absint( $post );
		} elseif ( $post && is_object( $post ) ) {
			$post_id = $post->ID;
		} else {
			$post_id = get_the_ID();
		}

		return get_the_terms( $post, $tax );
	}

	public function recipe_terms( $post = false, $tax = 'rb_recipe_category', $separator = ', ' ) {
		// Bail if no post was passed.
		if ( ! $post ) {
			return;
		}

		$terms = $this->get_the_recipe_terms( $post, $tax );
		$taxonomy = get_taxonomy( $tax );

		$i = 1;

		$output = '';

		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$separator = ( count( $terms > $i ) ) ? $separator : '';

				$output .= sprintf( '<a href="%1$s">%2$s</a>',
					get_term_link( $term, $tax ),
					esc_html( $term->name )
				);

			}

			// Translators: %s is a comma-separated list of categories.
			$output = sprintf( __( 'Recipe Category: %s', 'recipe-box' ), $output );
		}

		return apply_filters( 'rb_filter_recipe_' . $tax . '_terms', $output, $terms );
	}
}
