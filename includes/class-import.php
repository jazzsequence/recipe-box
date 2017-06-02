<?php
/**
 * Recipe Box Import.
 *
 * Class for syndicating/importing recipes from a remote Recipe Box site.
 *
 * @since   0.2
 * @package Recipe_Box
 */

/**
 * Recipe Box Import.
 *
 * @since 0.2
 */
class RB_Import {
	/**
	 * Parent plugin class.
	 *
	 * @since 0.2
	 *
	 * @var   Recipe_Box
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
	 * @since  0.2
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
	 * @since  0.2
	 */
	public function hooks() {
		add_action( 'admin_menu', [ $this, 'add_import_page' ] );
	}

	/**
	 * Add the import admin page.
	 *
	 * @since 0.2
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
	 * Admin page markup.
	 *
	 * @since  0.2
	 */
	public function import_page_display() {
		?>
		<div class="wrap recipe-box-import-page">
			<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
			(Stuff goes here.)
		</div>
		<?php
	}
}
