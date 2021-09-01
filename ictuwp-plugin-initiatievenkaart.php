<?php

/**
 * LED initiatieven-kaart
 *
 * @link              https://www.digitaleoverheid.nl/
 * @since             1.0.0
 * @package           Initiatieven_Kaart
 *
 * @wordpress-plugin
 * Plugin Name:       Initiatieven Kaart voor LED (digitaleoverheid.nl)
 * Plugin URI:        https://digitaleoverheid.nl/initiatieven-kaart-uri/
 * Description:       Toont LED initiatieven op een kaart
 * Version:           1.0.4
 * Author:            Thijs Brentjens
 * Author URI:        https://brentjensgeoict.nl
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       initiatieven-kaart
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'INITIATIEVEN_KAART_VERSION', '1.0.8' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-initiatieven-kaart.php';

// in dit bestand staan de veld-definities voor de ACF-velden
require plugin_dir_path( __FILE__ ) . 'includes/acf-definitions.php';

//========================================================================================================

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_initiatieven_kaart() {

	$plugin = new Initiatieven_Kaart();
	$plugin->run();

	add_action( 'wp_enqueue_scripts', array( $plugin, 'enqueue_scripts' ) );

	// voor de archives: tonen van ALLE initiatieven, ongeacht het
	// maximum aantal posts per pagina
	add_action( 'pre_get_posts', array( $plugin, 'load_all_initiatieven' ), 999 );
}

run_initiatieven_kaart();

//========================================================================================================
/*
 * filter voor overzicht (archive + taxonomy) van de initiatieven
 */
function led_template_archive_initiatieven( $archive_template ) {
	global $post;

	if ( is_post_type_archive( CPT_INITIATIEF ) ) {
		// het is een archive voor CPT = CPT_INITIATIEF
		$archive_template = dirname( __FILE__ ) . '/templates/page-initiatieven.php';
	} elseif ( ( is_tax( CT_INITIATIEFTYPE ) ) || ( is_tax( CT_INITIATIEF_PROVINCIE ) ) ) {
		// het is een overzicht van initiatieven per type of per provincie
		$archive_template = dirname( __FILE__ ) . '/templates/archive-initiatieven.php';
	}

	return $archive_template;
}

add_filter( 'taxonomy_template', 'led_template_archive_initiatieven' );
add_filter( 'archive_template', 'led_template_archive_initiatieven' );

//========================================================================================================
/*
 * filter voor overzicht (archive + taxonomy) van de initiatieven
 */
function led_template_page_initiatieven( $archive_template ) {
	global $post;

	$page_template = get_post_meta( get_the_id(), '_wp_page_template', true );

	if ( is_singular( CPT_INITIATIEF ) ) {
		// het is een single voor CPT = CPT_INITIATIEF
		$archive_template = dirname( __FILE__ ) . '/templates/single-initiatief.php';

	} elseif ( is_post_type_archive( CPT_INITIATIEF ) ) {

		// het is een overzicht van initiatieven per type of per provincie
		$archive_template = dirname( __FILE__ ) . '/templates/page-initiatieven.php';

	} elseif ( is_tax( CT_INITIATIEFTYPE ) || is_tax( CT_INITIATIEF_PROVINCIE ) ) {

		// het is een overzicht van initiatieven per type of per provincie
		$archive_template = dirname( __FILE__ ) . '/templates/archive-initiatieven.php';

	} elseif ( 'page-initiatieven.php' == $page_template ) {

		// het is een overzicht van initiatieven per type of per provincie
		$archive_template = dirname( __FILE__ ) . '/templates/page-initiatieven.php';

	}

	return $archive_template;
}

add_filter( 'template_include', 'led_template_page_initiatieven' );

//========================================================================================================

/*
 * Deze functie zorgt voor het custom post type 'initiatief' en voor
 * twee custom taxonomies: initiatieftype en provincie; deze
 * taxonomieen zijn alleen geldig voor CPT 'initiatief'.
 */
