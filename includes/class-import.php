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
		add_action( 'admin_menu',      [ $this, 'add_import_page' ] );
		add_action( 'cmb2_admin_init', [ $this, 'add_import_metabox' ] );
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
		?>
		<div class="wrap recipe-box-import-page">
			<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
			(Stuff goes here.)
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
}
