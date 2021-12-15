<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://digitaleoverheid.nl
 * @since      1.0.0
 *
 * @package    Initiatieven_Kaart
 * @subpackage Initiatieven_Kaart/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Initiatieven_Kaart
 * @subpackage Initiatieven_Kaart/includes
 * @author     Your Name <email@example.com>
 */
class Initiatieven_Kaart {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Initiatieven_Kaart_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $initiatieven_kaart The string used to uniquely identify this plugin.
	 */
	protected $initiatieven_kaart;

	// Thijs: TODO: document
	private $types;
	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * set van alle icons die aan een initiatieftype gekoppeld zijn
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array $icons
	 */
	protected $icons;


	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->template_initiatievenpagina     = 'page-initiatieven.php';
		$this->template_innovatieprojectpagina = 'page-innovatieproject.php';

		if ( defined( 'INITIATIEVEN_KAART_VERSION' ) ) {
			$this->version = INITIATIEVEN_KAART_VERSION;
		} else {
			$this->version = '1.0.0';
		}

		if ( ! defined( 'CPT_INITIATIEF' ) ) {
			define( 'CPT_INITIATIEF', 'initiatief' );
		}

		if ( ! defined( 'CPT_PROJECT' ) ) {
			define( 'CPT_PROJECT', 'innovatieproject' );
		}

		// taxonomie voor initiatief-type
		if ( ! defined( 'CT_INITIATIEFTYPE' ) ) {
			define( 'CT_INITIATIEFTYPE', 'initiatieftype' );
		}

		// taxonomie voor provincie voor een initiatief
		if ( ! defined( 'CT_PROVINCIE' ) ) {
			define( 'CT_PROVINCIE', 'provincie' );
		}

		// taxonomie voor type organisatie voor een innovatieproject
		if ( ! defined( 'CT_PROJECTORGANISATIE' ) ) {
			define( 'CT_PROJECTORGANISATIE', 'organisatie' );
		}

		// taxonomie voor jaar voor een innovatieproject
		if ( ! defined( 'CT_PROJECTJAAR' ) ) {
			define( 'CT_PROJECTJAAR', 'jaar-toekenning' );
		}

		$this->initiatieven_kaart = 'initiatieven-kaart';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();


	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Initiatieven_Kaart_Loader. Orchestrates the hooks of the plugin.
	 * - Initiatieven_Kaart_i18n. Defines internationalization functionality.
	 * - Initiatieven_Kaart_Admin. Defines all hooks for the admin area.
	 * - Initiatieven_Kaart_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-initiatieven-kaart-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-initiatieven-kaart-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-initiatieven-kaart-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-initiatieven-kaart-public.php';

		$this->loader = new Initiatieven_Kaart_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Initiatieven_Kaart_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Initiatieven_Kaart_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Initiatieven_Kaart_Admin( $this->get_initiatieven_kaart(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Initiatieven_Kaart_Public( $this->get_initiatieven_kaart(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );


		// add the page template to the templates list
		add_filter( 'theme_page_templates', array( $this, 'add_page_template' ) );

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
	 * @return    string    The name of the plugin.
	 * @since     1.0.0
	 */
	public function get_initiatieven_kaart() {
		return $this->initiatieven_kaart;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    Initiatieven_Kaart_Loader    Orchestrates the hooks of the plugin.
	 * @since     1.0.0
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 * @since     1.0.0
	 */
	public function get_version() {
		return $this->version;
	}

	// TODO: version? $plugin->version?
	public function enqueue_scripts() {

	}

	// Pagina-template toevoegen
	public function add_page_template( $post_templates ) {

		$post_templates[ $this->template_initiatievenpagina ]     = _x( 'Initiatieven-pagina', "naam template", 'initiatieven-kaart' );
		$post_templates[ $this->template_innovatieprojectpagina ] = _x( 'Innovatiebudget-pagina', "naam template", 'initiatieven-kaart' );

		return $post_templates;

	}

	//========================================================================================================
	/*
	 * Deze function wijzigt de main query voor archives van initiatieven, van taxonomie-overzichten
	 * van initiatieftypes en provincies.
	 * Door deze wijziging wordt op 1 pagina een overzicht getoond van ALLE initiatieven bij een bepaalde
	 * initiatieftype / provincie, in plaats van maximaal posts_per_page (meestal 10) en de initiatieven
	 * worden alfabetisch gesorteerd
	 */
	public function load_all_initiatieven_innovatieprojecten( $query ) {

		global $query_vars;

		if ( ! is_admin() && $query->is_main_query() ) {

			if ( is_post_type_archive( CPT_INITIATIEF ) ||
			     is_post_type_archive( CPT_PROJECT ) ||
			     ( is_tax( CT_INITIATIEFTYPE ) ) ||
			     ( is_tax( CT_PROVINCIE ) ) ||
			     ( is_tax( CT_PROJECTORGANISATIE ) ) ||
			     ( is_tax( CT_PROJECTJAAR ) ) ) {
				// geen pagination voor overzichten van:
				// - initiatieven
				// - initiatieven per initiatieftype
				// - initiatieven per provincie
				$query->set( 'posts_per_page', - 1 );
				$query->set( 'orderby', 'title' );
				$query->set( 'order', 'ASC' );

				return $query;

			}

		}

		return $query;
	}
	//========================================================================================================

}
