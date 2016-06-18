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
			array( 'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail' ) )
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

	public function fields() {
		$prefix = 'rb_recipe_';

		$cmb = new_cmb2_box( array(
			'id'            => $prefix . 'metabox',
			'title'         => __( 'Recipe Box Rb_recipe Meta Box', 'recipe-box' ),
			'object_types'  => array( 'rb-rb-recipe' ),
		) );
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