function led_custom_tax_and_types() {


	// ---------------------------------------------------------------------------------------------------
	// uit customizer de pagina ophalen die het overzicht is van alle initiatieven
	$optionpage        = get_theme_mod( 'customizer_led_pageid_overview' );
	$defaultslugforCPT = CPT_INITIATIEF;

	if ( $optionpage ) {
		$defaultslugforCPT = get_the_permalink( $optionpage );
		$defaultslugforCPT = str_replace( home_url(), '', $defaultslugforCPT );
		$defaultslugforCPT = trim( $defaultslugforCPT, '/' );
	}


	$args = array(
		'label'               => esc_html__( CPT_INITIATIEF, 'waymark' ),
		'description'         => '',
		'labels'              => array(
			'name'                  => esc_html_x( 'Initiatieven', 'post type', 'initiatieven-kaart' ),
			'singular_name'         => esc_html_x( 'Initiatief', 'post type', 'initiatieven-kaart' ),
			'menu_name'             => esc_html_x( 'Initiatieven', 'post type', 'initiatieven-kaart' ),
			'name_admin_bar'        => esc_html_x( 'Initiatief', 'post type', 'initiatieven-kaart' ),
			'archives'              => esc_html_x( 'Overzicht initiatieven', 'post type', 'initiatieven-kaart' ),
			'attributes'            => esc_html_x( 'Eigenschappen initiatief', 'post type', 'initiatieven-kaart' ),
			'parent_item_colon'     => esc_html_x( 'Parent Map:', 'post type', 'initiatieven-kaart' ),
			'all_items'             => esc_html_x( 'Alle initiatieven', 'post type', 'initiatieven-kaart' ),
			'add_new_item'          => esc_html_x( 'Initiatief toevoegen', 'post type', 'initiatieven-kaart' ),
			'add_new'               => esc_html_x( 'Toevoegen', 'post type', 'initiatieven-kaart' ),
			'new_item'              => esc_html_x( 'Nieuw initiatief', 'post type', 'initiatieven-kaart' ),
			'edit_item'             => esc_html_x( 'Bewerk initiatief', 'post type', 'initiatieven-kaart' ),
			'update_item'           => esc_html_x( 'Update initiatief', 'post type', 'initiatieven-kaart' ),
			'view_item'             => esc_html_x( 'Bekijk initiatief', 'post type', 'initiatieven-kaart' ),
			'view_items'            => esc_html_x( 'Bekijk initiatieven', 'post type', 'initiatieven-kaart' ),
			'search_items'          => esc_html_x( 'Zoek initiatief', 'post type', 'initiatieven-kaart' ),
			'not_found'             => esc_html_x( 'Not found', 'post type', 'initiatieven-kaart' ),
			'not_found_in_trash'    => esc_html_x( 'Not found in Trash', 'post type', 'initiatieven-kaart' ),
			'featured_image'        => esc_html_x( 'Featured Image', 'post type', 'initiatieven-kaart' ),
			'set_featured_image'    => esc_html_x( 'Set featured image', 'post type', 'initiatieven-kaart' ),
			'remove_featured_image' => esc_html_x( 'Remove featured image', 'post type', 'initiatieven-kaart' ),
			'use_featured_image'    => esc_html_x( 'Use as featured image', 'post type', 'initiatieven-kaart' ),
			'insert_into_item'      => esc_html_x( 'Insert into Map', 'post type', 'initiatieven-kaart' ),
			'uploaded_to_this_item' => esc_html_x( 'Uploaded to this initiatief', 'post type', 'initiatieven-kaart' ),
			'items_list'            => esc_html_x( 'Map list', 'post type', 'initiatieven-kaart' ),
			'items_list_navigation' => esc_html_x( 'Maps list navigation', 'post type', 'initiatieven-kaart' ),
			'filter_items_list'     => esc_html_x( 'Filter initiatief list', 'post type', 'initiatieven-kaart' ),
		),
		'supports'            => array( 'title', 'author', 'excerpt', 'editor' ),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_admin_bar'   => true,
		'show_in_nav_menus'   => true,
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'rewrite'             => array( 'slug' => $defaultslugforCPT ),
		'capability_type'     => 'post'
	);

	register_post_type( CPT_INITIATIEF, $args );

	// Initiatieftype
	$labels = array(
		'name'              => esc_html_x( 'Type initatief', 'taxonomy', 'initiatieven-kaart' ),
		'singular_name'     => esc_html_x( 'Initatieftype', 'taxonomy singular name', 'initiatieven-kaart' ),
		'search_items'      => esc_html_x( 'Search initatieftype', 'taxonomy', 'initiatieven-kaart' ),
		'all_items'         => esc_html_x( 'All initatieftypes', 'taxonomy', 'initiatieven-kaart' ),
		'parent_item'       => esc_html_x( 'Parent initatieftype', 'taxonomy', 'initiatieven-kaart' ),
		'parent_item_colon' => esc_html_x( 'Parent initatieftype:', 'taxonomy', 'initiatieven-kaart' ),
		'edit_item'         => esc_html_x( 'Edit initatieftype', 'taxonomy', 'initiatieven-kaart' ),
		'update_item'       => esc_html_x( 'Update initatieftype', 'taxonomy', 'initiatieven-kaart' ),
		'add_new_item'      => esc_html_x( 'Add New initatieftype', 'taxonomy', 'initiatieven-kaart' ),
		'new_item_name'     => esc_html_x( 'New initatieftype Name', 'taxonomy', 'initiatieven-kaart' ),
		'menu_name'         => esc_html_x( 'Initatieftype', 'taxonomy', 'initiatieven-kaart' ),
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => CT_INITIATIEFTYPE ),
	);

	register_taxonomy( CT_INITIATIEFTYPE, array( CPT_INITIATIEF ), $args );

	// Gemeente; dit is een taxonomy zodat we initiatieven kunnen groeperen.
	$labels = array(
		'name'              => esc_html_x( 'Provincie', 'taxonomy', 'initiatieven-kaart' ),
		'singular_name'     => esc_html_x( 'Provincie', 'taxonomy singular name', 'initiatieven-kaart' ),
		'search_items'      => esc_html_x( 'Search provincie', 'taxonomy', 'initiatieven-kaart' ),
		'all_items'         => esc_html_x( 'All provincies', 'taxonomy', 'initiatieven-kaart' ),
		'parent_item'       => esc_html_x( 'Parent provincie', 'taxonomy', 'initiatieven-kaart' ),
		'parent_item_colon' => esc_html_x( 'Parent provincie:', 'taxonomy', 'initiatieven-kaart' ),
		'edit_item'         => esc_html_x( 'Edit provincie', 'taxonomy', 'initiatieven-kaart' ),
		'update_item'       => esc_html_x( 'Update provincie', 'taxonomy', 'initiatieven-kaart' ),
		'add_new_item'      => esc_html_x( 'Add New provincie', 'taxonomy', 'initiatieven-kaart' ),
		'new_item_name'     => esc_html_x( 'New provincie Name', 'taxonomy', 'initiatieven-kaart' ),
		'menu_name'         => esc_html_x( 'Provincie', 'taxonomy', 'initiatieven-kaart' ),
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => CT_INITIATIEF_PROVINCIE ),
	);

	register_taxonomy( CT_INITIATIEF_PROVINCIE, array( CPT_INITIATIEF ), $args );


}

