<?php
/**
 * Recipe Box Import.
 *
 * Class for syndicating/importing recipes from a remote Recipe Box site.
 *
 * @since   0.3
 * @package Recipe_Box
 */

// We use CMB2 for the forms but we're not actually submitting anything.
require_once dirname( __FILE__ ) . '/../vendor/cmb2/init.php';

/**
 * Recipe Box Import.
 *
 * @since 0.3
 */
class RB_Import {
	/**
	 * Parent plugin class.
	 *
	 * @since 0.3
	 *
	 * @var   Recipe_Box
	 */
	protected $plugin = null;

	/**
	 * CMB2 key.
	 *
	 * @var    string
	 * @since  0.3
	 */
	protected $key = 'recipe_box_import';

	/**
	 * CMB2 metabox ID.
	 *
	 * @var    string
	 * @since  0.3
	 */
	protected $metabox_id = 'recipe_box_import_metabox';

	/**
	 * Constructor.
	 *
	 * @since  0.3
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
	 * @since  0.3
	 */
	public function hooks() {
		add_action( 'admin_menu',            [ $this, 'add_import_page' ] );
		add_action( 'cmb2_admin_init',       [ $this, 'add_import_metabox' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_js' ] );

		add_filter( 'json_prepare_post',     [ $this, 'trim_data' ], 12, 3 );
	}

	/**
	 * Enqueue admin javascript for importing recipes.
	 *
	 * @since 0.3
	 * @param string $hook The current screen.
	 */
	public function enqueue_admin_js( $hook ) {
		// Only load these scripts in the admin.
		if ( ! is_admin() || 'rb_recipe_page_recipe_box_import' !== $hook ) {
			return;
		}

		$min = '.min';

		// A better way to figure out the .min thing...
		// First we build an array of scripts. The first parameter is the script name and the second is the path to the script, excluding the .min.js or whatever.
		$scripts = [
			'name' => 'import',
			'js'   => rb()->url . 'assets/js/recipe-import',
			'css'  => rb()->url . 'assets/css/recipe-import',
		];

		// Check if debug is turned on. If it is, we set $min to an empty string. We won't minify if debugging is active.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$min = '';
		}

		// Set a default path that excludes $min entirely.
		$js_src  = $scripts['js'] . '.js';
		$css_src = $scripts['css'] . '.css';

		// Check if a minified version exists and set the $src to that if it does. If that file doesn't exist, we just use the default (unminified) version regardless of WP_DEBUG status.
		if ( file_exists( $scripts['js'] . $min . '.js' ) ) {
			$js_src = $scripts['js'] . $min . '.js';
		}

		if ( file_exists( $scripts['css'] . $min . '.css' ) ) {
			$css_src = $scripts['css'] . $min . '.css';
		}

		// If debug is on, bust the cache by appending a timestamp to the end of the version.
		$version = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? rb()->version . '-' . rand() : rb()->version;

		// Now enqueue the scripts.
		wp_enqueue_script( $scripts['name'], $js_src, [ 'jquery' ], $version, true );
		wp_enqueue_style( $scripts['name'], $css_src, [], $version );

		wp_localize_script( $scripts['name'], 'recipe_import_messages', [
			'error_no_url'      => esc_html__( 'No URL entered.', 'recipe-box' ),
			'error_invalid_url' => esc_html__( 'Attempted to fetch recipes but the URL you entered was invalid.', 'recipe-box' ),
			'success'           => esc_html__( 'Recipes found!', 'recipe-box' ),
			'no_more_recipes'   => esc_html__( 'That\'s all the recipes!', 'recipe-box' ),
			'import_url'        => get_admin_url( get_current_blog_id(), 'edit.php?post_type=rb_recipe&page=recipe_box_import' ),
		] );
	}

	/**
	 * Add the import admin page.
	 *
	 * @since 0.3
	 */
	public function add_import_page() {
		$this->options_page = add_submenu_page(
			'edit.php?post_type=rb_recipe',
			__( 'Import Recipes from Another Recipe Box', 'recipe-box' ),
			__( 'Import Recipes', 'recipe-box' ),
			'manage_options',
			'recipe_box_import',
			[ $this, 'import_page_display' ]
		);
	}

	/**
	 * Display the Import page. Handled by CMB2 and AJAX-y goodness.
	 *
	 * @since 0.3
	 */
	public function import_page_display() {
		// If we're here because we're importing recipes, handle that stuff and don't display this form.
		if ( isset( $_GET['importIds'] ) ) {
			$this->import_recipes();
			return;
		}

		// Override the default form and submit button.
		$args = [
			'form_format' => '',
			'save_button' => '',
		];
		?>
		<div class="wrap recipe-box-import">
			<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
			<div class="recipe-box-import-messages">
				<p class="rb-messages-inner"></p>
			</div>
			<?php cmb2_metabox_form( $this->metabox_id, $this->key, $args ); ?>
			<div class="recipe-box-import-header">
				<p class="fetching-recipes-message">
					<?php // Translators: %s is an API URL based on the Recipe Box site entered. ?>
					<?php echo wp_kses_post( sprintf( __( 'Fetching recipes from %s', 'recipe-box' ), '<span id="api-url-fetched"></span>' ) ); ?>
				</p>
			</div>
			<div class="recipe-box-import-recipe-list">
				<ul class="recipe-list">
				</ul>
			</div>
			<div class="recipe-box-import-footer">
				<p class="recipe-box-more">
					<a href="#" id="recipe-api-fetch-more" data-page="1"><?php esc_html_e( 'Fetch more recipes', 'recipe-box' ); ?></a>
				</p>
				<p class="recipe-box-import-submit">
					<button class="recipe-box-fetch button button-primary"><?php esc_html_e( 'Import selected recipes', 'recipe-box' ); ?></button>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Add the CMB2 metabox for the API URL.
	 *
	 * @since 0.3
	 */
	public function add_import_metabox() {
		$cmb = new_cmb2_box( array(
			'id'         => $this->metabox_id,
			'hookup'     => false,
			'cmb_styles' => false,
			'show_on'    => array(
				// These are important, don't remove.
				'key'   => 'options-page',
				'value' => array( $this->key ),
			),
		) );

		$cmb->add_field( array(
			'name'       => __( 'Recipe Box URL', 'recipe-box' ),
			'id'         => 'api_url',
			'type'       => 'text',
			'desc'       => '<a href="#" id="recipe-api-fetch" data-action="api-fetch">' . __( 'Fetch recipes', 'recipe-box' ) . '</a>',
			'attributes' => [
				'placeholder' => 'e.g. http://myrecipebox.com',
			],
		) );
	}

	/**
	 * Trim the data we're fetching to only include the stuff we actually want.
	 *
	 * @since  0.3
	 * @param  object $data    The WP-REST API data object.
	 * @param  object $post    The WP_Post object (not used).
	 * @param  string $context The context in which we are looking at this data.
	 * @return object          The updated REST API data object.
	 */
	public function trim_data( $data, $post, $context ) {
		// We only want to modify the 'view' context, for reading posts.
		if ( 'view' !== $context || is_wp_error( $data ) ) {
			return $data;
		}

		// Remove all the things we don't care about.
		$properties_to_remove = [
			'guid',
			'modified',
			'modified_gmt',
			'status',
			'type',
			'template',
			'_links',
		];

		foreach ( $properties_to_remove as $field ) {
			unset( $data[ $field ] );
		}

		return $data;
	}
}
