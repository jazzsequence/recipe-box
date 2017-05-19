<?php
/**
 * Recipe Box Options.
 *
 * @since   0.2
 * @package Recipe_Box
 */

require_once dirname( __FILE__ ) . '/../vendor/cmb2/init.php';

/**
 * Recipe Box Options class.
 *
 * @since 0.2
 */
class RB_Options {
	/**
	 * Parent plugin class.
	 *
	 * @var    Recipe_Box
	 * @since  0.2
	 */
	protected $plugin = null;

	/**
	 * Option key, and option page slug.
	 *
	 * @var    string
	 * @since  0.2
	 */
	protected $key = 'recipe_box_options';

	/**
	 * Options page metabox ID.
	 *
	 * @var    string
	 * @since  0.2
	 */
	protected $metabox_id = 'recipe_box_options_metabox';

	/**
	 * Options Page title.
	 *
	 * @var    string
	 * @since  0.2
	 */
	protected $title = '';

	/**
	 * Options Page hook.
	 *
	 * @var string
	 */
	protected $options_page = '';

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

		// Set our title.
		$this->title = esc_attr__( 'Recipe Box Options', 'recipe-box' );
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  0.2
	 */
	public function hooks() {

		// Hook in our actions to the admin.
		add_action( 'admin_init', [ $this, 'admin_init' ] );
		add_action( 'admin_menu', [ $this, 'add_options_page' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_css' ] );
		add_action( 'cmb2_admin_init', [ $this, 'add_options_page_metabox' ] );

	}

	/**
	 * Register our setting to WP.
	 *
	 * @since  0.2
	 */
	public function admin_init() {
		register_setting( $this->key, $this->key );
	}

	public function enqueue_css( $hook ) {
		// Bail if we aren't on the recipe box options page.
		if ( 'rb_recipe_page_recipe_box_options' !== $hook ) {
			return;
		}
		wp_enqueue_style( 'recipes', rb()->url . 'assets/css/recipes.css', array(), rb()->version, 'screen' );
	}

	/**
	 * Add menu options page.
	 *
	 * @since  0.2
	 */
	public function add_options_page() {
		$this->options_page = add_submenu_page(
			'edit.php?post_type=rb_recipe',
			$this->title,
			__( 'Options', 'recipe-box' ),
			'manage_options',
			$this->key,
			array( $this, 'admin_page_display' )
		);

		// Include CMB CSS in the head to avoid FOUC.
		add_action( "admin_print_styles-{$this->options_page}", array( 'CMB2_hookup', 'enqueue_cmb_css' ) );
	}

	/**
	 * Admin page markup. Mostly handled by CMB2.
	 *
	 * @since  0.2
	 */
	public function admin_page_display() {
		?>
		<div class="wrap cmb2-options-page <?php echo esc_attr( $this->key ); ?>">
			<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
			<?php cmb2_metabox_form( $this->metabox_id, $this->key ); ?>
		</div>
		<?php
	}

	/**
	 * Add custom fields to the options page.
	 *
	 * @since  0.2
	 */
	public function add_options_page_metabox() {

		// Add our CMB2 metabox.
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

		// Add your fields here.
		$cmb->add_field( array(
			'name' => __( 'Recipes with blog posts', 'recipe-box' ),
			'desc' => __( 'Check this box if you want recipes to display in line with other blog posts or otherwise display on the home page automagically.', 'recipe-box' ),
			'id'   => 'recipes_with_blog', // No prefix needed.
			'type' => 'checkbox',
		) );

	}
}