// Trigger registering the post type as soon as possible
add_action( 'init', 'led_custom_tax_and_types' );

//========================================================================================================
/*
 * deze functie haalt voor alle inititieftypen de bijbehorende icoontjes op
 * TODO: EIGENLIJK hoort deze functie thuis in de class Initiatieven_Kaart
 * maar voor nu heb ik 'm effe hier gefrut
 * SORRY MENSHEID!!
 */
function led_get_initiatieficons() {

	$arr_initiatief_type_icon = [];

	// alle types langs om voor elk het bijbehorende icoontje op te halen
	$args  = [
		'taxonomy'   => CT_INITIATIEFTYPE,
		'hide_empty' => true,
		'orderby'    => 'name',
		'order'      => 'ASC',
	];
	$terms = get_terms( $args );

	if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
		$count = count( $terms );

		foreach ( $terms as $term ) {

			$initiatief_type_icon = get_field( 'initiatief_type_icon', CT_INITIATIEFTYPE . '_' . $term->term_id );

			if ( $initiatief_type_icon ) {
				$arr_initiatief_type_icon[ $term->slug ] = $initiatief_type_icon;
			} else {
				$arr_initiatief_type_icon[ $term->slug ] = 'onbekend';
			}
		}
	}

	return $arr_initiatief_type_icon;

}

//========================================================================================================
/*
 * deze functie haalt formatteert een list-item voor de lijst met initiatieven
 */
