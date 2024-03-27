<?php

/**
 *
 * @link       https://www.floraathome.nl/
 * @since      1.0.0
 *
 * @package    florahome
 * @subpackage florahome/includes
 */

/**
 * The core plugin class.
 *
 *
 * @since      1.0.0
 * @package    florahome
 * @subpackage florahome/includes
 */
class florahome {

	/**
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      florahome_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $florahome    The string used to uniquely identify this plugin.
	 */
	protected $florahome;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	public function __construct() {
		if ( defined( 'florahome_VERSION' ) ) {
			$this->version = florahome_VERSION;
		} else {
			$this->version = '1.2.2';
		}
		$this->florahome = 'florahome';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	private function load_dependencies() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-florahome-loader.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-florahome-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-florahome-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-florahome-order.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-florahome-order-item.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-florahome-order-productoptions.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-florahome-process.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-florahome-notice.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/florahome-admin-display.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/florahome-product-import.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/florahome-import-image.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/florahome-order-export.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/florahome-order-screen.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/florahome-admin-error.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/florahome-admin-errors.php';

		//Public
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-florahome-public.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/florahome-public-display.php';


		$this->loader = new florahome_Loader();

	}

	private function set_locale() {

		$plugin_i18n = new florahome_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	private function define_admin_hooks() {

		$plugin_admin = new florahome_Admin( $this->fah_get_florahome(), $this->fah_get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'fah_enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'fah_enqueue_scripts' );
		$this->loader->add_action( 'admin_menu',$plugin_admin,'fah_add_admin_menu' );
		$this->loader->add_action( 'admin_init', $plugin_admin,'fah_settings_init' );
		$this->loader->add_action( 'woocommerce_product_options_grouping', $plugin_admin,'fah_product_options_grouping' );
		$this->loader->add_action( 'task_flora_product_update', $plugin_admin,'fah_cron_product_update' );
		$this->loader->add_action( 'task_flora_order_export', $plugin_admin,'fah_cron_order_export' );
		$this->loader->add_action( 'task_flora_image_import', $plugin_admin,'fah_cron_image_import' );
		$this->loader->add_action( 'task_flora_product_sync', $plugin_admin,'fah_cron_product_sync' );


	}


	private function define_public_hooks() {

		$plugin_public = new florahome_Public( $this->fah_get_florahome(), $this->fah_get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'fah_enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'fah_enqueue_scripts' );

	}
	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function fah_get_florahome() {
		return $this->florahome;
	}

	/**
	 *
	 * @since     1.0.0
	 * @return    florahome_Loader    Orchestrates the hooks of the plugin.
	 */
	public function fah_get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function fah_get_version() {
		return $this->version;
	}

}