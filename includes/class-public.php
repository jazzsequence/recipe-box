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
		add_filter( 'the_content', array( $this, 'append_to_the_content' ) );
	}

	/**
	 * Returns an array with preheat temperature and unit (farenheit or celcius).
	 *
	 * @param  mixed $post_id The post ID (optional).
	 * @return array          The post meta.
	 */
	public function get_preheat_temp( $post_id = false ) {
		// Get the post ID.
		$post_id = ( $post_id && is_int( $post_id ) ) ? absint( $post_id ) : get_the_ID();

		// Return the preheat temperature and units.
		$preheat_temp = get_post_meta( $post_id, '_rb_preheat_group', true );
		return ( $preheat_temp && isset( $preheat_temp[0] ) ) ? $preheat_temp[0] : false;
	}

	/**
	 * Returns an array of ingredients with units and type of units.
	 *
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
	 *
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
	 * Returns an array of cook times (prep, cook and total).
	 *
	 * @param  mixed $post_id The post ID (optional).
	 * @return array          An array of times.
	 */
	public function get_cook_time( $post_id = false ) {
		// Get the post ID.
		$post_id = ( $post_id && is_int( $post_id ) ) ? absint( $post_id ) : get_the_ID();

		$prep_time  = get_post_meta( $post_id, '_rb_prep_time', true );
		$cook_time  = get_post_meta( $post_id, '_rb_cook_time', true );
		$total_time = rb()->cpt->get_total_time( $post_id );

		return array(
			'prep_time'  => ( $prep_time ) ? $prep_time : '',
			'cook_time'  => ( $cook_time ) ? $cook_time : '',
			'total_time' => ( $total_time ) ? $total_time : '',
		);
	}

	/**
	 * Handles the markup for the preheat temperature.
	 *
	 * @param  mixed $post_id The post ID (optional).
	 * @return string         The markup for the preheat temp.
	 */
	public function render_preheat_temp( $post_id = false ) {
		// Get the post ID.
		$post_id = ( $post_id && is_int( $post_id ) ) ? absint( $post_id ) : get_the_ID();

		// Initialize the $output with an empty string.
		$output = '';

		// Get the preheat temp.
		$preheat_temp = $this->get_preheat_temp( $post_id );

		// Check to make sure we have preheat temp info.
		if ( $preheat_temp && isset( $preheat_temp['_rb_preheat_temp'] ) && isset( $preheat_temp['_rb_preheat_unit'] ) ) {
			// Do some sanitization of the data.
			$temp = absint( $preheat_temp['_rb_preheat_temp'] );
			$unit = ( in_array( $preheat_temp['_rb_preheat_unit'], [ 'farenheit', 'celcius' ], true ) ) ? ucfirst( $preheat_temp['_rb_preheat_unit'] ) : '';
			$output .= '<div class="recipe-preheat-temp">';
			$output .= '<h4 class="recipe-preheat-temp-heading">' . esc_html__( 'Preheat Temperature', 'recipe-box' ) . '</h4>';
			$output .= sprintf( '<p>%d° %s</p>', $temp, $unit );
			$output .= '</div> <!-- .recipe-preheat-temp -->';
		}

		return $output;
	}

	/**
	 * Handles the markup for the ingredients.
	 *
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

				$quantity = isset( $ingredient['_rb_ingredients_quantity'] ) ? $ingredient['_rb_ingredients_quantity'] : false;
				if ( $quantity ) {
					$output .= sprintf(
						'%s' . esc_html( $quantity ) . '%s',
						'<li><span class="recipe-ingredient-quantity">',
						'</span> '
					);
				}

				$unit     = ( 'none' !== $ingredient['_rb_ingredients_unit'] ) ? $ingredient['_rb_ingredients_unit'] : false;
				if ( $unit ) {
					$output .= sprintf(
						'%s' . esc_html( $unit ) . '%s',
						'<span class="recipe-ingredient-unit">',
						'</span> '
					);
				}

				$item     = isset( $ingredient['_rb_ingredients_product'] ) ? $ingredient['_rb_ingredients_product'] : false;
				if ( $item ) {
					$output .= sprintf(
						'%s' . esc_html( $item ) . '%s',
						'<span class="recipe-ingredient-item">',
						'</span>'
					);
				}

				$notes    = isset( $ingredient['_rb_ingredients_notes'] ) ? $ingredient['_rb_ingredients_notes'] : false;
				if ( $notes ) {
					$output .= sprintf(
						' %s' . esc_html( $notes ) . '%s',
						'<span class="recipe-ingredient-notes">',
						'</span>'
					);
				}

				$output .= '</li>';
			} // End foreach().

			$output .= '</ul> <!-- .recipe-ingredients -->';
		} // End if().

		return $output;
	}

	/**
	 * Handles the markup for recipe instructions.
	 *
	 * @param  mixed $post_id The post ID (optional).
	 * @return string         The markup for the recipe steps.
	 */
	public function render_steps( $post_id = false ) {
		// Get the post ID.
		$post_id = ( $post_id && is_int( $post_id ) ) ? absint( $post_id ) : get_the_ID();

		// Get the steps.
		$instruction_groups = $this->get_steps( $post_id );

		// Initialize the $output with an empty string.
		$output = '';

		if ( is_array( $instruction_groups ) && ! empty( $instruction_groups ) ) {

			// Loop through each group.
			foreach ( $instruction_groups as $instruction_group ) {
				$instructions_title = esc_html( $instruction_group['_rb_instructions_title'] );
				$instruction_group_slug = sanitize_title( $instructions_title );

				$steps = $instruction_group['content'];

				$output .= '<div class="recipe-instruction-group ' . $instruction_group_slug . '">';
				$output .= '<h3 class="instruction-heading">' . $instructions_title . '</h3>';
				$output .= '<ol class="' . $instruction_group_slug . '-steps">';

				// Within each group is a series of steps. Loop through each set of steps.
				foreach ( $steps as $step ) {
					$output .= sprintf(
						'%s' . wp_kses_post( $step ) . '%s',
						'<li class="recipe-step">',
						'</li>'
					);
				}

				$output .= '</ol> <!-- .' . $instruction_group_slug . '-steps -->';
				$output .= '</div> <!-- .recipe-instruction-group.' . $instruction_group_slug . ' -->';

			}
		}

		return $output;
	}

	/**
	 * Handles markup for cooking and preparation times.
	 *
	 * @param  mixed $post_id The post ID (optional).
	 * @return string         The cook time markup.
	 */
	public function render_cook_times( $post_id = false ) {
		// Get the post ID.
		$post_id = ( $post_id && is_int( $post_id ) ) ? absint( $post_id ) : get_the_ID();

		// Get the cook times.
		$times = $this->get_cook_time( $post_id );

		$output = '<div class="recipe-preparation-times"><p>';
		// Translators: %s is the preparation time value.
		$output .= ( '' !== $times['prep_time'] ) ? '<div class="prep-time">' . sprintf( esc_html__( 'Prep time: %s', 'recipe-box' ), rb()->cpt->calculate_hours_minutes( $times['prep_time'], 'string' ) ) . '</div> ' : '';
		// Translators: %s is the cooking time value.
		$output .= ( '' !== $times['cook_time'] ) ? '<div class="cook-time">' . sprintf( esc_html__( 'Cooking Time: %s', 'recipe-box' ), rb()->cpt->calculate_hours_minutes( $times['cook_time'], 'string' ) ) . '</div> ' : '';
		// Translators: %s is the total time it takes to cook the recipe.
		$output .= ( '' !== $times['total_time'] ) ? '<div class="total-time">' . sprintf( esc_html__( 'Total Time: %s', 'recipe-box' ), rb()->cpt->calculate_hours_minutes( $times['total_time'], 'string' ) ) . '</div>' : '';
		$output .= '</p></div> <!-- .recipe-preparation-times -->';

		return $output;
	}

	/**
	 * Handles echoing the recipe meta (ingredients and recipe steps).
	 *
	 * @param  mixed $post_id The post ID (optional).
	 */
	public function render_display( $post_id = false ) {
		// Get the post ID.
		$post_id = ( $post_id && is_int( $post_id ) ) ? absint( $post_id ) : get_the_ID();

		// Get the preheat temperature.
		$preheat_temp = $this->render_preheat_temp( $post_id );

		// Get the cook times.
		$cook_times = $this->render_cook_times( $post_id );

		// Get the ingredients.
		$ingredients = $this->render_ingredients( $post_id );

		// Get the steps.
		$steps = $this->render_steps( $post_id );

		return $cook_times . $preheat_temp . $ingredients . $steps;
	}

	/**
	 * Filter the_content to add recipe instructions to the bottom of recipe posts.
	 *
	 * @param  string $content The post content.
	 * @return string          The updated post content.
	 */
	public function append_to_the_content( $content ) {
		if ( is_singular( 'rb_recipe' ) ) {
			$content = $content . $this->render_display( get_the_ID() );
		}

		return $content;
	}
}