function led_get_list_item_archive( $postobject, $initiatieficons = array() ) {

	$return  = '';
	$counter = 0;

	// use the location attributes to create data-attributes for the map
	// second term false: current post
	$locationField = get_field( 'openstreet_map', $postobject->ID );
	$title         = get_the_title( $postobject->ID );
	$permalink     = led_get_initiatief_permalink( $postobject->ID );

	/*
	 * haal de intitieftypes op. Dit kunnen er meerdere zijn, op dit moment.
	 * dit is de taxonomie CT_INITIATIEFTYPE
	 * aan elke waarde hiervan zou een icoontje moeten hangen
	 * dus bijv. type 'Community' krijgt een icoontje 'community'
	 */
	$initatieftypes = get_the_terms( $postobject->ID, CT_INITIATIEFTYPE );
	$classes        = array();

	if ( $locationField != false ) {
		// er zijn locatie-gegevens voor dit initiatief

		$plaatsnaam     = get_field( 'locatie_plaatsnaam', $postobject->ID );
		$initiatieftype = '';

		if ( $initatieftypes && ! is_wp_error( $initatieftypes ) ) :
			// check in welk initiatieftype dit initatieftype zit
			// aan dit type hangt o.m. het icoontje
			// NB op dit moment is het praktisch mogelijk om een initiatief aan
			// MEERDERE initiatieftypes te hangen

			$labels = '';

			foreach ( $initatieftypes as $term ) {
				// het icoontje dat bij dit initatieftype hoort, staat in de array $initiatieficons
				array_push( $classes, $initiatieficons[ $term->slug ] );
				$labels .= '<dd class="' . $term->slug . '">' . $term->name . '</dd>';
			}

			if ( $labels ) {
				// als er iets aanwezig is voor de taxonomy initatietype,
				// dan zetten we alle waarden daarvoor in een <dl>
				$initiatieftype = '<dl class="initiatieftype ' . join( " ", $classes ) . '">';
				$initiatieftype .= '<dt>' . _x( 'Type', 'Label type initiatief', 'initiatieven-kaart' ) . '</dt>';
				$initiatieftype .= $labels;
				$initiatieftype .= '</dl>';
			}

		endif;

		// TB: gebruik de lat/lon van de eerste marker als locatie
		// zie https://github.com/mcguffin/acf-openstreetmap-field/wiki/Usage
		$bestLatitude  = '';
		$bestLongitude = '';
		if ( count( $locationField["markers"] ) >= 1 ):
			$bestLatitude  = $locationField["markers"][0]["lat"];
			$bestLongitude = $locationField["markers"][0]["lng"];
		else:
			// TODO: map center acceptable?
			$bestLatitude  = $locationField["lat"];
			$bestLongitude = $locationField["lng"];
		endif;

		$return .= sprintf( "\n\n" . '<li class="map-item" data-latitude="%s" data-longitude="%s" data-map-item-type="%s" data-map-item-plaats="%s" data-map-item-naam="%s">', $bestLatitude, $bestLongitude, join( " ", $classes ), $plaatsnaam, $title );
		$return .= sprintf( "\n" . '<h2><a href="%s">%s</a></h2>', $permalink, $title );
		$return .= sprintf( "\n" . '%s', $initiatieftype );

		// iets van een samenvatting, beschrijving tonen hier
		$return .= sprintf( '<p>%s</p>', wp_strip_all_tags( get_the_excerpt() ) );
		$return .= "\n</li>";
	} else {
		// geen locationField , wel een list item toevoegen, maar zonder de data attributen voor locatie?
		// nog bepalen wat te doen, obv daravan evt refactoren met code hierboven
		$return .= $locationField;
		$return .= sprintf( '<li class="map-item no-location" data-map-item-type="%s">', join( " ", $classes ) );
		$return .= sprintf( '<h2><a href="%s">%s</a></h2>', $permalink, $title );
		$return .= sprintf( '<p>%s</p>', wp_strip_all_tags( get_the_excerpt() ) );
		$return .= '</li>';
	}

	return $return;
}

//========================================================================================================

/**
 * Voeg een paginaselector toe aan de customizer
 * zie [admin] > Weergave > Customizer > Initiatievenkaart
 */
function led_get_initiatief_permalink( $initiatiefid = 0 ) {

	if ( ! $initiatiefid ) {
		return;
	}

	$led_pageid_overview = get_theme_mod( 'customizer_led_pageid_overview' );

	if ( $led_pageid_overview ) {

		$base = get_permalink( $initiatiefid );

		return $base;
	} else {
		return get_permalink( $initiatiefid );
	}

}

//========================================================================================================

