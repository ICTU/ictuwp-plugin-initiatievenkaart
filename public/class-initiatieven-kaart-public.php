<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://digitaleoverheid.nl
 * @since      1.0.0
 *
 * @package    Initiatieven_Kaart
 * @subpackage Initiatieven_Kaart/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Initiatieven_Kaart
 * @subpackage Initiatieven_Kaart/public
 * @author     Your Name <email@example.com>
 */
class Initiatieven_Kaart_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $initiatieven_kaart The ID of this plugin.
	 */
	private $initiatieven_kaart;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $initiatieven_kaart The name of the plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $initiatieven_kaart, $version ) {

		$this->initiatieven_kaart = $initiatieven_kaart;
		$this->version            = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * An instance of this class should be passed to the run() function
		 * defined in Initiatieven_Kaart_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Initiatieven_Kaart_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */


		if ( $this->is_initiatieven_page() ) {

			$version = $this->version;
			if ( WP_DEBUG ) {
				$file    = plugin_dir_path( __FILE__ ) . 'css/leaflet.css';
				$version = filemtime( $file );
			}
			wp_register_style( 'leaflet-css', plugin_dir_url( __FILE__ ) . 'css/leaflet.css', array(), $version );
			wp_enqueue_style( 'leaflet-css' );

			$version = $this->version;
			if ( WP_DEBUG ) {
				$file    = plugin_dir_path( __FILE__ ) . 'css/MarkerCluster.css';
				$version = filemtime( $file );
			}
			wp_register_style( 'markercluster-css', plugin_dir_url( __FILE__ ) . 'css/MarkerCluster.css', array(), $version );
			wp_enqueue_style( 'markercluster-css' );

			$version = $this->version;
			if ( WP_DEBUG ) {
				$file    = plugin_dir_path( __FILE__ ) . 'css/MarkerCluster.Default.css';
				$version = filemtime( $file );
			}
			wp_register_style( 'markercluster-default-css', plugin_dir_url( __FILE__ ) . 'css/MarkerCluster.Default.css', array(), $version );
			wp_enqueue_style( 'markercluster-default-css' );

			$version = $this->version;
			if ( WP_DEBUG ) {
				$file    = plugin_dir_path( __FILE__ ) . 'css/initiatieven-kaart-public.css';
				$version = filemtime( $file );
			}
			wp_enqueue_style( $this->initiatieven_kaart, plugin_dir_url( __FILE__ ) . 'css/initiatieven-kaart-public.css', array(), $version, 'all' );

			$version = $this->version;
			if ( WP_DEBUG ) {
				$file    = plugin_dir_path( __FILE__ ) . 'css/leaflet-gesture-handling.min.css';
				$version = filemtime( $file );
			}
			wp_register_style( 'leaflet-gesture-handling-css', plugin_dir_url( __FILE__ ) . 'css/leaflet-gesture-handling.min.css', array(), $version );
			wp_enqueue_style( 'leaflet-gesture-handling-css' );

		}

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Initiatieven_Kaart_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Initiatieven_Kaart_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		if ( $this->is_initiatieven_page() ) {

			$version = $this->version; // dit is NIET de versie van leaflet.js, maar ons eigen versienummer

			if ( WP_DEBUG ) {
				$file    = plugin_dir_path( __FILE__ ) . 'js/leaflet.js';
				$version = filemtime( $file );
			}
			wp_enqueue_script( 'leaflet-js', plugin_dir_url( __FILE__ ) . 'js/leaflet.js', array( 'jquery' ), $version, true );

			wp_enqueue_script( 'leaflet-gesture-handling-js', plugin_dir_url( __FILE__ ) . 'js/leaflet-gesture-handling.min.js', array( 'leaflet-js' ), $this->version, true );

			wp_enqueue_script( 'leaflet-markercluster-js', plugin_dir_url( __FILE__ ) . 'js/leaflet.markercluster.js', array(
				'jquery',
				'leaflet-js'
			), $this->version, true );

			wp_enqueue_script( $this->initiatieven_kaart, plugin_dir_url( __FILE__ ) . 'js/initiatieven-kaart-public.js', array( 'jquery' ), $this->version, false );

			// creates a javascript object Utils: Utils.baseurl = "http://.../../".
			// Use to create a nice path to SVG icons for example
			wp_localize_script( $this->initiatieven_kaart, 'Utils', array( 'siteurl' => get_option( 'siteurl' ) ) );

		}

	}

	public function is_initiatieven_page() {

		$islandingpage       = false;
		$led_pageid_overview = get_theme_mod( 'customizer_led_pageid_overview' );
		$currentpageid       = false;
		$page_template       = false;

		if ( is_admin()) {
			return false;
		}

		if ( is_page() ) {
			// testen of dit de landingspagina is van de initiatievenkaart
			$page_template = get_post_meta( get_the_id(), '_wp_page_template', true );
			$currentpageid = get_queried_object_id();

			if ( 'page-initiatieven.php' == $page_template ) {
				// deze pagina heeft het pagina template voor de initiatievenkaart
				$islandingpage = true;
			} else {
				if ( $led_pageid_overview === $currentpageid ) {
					// deze pagina is ingesteld als de centrale pagina voor initiatieven
					$islandingpage = true;
				}
			}
		}
		if ( $islandingpage ||
		     is_singular( CPT_INITIATIEF ) ||
		     is_post_type_archive( CPT_INITIATIEF ) ||
		     is_tax( CT_INITIATIEFTYPE ) ||
		     is_tax( CT_INITIATIEF_PROVINCIE )
		) {
			return true;
		} else {
			return false;
		}
	}

}
