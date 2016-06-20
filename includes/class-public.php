<?php
/**
 * Recipe Box Public
 * Public-facing front-end display functions.
 *
 * @since NEXT
 * @package Recipe Box
 */

/**
 * Recipe Box Public.
 *
 * @since NEXT
 */
class RB_Public {
	/**
	 * Parent plugin class
	 *
	 * @var   class
	 * @since NEXT
	 */
	protected $plugin = null;

	/**
	 * Constructor
	 *
	 * @since  NEXT
	 * @param  object $plugin Main plugin object.
	 * @return void
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();
	}

	/**
	 * Initiate our hooks
	 *
	 * @since  NEXT
	 * @return void
	 */
	public function hooks() {
	}

	/**
	 * Returns an array of ingredients with units and type of units.
	 * @param  mixed $post_id The post ID (optional).
	 * @return array          The post meta.
	 */
	public function get_ingredients( $post_id = false ) {
		// Get the post ID.
		$post_id = ( $post_id && is_int( $post_id ) ) ? absint( $post_id ) : get_the_ID();

		// Return the ingredients.
		return get_post_meta( $post_id, '_rb_ingredients_group', true );
	}

	/**
	 * Returns an array of instructions (and instruction groups).
	 * @param  mixed $post_id The post ID (optional).
	 * @return array          The post meta.
	 */
	public function get_steps( $post_id = false ) {
		// Get the post ID.
		$post_id = ( $post_id && is_int( $post_id ) ) ? absint( $post_id ) : get_the_ID();

		// Return the preparation groups and steps.
		return get_post_meta( $post_id, '_rb_instructions_group', true );
	}

	/**
	 * Handles the markup for the ingredients.
	 * @param  mixed $post_id The post ID (optional).
	 * @return string         The markup for the recipe ingredients.
	 */
	public function render_ingredients( $post_id = false ) {
		// Get the post ID.
		$post_id = ( $post_id && is_int( $post_id ) ) ? absint( $post_id ) : get_the_ID();

		// Get the ingredients.
		$ingredients = $this->get_ingredients( $post_id );

		// Initialize the $output with an empty string.
		$output = '';

		// Check to make sure we have ingredients.
		if ( is_array( $ingredients ) && ! empty( $ingredients ) ) {
			$output = '<ul class="recipe-ingredients">';

			// Loop through the ingredients and display each one.
			foreach ( $ingredients as $ingredient ) {
				$item     = $ingredient['_rb_ingredients_product'];
				$unit     = $ingredient['_rb_ingredients_unit'];
				$quantity = $ingredient['_rb_ingredients_quantity'];

				$output .= '<li><span class="recipe-ingredient-quantity">' . esc_html( $quantity ) . '</span> ';
				$output .= '<span class="recipe-ingredient-unit">' . esc_html( $unit ) . '</span> ';
				$output .= '<span class="recipe-ingredient-item">' . esc_html( $item ) . '</span></li>';
			}

			$output .= '</ul> <!-- .recipe-ingredients -->';
		}

		return $output;
	}
}