/**
 * Voeg een paginaselector toe aan de customizer
 * zie [admin] > Weergave > Customizer > Initiatievenkaart
 */
function led_append_customizer_field( $wp_customize ) {

	//	eigen sectie voor Theme Customizer
	$wp_customize->add_section( 'customizer_led_initiatievenkaart', array(
		'title'       => _x( 'Initiatievenkaart', 'customizer menu', 'initiatieven-kaart' ),
		'capability'  => 'edit_theme_options',
		'description' => _x( 'Instellingen voor de initiatievenkaart.', 'customizer menu', 'initiatieven-kaart' ),
	) );

	// add two text fields
	$wp_customize->add_setting( 'led_text_before_list', array(
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'led_sanitize_text_block',
	) );
	$wp_customize->add_control( 'led_text_before_list', array(
		'type'        => 'textarea',
		'section'     => 'customizer_led_initiatievenkaart', // Add a default or your own section
		'label'       => _x( 'Tekst <em>voor</em> de kaart', 'customizer menu', 'initiatieven-kaart' ),
		'description' => _x( 'Tekst die <em>direct voor</em> de kaart staat', 'customizer menu', 'initiatieven-kaart' ),
	) );

	$wp_customize->add_setting( 'led_text_after_list', array(
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'led_sanitize_text_block',
	) );
	$wp_customize->add_control( 'led_text_after_list', array(
		'type'        => 'textarea',
		'section'     => 'customizer_led_initiatievenkaart', // Add a default or your own section
		'label'       => _x( 'Tekst <em>na</em> de kaart', 'customizer menu', 'initiatieven-kaart' ),
		'description' => _x( 'Tekst die <em>direct na</em> de kaart staat', 'customizer menu', 'initiatieven-kaart' ),
	) );

	// add dropdown with pages to appoint the new slug for the CPT
	$wp_customize->add_setting( 'customizer_led_pageid_overview', array(
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'led_sanitize_dropdown_pages',
	) );
	$wp_customize->add_control( 'customizer_led_pageid_overview', array(
		'type'        => 'dropdown-pages',
		'section'     => 'customizer_led_initiatievenkaart', // Add a default or your own section
		'label'       => _x( 'Pagina met alle initiatieven', 'customizer menu', 'initiatieven-kaart' ),
		'description' => _x( 'In het kruimelpad en in de URL voor een initiatief zal deze pagina terugkomen.', 'customizer menu', 'initiatieven-kaart' ),
	) );


}

add_action( 'customize_register', 'led_append_customizer_field' );

//========================================================================================================
/*
only allow for
<a>, <h2>, <h3>, <br>, <p>, <em>, <strong>
*/
function led_sanitize_text_block( $text ) {

	return wp_kses( $text, array(
		'a'      => array(
			'href'  => array(),
			'title' => array()
		),
		'h2'     => array(),
		'h3'     => array(),
		'br'     => array(),
		'p'      => array(),
		'em'     => array(),
		'strong' => array(),
	) );

}

//========================================================================================================

// zorg dat een geldige pagina wordt teruggegeven
function led_sanitize_dropdown_pages( $page_id, $setting ) {

	$value = $setting->default;

	// Alleen een geldige ID accepteren
	$page_id = absint( $page_id );

	if ( $page_id ) {

		if ( 'publish' != get_post_status( $page_id ) ) {
			// alleen geubliceerde pagina's accepteren
			return $value;
		}

		// heeft de pagina het juiste template?

		// het is een gepubliceerde pagina.
		// de complete slug voor deze pagina wordt de basis voor CPT_INITIATIEF
		$value     = $page_id;
		$permalink = get_the_permalink( $page_id );
		$permalink = str_replace( home_url(), '', $permalink );
		$permalink = trim( $permalink, '/' );

		if ( $permalink ) {

			$args = array(
				"rewrite" => array( "slug" => $permalink, "with_front" => true ),
			);

			register_post_type( CPT_INITIATIEF, $args );

			// ---------------------------------------------------------------------------------------------------
			// clean up after ourselves
			flush_rewrite_rules();

			if ( WP_DEBUG ) {

				// note in log
				error_log( 'led_sanitize_dropdown_pages: slug for ' . CPT_INITIATIEF . " changed to " . $permalink );

			}
		}
	}

	return $value;

}

