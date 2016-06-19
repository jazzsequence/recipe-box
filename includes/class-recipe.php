<?php
/**
 * Recipe CPT
 *
 * @since NEXT
 * @package Recipe Box
 */

require_once dirname(__FILE__) . '/../vendor/cpt-core/CPT_Core.php';
require_once dirname(__FILE__) . '/../vendor/cmb2/init.php';

/**
 * Recipe Box RB_Recipe post type class.
 *
 * @see https://github.com/WebDevStudios/CPT_Core
 * @since NEXT
 */
class RB_Recipe extends CPT_Core {
	/**
	 * Parent plugin class
	 *
	 * @var class
	 * @since  NEXT
	 */
	protected $plugin = null;

	/**
	 * Constructor
	 * Register Custom Post Types. See documentation in CPT_Core, and in wp-includes/post.php
	 *
	 * @since  NEXT
	 * @param  object $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();

		// Register this cpt
		// First parameter should be an array with Singular, Plural, and Registered name.
		parent::__construct(
			array( __( 'Recipe', 'recipe-box' ), __( 'Recipes', 'recipe-box' ), 'rb_recipe' ),
			array(
				'supports' => array( 'title', 'editor', 'thumbnail' ),
				'menu_icon' => 'dashicons-carrot',
			)
		);
	}

	/**
	 * Initiate our hooks
	 *
	 * @since  NEXT
	 */
	public function hooks() {
		add_action( 'cmb2_init', array( $this, 'fields' ) );
		// add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}


	/**
	 * Enqueue admin javascript. Mostly for autocompletion.
	 *
	 * @param  string $hook The current admin page.
	 * @return void
	 */
	public function admin_enqueue_scripts( $hook ) {
		global $post;

		// Bail if we aren't editing a post.
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ) ) ) {
			return;
		}

		// Bail if we aren't editing a recipe.
		if ( 'rb_recipe' !== $post->post_type ) {
			return;
		}

		$min = '.min';

		// Don't use minified js/css if DEBUG is on.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$min = '';
		}

		wp_enqueue_script( 'jquery-ui-autocomplete' );
		wp_enqueue_script( 'recipes', wdscm()->url . 'assets/js/recipes' . $min . '.js', array( 'jquery' ), wdscm()->version, true );
		wp_enqueue_style( 'recipes', wdscm()->url . 'assets/css/recipes' . $min . '.css', array(), wdscm()->version, 'screen' );
		wp_localize_script( 'recipes', 'recipes', array(
			'autosuggest' => $this->autosuggest_terms(),
			'wp_debug'    => ( defined( 'WP_DEBUG' ) && WP_DEBUG ),
		) );
	}

	/**
	 * Get an array of unique ingredient names using the WP-API.
	 * @todo         This whole thing needs to be edited to use post meta instead of an ingredient cpt.
	 * @return array An array of ingredient names (post titles).
	 */
	public function autosuggest_terms() {
		// Get the ingredients from the WP-API.
		$request = wp_remote_get( home_url( '/wp-json/wp/v2/ingredients?filter[posts_per_page]=1000' ) );

		if ( $request && ! is_wp_error( $request ) ) {
			// Decode the json.
			$results = json_decode( $request['body'] );

			// Build an array of ingredient names.
			foreach ( $results as $ingredient ) {
				$ingredient_list[] = $ingredient->title->rendered;
			}

			// Strip out any duplicate ingredients and return.
			return array_unique( $ingredient_list );
		}

		return new WP_Error( 'rb_ingredients_remote_get_fail', __( 'WordPress remote get operation failed.', 'recipe-box' ), $request );
	}

	/**
	 * Register the CMB2 fields and metaboxes.
	 */
	public function fields() {
		$prefix = '_rb_';

		$this->recipe_meta( $prefix );
		$this->instructions( $prefix . 'instructions_' );
		$this->ingredients( $prefix . 'ingredients_' );
	}


	/**
	 * Handles the Recipe Information CMB2 box.
	 * @param  string $prefix The meta prefix.
	 */
	private function recipe_meta( $prefix ) {

		$post_id = isset( $_GET['post'] ) ? absint( esc_attr( $_GET['post'] ) ) : false;

		$cmb = new_cmb2_box( array(
			'id'           => $prefix . 'info_metabox',
			'title'        => __( 'Recipe Information', 'recipe-box' ),
			'object_types' => array( 'rb_recipe' ),
			'classes'      => 'recipe-meta',
		) );

		$cmb->add_field( array(
			'name'       => __( 'Servings', 'recipe-box' ),
			'id'         => $prefix . 'servings',
			'type'       => 'text_small',
			'default'    => '2',
		) );

		$cmb->add_field( array(
			'name'       => __( 'Preparation Time', 'recipe-box' ),
			'id'         => $prefix . 'prep_time',
			'type'       => 'text_small',
			'desc'       => __( 'minutes<br>Time to prepare the recipe.', 'recipe-box' ),
		) );

		$cmb->add_field( array(
			'name'       => __( 'Cook Time', 'recipe-box' ),
			'id'         => $prefix . 'cook_time',
			'type'       => 'text_small',
			'desc'       => __( 'minutes<br>Time to cook the recipe.', 'recipe-box' ),
		) );

		$cmb->add_field( array(
			'name'       => __( 'Total Time (optional)', 'recipe-box' ),
			'id'         => $prefix . 'total_time',
			'type'       => 'text_small',
			'desc'       => __( 'minutes<br>The total time to prepare the recipe. (Defaults to Prep Time + Cook Time. Change if that is not accurate.', 'recipe-box' ),
			'default'    => ( $post_id ) ? $this->get_total_time( $post_id ) : '',
		) );
	}


	/**
	 * Handles the recipe instructions metabox.
	 * @param  string $prefix The post meta key prefix.
	 */
	private function instructions( $prefix ) {
		$cmb = new_cmb2_box( array(
			'id'           => $prefix . 'metabox',
			'title'        => __( 'Preparation', 'recipe-box' ),
			'object_types' => array( 'rb_recipe' ),
			'classes'      => 'preparation',
		) );

		$group_field_id = $cmb->add_field( array(
			'id'          => $prefix . 'group',
			'type'        => 'group',
			'description' => __( 'Add the instructions for the recipe. Instructions can be divided up into multiple groups of steps (e.g. Batter Instructions, Filling Instructions).', 'recipe-box' ),
			'options'     => array(
				'group_title'   => __( 'Preparation Group {#}', 'recipe-box' ),
				'add_button'    => __( 'Add another group', 'recipe-box' ),
				'remove_button' => __( 'Remove group', 'recipe-box' ),
				'sortable'      => true,
			),
		) );

		$cmb->add_group_field( $group_field_id, array(
			'name'       => __( 'Title', 'recipe-box' ),
			'id'         => $prefix . 'title',
			'type'       => 'text',
			'desc'       => __( 'The title you want to appear above your preparation instructions in this group.', 'recipe-box' ),
			'default'    => __( 'Instructions', 'recipe-box' ),
		) );

		$cmb->add_group_field( $group_field_id, array(
			'name'        => __( 'Steps', 'recipe-box' ),
			'id'          => 'content',
			'desc'        => __( 'Click "Add another step" to add more steps to this instruction group.', 'recipe-box' ),
			'type'        => 'textarea_small',
			'options'     => array(
				'add_row_text' => __( 'Add another step', 'recipe-box' ),
			),
			'repeatable'  => true,
		) );
	}


	/**
	 * The ingredients list metabox.
	 * @param  string $prefix The post metakey prefix.
	 */
	private function ingredients( $prefix ) {
		$cmb = new_cmb2_box( array(
			'id'           => $prefix . 'metabox',
			'title'        => __( 'Ingredients', 'recipe-box' ),
			'object_types' => array( 'rb_recipe' ),
			'show_names'   => true,
			'classes'      => 'ingredients',
		) );

		$group_field_id = $cmb->add_field( array(
			'id'          => $prefix . 'group',
			'type'        => 'group',
			'description' => __( 'Add the ingredients for this recipe from the database of available products. For each ingredient, start typing in the Ingredient box. Your ingredient will be automatically matched to an existing ingredient or you can enter a custom ingredient.', 'recipe-box' ),
			'options'     => array(
				'group_title'   => __( 'Ingredient {#}', 'recipe-box' ),
				'add_button'    => __( 'Add another ingredient', 'recipe-box' ),
				'remove_button' => __( 'Remove ingredient', 'recipe-box' ),
				'sortable'      => true,
			),
		) );

		$cmb->add_group_field( $group_field_id, array(
			'name'        => __( 'Quantity', 'recipe-box' ),
			'description' => __( 'How many units of this ingredient?', 'recipe-box' ),
			'id'          => $prefix . 'quantity',
			'type'        => 'text_small',
			'attributes'  => array( 'type' => 'number' ),
		) );

		$cmb->add_group_field( $group_field_id, array(
			'name'        => __( 'Unit of measurement', 'recipe-box' ),
			'description' => __( '(2 Tbsp, 3 cups, 1 handful, etc.)' ),
			'id'          => $prefix . 'unit',
			'type'        => 'select',
			'options'     => $this->get_units(),
			'attributes'  => array(
				'data-placeholder' => __( 'Select unit of measurement.', 'recipe-box' ),
			),
		) );

		$cmb->add_group_field( $group_field_id, array(
			'name'        => __( 'Ingredient', 'recipe-box' ),
			'id'          => $prefix . 'product',
			'type'        => 'text',
			'desc'        => __( 'Enter the ingredient name.', 'recipe-box' ),
			'attributes'  => array( 'class' => 'ingredient' ),
		) );
	}


	/**
	 * Return various units of measurement.
	 * @return array Different units of measurement that could be used in a recipe.
	 */
	private function get_units() {
		return array(
			'none'    => '&mdash;',
			'cup'     => __( 'cup(s)', 'recipe-box' ),
			'tbsp'    => __( 'tablespoon(s)', 'recipe-box' ),
			'tsp'     => __( 'teaspoon(s)', 'recipe-box' ),
			'oz'      => __( 'ounce(s)', 'recipe-box' ),
			'gram'    => __( 'gram(s)', 'recipe-box' ),
			'piece'   => __( 'piece(s)', 'recipe-box' ),
			'quart'   => __( 'quart(s)', 'recipe-box' ),
			'gallon'  => __( 'gallon(s)', 'recipe-box' ),
			'half'    => __( 'halves', 'recipe-box' ),
			'can'     => __( 'can(s)', 'recipe-box' ),
			'package' => __( 'package(s)', 'recipe-box' ),
			'sprig'   => __( 'sprig(s)', 'recipe-box' ),
			'dash'    => __( 'dash(es)', 'recipe-box' ),
			'drop'    => __( 'drop(s)', 'recipe-box' ),
			'bunch'   => __( 'bunch(es)', 'recipe-box' ),
			'hand'    => __( 'handful(s)', 'recipe-box' ),
			'splash'  => __( 'splash(es)', 'recipe-box' ),
			'pinch'   => __( 'pinch(es)', 'recipe-box' ),
		);
	}

	/**
	 * Helper function to calculate the total time based on prep time and cook time.
	 * @param  int $post_id The post ID.
	 * @return int          The total time calculation.
	 */
	public function get_total_time( $post_id ) {
		$total_time = get_post_meta( $post_id, '_rb_total_time', true );
		if ( '' == $total_time || ! $total_time ) {
			$total_time = get_post_meta( $post_id, '_rb_prep_time', true ) + get_post_meta( $post_id, '_rb_cook_time', true );
		}

		return $total_time;
	}

	/**
	 * Function to calculate time in HH:MM from time stored only in minutes.
	 * @param  integer $time_in_minutes Time in minutes.
	 * @param  string  $format          The desired format of the calculated time.
	 *         Accepted possibilities are:
	 *         'hh:mm' or 'HH:MM'        Time in hours and minutes, e.g. 4:30.
	 *         'array'                   Returns an array of hours and minutes.
	 *         'string'                  Returns the time in plain english.
	 * @return mixed                     Time in HH:MM (default) or whatever format was passed.
	 */
	public function calculate_hours_minutes( $time_in_minutes, $format = 'hh:mm' ) {
		// Store hours and minutes in an array.
		$time = array(
			'hours'   => intval( $time_in_minutes / 60 ),
			'minutes' => $time_in_minutes - ( $hours * 60 ),
		);

		// Check the format. If we want the time in HH:MM format, return that.
		if ( in_array( $format, array( 'hh:mm', 'HH:MM' ) ) ) {
			return ( $time['hours'] >= 1 ) ? sprintf( '%d:%d', $time['hours'], $time['minutes'] ) : $time['minutes'];
		}

		// ...but maybe we want to do something like "4 hours and 20 minutes", or manipulate the format manually. In that case we can just return the array of hours/minutes.
		if ( 'array' == $format ) {
			return $time;
		}

		// We can also use this array to return the time in plain text.
		if ( 'string' == $format ) {
			return ( $time['hours'] >= 1 ) ? sprintf( __( '%d hours and %d minutes', 'recipe-box' ), $time['hours'], $time['minutes'] ) : sprintf( __( '%d minutes', 'recipe-box' ), $time['minutes'] );
		}
	}

	/**
	 * Registers admin columns to display. Hooked in via CPT_Core.
	 *
	 * @since  NEXT
	 * @param  array $columns Array of registered column names/labels.
	 * @return array          Modified array
	 */
	public function columns( $columns ) {
		$new_column = array();
		return array_merge( $new_column, $columns );
	}

	/**
	 * Handles admin column display. Hooked in via CPT_Core.
	 *
	 * @since  NEXT
	 * @param array $column  Column currently being rendered.
	 * @param int   $post_id ID of post to display column for.
	 */
	public function columns_display( $column, $post_id ) {
		switch ( $column ) {
		}
	}
}
