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

	public function enqueue_admin_js( $hook ) {
		// Only load these scripts in the admin.
		if ( ! is_admin() || 'rb_recipe_page_recipe_box_import' !== $hook ) {
			return;
		}

		$min = '.min';

		// A better way to figure out the .min thing...
		// First we build an array of scripts. The first parameter is the script name and the second is the path to the script, excluding the .min.js or whatever.
		$script = array(
			'name' => 'import',
			'path' => rb()->url . 'assets/js/recipe-import',
		);

		// Check if debug is turned on. If it is, we set $min to an empty string. We won't minify if debugging is active.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$min = '';
		}

		// Set a default path that excludes $min entirely.
		$src = $script['path'] . '.js';

		// Check if a minified version exists and set the $src to that if it does. If that file doesn't exist, we just use the default (unminified) version regardless of WP_DEBUG status.
		if ( file_exists( $script['path'] . $min . '.js' ) ) {
			$src = $script['path'] . $min . '.js';
		}

		// Now enqueue the script.
		wp_enqueue_script( $script['name'], $src, array( 'jquery' ), rb()->version, true );
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
		// Override the default form and submit button.
		$args = [
			'form_format' => '',
			'save_button' => '',
		];
		?>
		<div class="wrap recipe-box-import">
			<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
			<?php cmb2_metabox_form( $this->metabox_id, $this->key, $args ); ?>
		</div>
		<?php
	}

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
			'desc'       => '<a href="#" data-action="api-fetch">' . __( 'Fetch recipes', 'recipe-box' ) . '</a>',
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