//========================================================================================================
/*
 * Deze functie hoest een titel op boven de lijst met initiatieven.
 * Deze titel komt voor op een overzicht van ALLE initiatieven of een lijst
 * met initiatieven per provincie.
 * Je ziet ook het aantal initiatieven.
 */
function led_initiatieven_archive_title( $doreturn = false ) {

	global $wp_query;
	global $post;

	$archive_title       = _x( 'Initiatieven', 'Archive initiatieven', 'initiatieven-kaart' );
	$archive_description = '';
	$return              = '';
	$count               = $wp_query->post_count;
	$led_pageid_overview = get_theme_mod( 'customizer_led_pageid_overview' );

	if ( is_post_type_archive( CPT_INITIATIEF ) ) {

		// als er een pagina is aangewezen als overview voor de initiatieven, neem
		// dan die titel over
		if ( $led_pageid_overview ) {

			$content_post        = get_post( $led_pageid_overview );
			$archive_title       = get_the_title( $led_pageid_overview );
			$content             = $content_post->post_content;
			$archive_description = apply_filters( 'the_content', $content );

		} else {
			// anders is de paginatitel het label dat we aan het CPT hebben gegeven
			// info ophalen voor custom post type CPT_INITIATIEF
			$obj = get_post_type_object( CPT_INITIATIEF );

			if ( $obj->labels->singular_name ) {
				$archive_title = $obj->labels->archives;
			}

		}

		$return = '<h1>' . $archive_title . '</h1>';

	} elseif ( ( is_tax( CT_INITIATIEFTYPE ) ) || ( is_tax( CT_INITIATIEF_PROVINCIE ) ) ) {
		// we kijken naar een lijst van initiatieven per initiatieftype of provincie

		$term_id = get_queried_object_id();
		$term    = get_term( $term_id, ( is_tax( CT_INITIATIEFTYPE ) ? CT_INITIATIEFTYPE : CT_INITIATIEF_PROVINCIE ) );

		if ( $term && ! is_wp_error( $term ) ) {
			$archive_title = $term->name;
			if ( $term->description ) {
				$archive_description = $term->description;
			}
		}

		if ( $count ) {
			if ( is_tax( CT_INITIATIEF_PROVINCIE ) ) {
				// voorzetsels, best belangrijk
				$return = '<h1>' . sprintf( _n( '%s initiatief in %s', "%s initiatieven in %s", $count, 'initiatieven-kaart' ), $count, $archive_title ) . '</h1>';
			} else {
				$return = '<h1>' . sprintf( _n( '%s initiatief voor %s', "%s initiatieven voor %s", $count, 'initiatieven-kaart' ), $count, $archive_title ) . '</h1>';
			}
		} else {
			// Niks geen initiatieven niet. Nul, nada, noppes, nihil.
			// Zeeland en Drenthe, laat van je HO-REN!
			$return              = '<h1>' . sprintf( _x( 'Geen initiatieven gevonden voor %s', "Geen initiatieven", 'initiatieven-kaart' ), $archive_title ) . '</h1>';
			$archive_description = 'Sorry.';
		}

	}

	if ( $archive_description ) {
		$return .= '<p>' . $archive_description . '</p>';
	}

	if ( $doreturn ) {
		return $return;
	} else {
		echo $return;
	}

}

//========================================================================================================
/*
 * Deze functie retourneert een of meerder lijstjes met links naar de custom taxonomieen.
 * Als je kijkt naar een single term uit een taxonomie, dan worden alleen links naar
 * de andere terms uit diezelfde taxonomie getoond.
 * Maar anders, dan worden lijstjes getoond met provincies waar
 * initiatieven zijn (wat, waarom is er niks in Zeeland?) of alle initiatieftypes.
 */

function led_initiatieven_taxonomy_list( $doreturn = false ) {

	$return = '';

	if ( is_tax( CT_INITIATIEFTYPE ) ) {
		// toon de lijst van ANDERE initiatieftypes
		$return .= led_initiatieven_show_taxonomy_list( CT_INITIATIEFTYPE, __( 'Andere initiatieftypes', 'taxonomie-lijst', 'initiatieven-kaart' ), false, get_queried_object_id() );
	} elseif ( is_tax( CT_INITIATIEF_PROVINCIE ) ) {
		// toon de lijst van ANDERE provincies
		$return .= led_initiatieven_show_taxonomy_list( CT_INITIATIEF_PROVINCIE, __( 'Andere provincies', 'taxonomie-lijst', 'initiatieven-kaart' ), false, get_queried_object_id() );
	} else {
		// toon de lijst van ALLE initiatieftypes
		$return .= led_initiatieven_show_taxonomy_list( CT_INITIATIEFTYPE, __( 'Initiatieftypes', 'taxonomie-lijst', 'initiatieven-kaart' ), false );
		// en de lijst van ALLE provincies
		$return .= led_initiatieven_show_taxonomy_list( CT_INITIATIEF_PROVINCIE, __( 'Provincies', 'taxonomie-lijst', 'initiatieven-kaart' ), false );
	}

	if ( $return ) {
		$return = '<div class="initiatieven-taxonomylist">' . $return . '</div>';
	}

	if ( $doreturn ) {
		return $return;
	} else {
		echo $return;
	}

}

//========================================================================================================

function led_initiatieven_show_taxonomy_list( $taxonomy = 'category', $title = '', $doecho = false, $exclude = '' ) {

	$return = '';

	if ( taxonomy_exists( $taxonomy ) ) {

		$args = array(
			'taxonomy'           => $taxonomy,
			'orderby'            => 'name',
			'order'              => 'ASC',
			'hide_empty'         => false,
			'ignore_custom_sort' => true,
			'echo'               => 0,
			'hierarchical'       => true,
			'title_li'           => '',
		);

		if ( $exclude ) {
			//
			$args['exclude']    = $exclude;
			$args['hide_empty'] = true;
		}

		$terms = get_terms( $args );

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {

			$return .= '<section class="taxonomy ' . $taxonomy . '">';
			if ( $title ) {
				$return .= '<h2>' . $title . '</h2>';
			}

			$return .= '<ul>';
			foreach ( $terms as $term ) {
				$return .= '<li><a href="' . get_term_link( $term ) . '">' . $term->name . '</a>';
				if ( $term->description ) {
					$return .= '<br>' . $term->description;
				}
				$return .= '</li>';
			}
			$return .= '</ul>';
			$return .= '</section>';

		}
	}

	if ( $doecho ) {
		echo $return;
	} else {
		return $return;
	}

}

//========================================================================================================

function led_initiatieven_filter_breadcrumb( $crumb = '', $args = '' ) {

	global $post;

	if ( ! ( is_singular( CPT_INITIATIEF ) || is_post_type_archive( CPT_INITIATIEF ) || is_tax( CT_INITIATIEFTYPE ) || is_tax( CT_INITIATIEF_PROVINCIE ) ) ) {
		// niks doen we niet met een initiatief bezig zijn
		return $crumb;
	}


	// uit siteopties de pagina ophalen die het overzicht is van alle links
	$optionpage  = get_theme_mod( 'customizer_led_pageid_overview' );
	$currentitem = explode( '</span>', $crumb );
	$parents     = array();
	$return      = '';
	$termid      = '';
	if ( is_tax( CT_INITIATIEFTYPE ) || is_tax( CT_INITIATIEF_PROVINCIE ) ) {
		$termid = get_queried_object_id();
	}

	if ( $optionpage ) {
		// haal de ancestors op voor deze pagina
		$ancestors = get_post_ancestors( $optionpage );
		if ( is_post_type_archive( CPT_INITIATIEF ) ) {
			$parents[] = array(
				'text' => get_the_title( $optionpage ),
			);
		} else {
			$parents[] = array(
				'url'  => get_page_link( $optionpage ),
				'text' => get_the_title( $optionpage ),
			);
		}

		if ( $ancestors ) {

			// haal de hele keten aan ancestors op en zet ze in de returnstring
			foreach ( $ancestors as $ancestorid ) {
				// Prepend one or more elements to the beginning of an array
				array_unshift( $parents, [
					'url'  => get_page_link( $ancestorid ),
					'text' => get_the_title( $ancestorid ),
				] );
			}
		}

	} else {
		if ( is_singular( CPT_INITIATIEF ) ) {
			return $crumb;
		}

		$obj = get_post_type_object( CPT_INITIATIEF );

		if ( is_post_type_archive( CPT_INITIATIEF ) ) {

			$parents[] = array(
				'text' => $obj->label,
			);

		} elseif ( is_tax( CT_INITIATIEFTYPE ) || is_tax( CT_INITIATIEF_PROVINCIE ) ) {
			$parents[] = array(
				'url'  => get_post_type_archive_link( CPT_INITIATIEF ),
				'text' => $obj->label,
			);

		}

	}

	foreach ( $parents as $link ) {
		if ( isset( $link['url'] ) && isset( $link['text'] ) ) {
			$return .= '<a href="' . $link['url'] . '">' . $link['text'] . '</a> ';
		} else {
			$return .= $link['text'] . '  ';
		}
	}

	if ( isset( $post->ID ) && $post->ID === $optionpage ) {
		//
	} elseif ( is_post_type_archive( CPT_INITIATIEF ) ) {
		//
	} else {
		if ( $termid ) {
			$term   = get_term( $termid );
			$return .= $term->name;
		} elseif ( is_singular( CPT_INITIATIEF ) ) {
			$return .= get_the_title( $post->ID );
		} else {
			//
		}
	}

	return $return;

}


//========================================================================================================

function led_initiatieven_list_after( $doreturn = true ) {

	$led_text_after_list = get_theme_mod( 'led_text_after_list' );
	$return              = '';
	if ( $led_text_after_list ) {
		$return = '<div class="led-initiatievenkaart-warning-after"><p>' . $led_text_after_list . '</p></div>';
	}

	if ( $doreturn ) {
		return $return;
	} else {
		echo $return;
	}

}

//========================================================================================================

function led_initiatieven_list_before( $doreturn = true ) {

	$led_text_before_list = get_theme_mod( 'led_text_before_list' );
	$return               = '';
	if ( $led_text_before_list ) {
		$return = '<div class="led-initiatievenkaart-warning-before"><p>' . $led_text_before_list . '</p></div>';
	}

	if ( $doreturn ) {
		return $return;
	} else {
		echo $return;
	}

}

//========================================================================================================

add_filter( 'pre_get_document_title', 'led_initiatieven_add_to_page_titles', 10, 2 ); // standard WordPress hook for <title>
add_filter( 'wp_title', 'led_initiatieven_add_to_page_titles', 10, 2 ); // standard WordPress hook for <title>
add_filter( 'wpseo_title', 'led_initiatieven_add_to_page_titles' ); // hook voor Yoast SEO


function led_initiatieven_add_to_page_titles( $title ) {
	global $wp_query;
	$led_pageid_overview = get_theme_mod( 'customizer_led_pageid_overview' );
	$page_template       = get_post_meta( get_the_id(), '_wp_page_template', true );
	$count               = $wp_query->post_count;

	if ( is_singular( CPT_INITIATIEF ) ) {

		// het is een single voor CPT = CPT_INITIATIEF
		// daarvoor staat de titel al ok.

	} elseif ( is_post_type_archive( CPT_INITIATIEF ) ) {

		// het totaaloverzicht van alle initiatieven
		$title = get_the_title( $led_pageid_overview );

	} elseif ( is_tax( CT_INITIATIEFTYPE ) || is_tax( CT_INITIATIEF_PROVINCIE ) ) {

		// het is een overzicht van initiatieven per type of per provincie
		$term_id = get_queried_object_id();
		$term    = get_term( $term_id, ( is_tax( CT_INITIATIEFTYPE ) ? CT_INITIATIEFTYPE : CT_INITIATIEF_PROVINCIE ) );

		if ( $term && ! is_wp_error( $term ) ) {
			$archive_title = $term->name;
		}

		if ( $count ) {
			if ( is_tax( CT_INITIATIEF_PROVINCIE ) ) {
				// voorzetsels, best belangrijk
				$title = sprintf( _n( '%s initiatief in %s', "%s initiatieven in %s", $count, 'initiatieven-kaart' ), $count, $archive_title );
			} else {
				$title = sprintf( _n( '%s initiatief voor %s', "%s initiatieven voor %s", $count, 'initiatieven-kaart' ), $count, $archive_title );
			}
		} else {
			// Niks geen initiatieven niet. Nul, nada, noppes, nihil.
			// Zeeland en Drenthe, laat van je HO-REN!
			$title = sprintf( _x( 'Geen initiatieven gevonden voor %s', "Geen initiatieven", 'initiatieven-kaart' ), $archive_title );
		}


	} elseif ( 'page-initiatieven.php' == $page_template ) {

		// het totaaloverzicht van alle initiatieven
		$title = get_the_title( $led_pageid_overview );

	}


	return $title;
}
