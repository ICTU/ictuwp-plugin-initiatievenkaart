<?php

/**
 * LED initiatieven-kaart
 *
 * @link              https://www.digitaleoverheid.nl/
 * @since             1.0.0
 * @package           Initiatieven_Kaart
 *
 * @wordpress-plugin
 * Plugin Name:       ICTU / WP / DO - initiatievenkaart
 * Plugin URI:        https://digitaleoverheid.nl/initiatieven-kaart-uri/
 * Description:       Toont LED initiatieven op een kaart
 * Version:           2.0.2.a
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
define( 'INITIATIEVEN_KAART_VERSION', '2.0.2.a' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-initiatieven-kaart.php';

// in dit bestand staan de veld-definities voor de ACF-velden
require plugin_dir_path( __FILE__ ) . 'includes/acf-definitions.php';

// in dit bestand staan de veld-definities voor de ACF-velden
require plugin_dir_path( __FILE__ ) . 'includes/filter-functions.php';


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
	add_action( 'pre_get_posts', array( $plugin, 'load_all_initiatieven_innovatieprojecten' ), 999 );
}

run_initiatieven_kaart();

//========================================================================================================
/*
 * filter voor overzicht (archive + taxonomy) van de initiatieven
 */
function led_template_archive_initiatieven( $archive_template ) {
	global $post;

	if ( is_search() ) {
		return $archive_template;
	}

	if ( is_post_type_archive( CPT_INNOVATIEPROJECT ) ) {
		// het is een archive voor CPT = CPT_INNOVATIEPROJECT
		$archive_template = dirname( __FILE__ ) . '/templates/page-innovatieprojecten.php';
	} elseif ( ( is_tax( CT_PROJECTORGANISATIE ) ) || ( is_tax( CT_PROJECTJAAR ) ) ) {
		// het is een overzicht van innovatieprojecten per type of per provincie
		$archive_template = dirname( __FILE__ ) . '/templates/archive-innovatieprojecten.php';
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

	if ( is_search() ) {
		return $archive_template;
	}

	$page_template = get_post_meta( get_the_id(), '_wp_page_template', true );

	if ( is_singular( CPT_INNOVATIEPROJECT ) ) {
		// het is een single voor CPT = CPT_INNOVATIEPROJECT
		$archive_template = dirname( __FILE__ ) . '/templates/single-innovatieproject.php';

	} elseif ( is_post_type_archive( CPT_INNOVATIEPROJECT ) ) {

		// het is een overzicht van initiatieven per type of per provincie
		$archive_template = dirname( __FILE__ ) . '/templates/page-innovatieprojecten.php';

	} elseif ( 'page-innovatieproject.php' == $page_template ) {

		// het is een overzicht van innovatieprojecten
		$archive_template = dirname( __FILE__ ) . '/templates/page-innovatieprojecten.php';

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
function innovatiebudget_cpt_and_taxonomies() {

	// ---------------------------------------------------------------------------------------------------
	$slug_innovatieprojecten = CPT_INNOVATIEPROJECT;

	$args = array(
		'label'               => esc_html__( CPT_INNOVATIEPROJECT, 'initiatieven-kaart' ),
		'description'         => '',
		'labels'              => array(
			'name'                  => esc_html_x( 'Innovatieprojecten', 'post type', 'initiatieven-kaart' ),
			'singular_name'         => esc_html_x( 'Innovatieproject', 'post type', 'initiatieven-kaart' ),
			'menu_name'             => esc_html_x( 'Innovatieprojecten', 'post type', 'initiatieven-kaart' ),
			'name_admin_bar'        => esc_html_x( 'Innovatieproject', 'post type', 'initiatieven-kaart' ),
			'archives'              => esc_html_x( 'Overzicht innovatieprojecten', 'post type', 'initiatieven-kaart' ),
			'attributes'            => esc_html_x( 'Eigenschappen innovatieproject', 'post type', 'initiatieven-kaart' ),
			'parent_item_colon'     => esc_html_x( 'Parent Map:', 'post type', 'initiatieven-kaart' ),
			'all_items'             => esc_html_x( 'Alle innovatieprojecten', 'post type', 'initiatieven-kaart' ),
			'add_new_item'          => esc_html_x( 'Innovatieproject toevoegen', 'post type', 'initiatieven-kaart' ),
			'add_new'               => esc_html_x( 'Toevoegen', 'post type', 'initiatieven-kaart' ),
			'new_item'              => esc_html_x( 'Nieuw innovatieproject', 'post type', 'initiatieven-kaart' ),
			'edit_item'             => esc_html_x( 'Bewerk innovatieproject', 'post type', 'initiatieven-kaart' ),
			'update_item'           => esc_html_x( 'Update innovatieproject', 'post type', 'initiatieven-kaart' ),
			'view_item'             => esc_html_x( 'Bekijk innovatieproject', 'post type', 'initiatieven-kaart' ),
			'view_items'            => esc_html_x( 'Bekijk innovatieprojecten', 'post type', 'initiatieven-kaart' ),
			'search_items'          => esc_html_x( 'Zoek innovatieproject', 'post type', 'initiatieven-kaart' ),
			'not_found'             => esc_html_x( 'Not found', 'post type', 'initiatieven-kaart' ),
			'not_found_in_trash'    => esc_html_x( 'Not found in Trash', 'post type', 'initiatieven-kaart' ),
			'featured_image'        => esc_html_x( 'Featured Image', 'post type', 'initiatieven-kaart' ),
			'set_featured_image'    => esc_html_x( 'Set featured image', 'post type', 'initiatieven-kaart' ),
			'remove_featured_image' => esc_html_x( 'Remove featured image', 'post type', 'initiatieven-kaart' ),
			'use_featured_image'    => esc_html_x( 'Use as featured image', 'post type', 'initiatieven-kaart' ),
			'insert_into_item'      => esc_html_x( 'Insert into Map', 'post type', 'initiatieven-kaart' ),
			'uploaded_to_this_item' => esc_html_x( 'Uploaded to this innovatieproject', 'post type', 'initiatieven-kaart' ),
			'items_list'            => esc_html_x( 'Map list', 'post type', 'initiatieven-kaart' ),
			'items_list_navigation' => esc_html_x( 'Maps list navigation', 'post type', 'initiatieven-kaart' ),
			'filter_items_list'     => esc_html_x( 'Filter initiatief list', 'post type', 'initiatieven-kaart' ),
		),
		'supports'            => array(
			'title',
			'author',
			'excerpt',
			'editor',
			'genesis-simple-sidebars', // adds the sidebar selector metabox
			'genesis-layouts',         // optional: layout selector per post
		),
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
		'rewrite'             => array( 'slug' => $slug_innovatieprojecten ),
		'capability_type'     => 'post'
	);

	register_post_type( CPT_INNOVATIEPROJECT, $args );


	// Type organisatie; dit is een taxonomy zodat we innovatieprojecten kunnen groeperen.
	$taxonomy_terms = array(
		'name'              => esc_html_x( 'Type organisatie', 'taxonomy', 'initiatieven-kaart' ),
		'singular_name'     => esc_html_x( 'Organisatie', 'taxonomy singular name', 'initiatieven-kaart' ),
		'search_items'      => esc_html_x( 'Search organisatie', 'taxonomy', 'initiatieven-kaart' ),
		'all_items'         => esc_html_x( 'All organisaties', 'taxonomy', 'initiatieven-kaart' ),
		'parent_item'       => esc_html_x( 'Parent organisatie', 'taxonomy', 'initiatieven-kaart' ),
		'parent_item_colon' => esc_html_x( 'Parent organisatie:', 'taxonomy', 'initiatieven-kaart' ),
		'edit_item'         => esc_html_x( 'Edit organisatie', 'taxonomy', 'initiatieven-kaart' ),
		'update_item'       => esc_html_x( 'Update organisatie', 'taxonomy', 'initiatieven-kaart' ),
		'add_new_item'      => esc_html_x( 'Add new organisatie', 'taxonomy', 'initiatieven-kaart' ),
		'new_item_name'     => esc_html_x( 'New organisatie name', 'taxonomy', 'initiatieven-kaart' ),
		'menu_name'         => esc_html_x( 'Type organisatie', 'taxonomy', 'initiatieven-kaart' ),
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $taxonomy_terms,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => CT_PROJECTORGANISATIE ),
	);

	register_taxonomy( CT_PROJECTORGANISATIE, array( CPT_INNOVATIEPROJECT ), $args );

	// Type organisatie; dit is een taxonomy zodat we innovatieprojecten kunnen groeperen.
	$taxonomy_terms = array(
		'name'              => esc_html_x( 'Jaar toekenning', 'taxonomy', 'initiatieven-kaart' ),
		'singular_name'     => esc_html_x( 'Jaar toekenning', 'taxonomy singular name', 'initiatieven-kaart' ),
		'search_items'      => esc_html_x( 'Search jaar', 'taxonomy', 'initiatieven-kaart' ),
		'all_items'         => esc_html_x( 'Alle jaren', 'taxonomy', 'initiatieven-kaart' ),
		'parent_item'       => esc_html_x( 'Parent jaar', 'taxonomy', 'initiatieven-kaart' ),
		'parent_item_colon' => esc_html_x( 'Parent jaar:', 'taxonomy', 'initiatieven-kaart' ),
		'edit_item'         => esc_html_x( 'Edit jaar', 'taxonomy', 'initiatieven-kaart' ),
		'update_item'       => esc_html_x( 'Update jaar', 'taxonomy', 'initiatieven-kaart' ),
		'add_new_item'      => esc_html_x( 'Add New jaar', 'taxonomy', 'initiatieven-kaart' ),
		'new_item_name'     => esc_html_x( 'New jaar Name', 'taxonomy', 'initiatieven-kaart' ),
		'menu_name'         => esc_html_x( 'Jaar toekenning', 'taxonomy', 'initiatieven-kaart' ),
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $taxonomy_terms,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => CT_PROJECTJAAR ),
	);

	register_taxonomy( CT_PROJECTJAAR, array( CPT_INNOVATIEPROJECT ), $args );

	add_filter( 'query_vars', function ( $vars ) {
		$vars[] = QUERYVAR_SHOW_MAP;

		return $vars;
	} );


}

// Trigger registering the post type as soon as possible
add_action( 'init', 'innovatiebudget_cpt_and_taxonomies' );

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
		'taxonomy'   => CT_PROJECTORGANISATIE,
		'hide_empty' => true,
		'orderby'    => 'name',
		'order'      => 'ASC',
	];
	$terms = get_terms( $args );

	if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
		$count = count( $terms );


		foreach ( $terms as $term ) {

			$initiatief_type_icon = get_field( 'organisatie_type_icon', CT_PROJECTORGANISATIE . '_' . $term->term_id );

			$array = array(
				'slug' => $term->slug,
				'name' => $term->name,
				'icon' => 'onbekend',
			);

			if ( $initiatief_type_icon ) {
				$array['icon'] = $initiatief_type_icon;
			}

			$arr_initiatief_type_icon[ $term->slug ] = $array;
		}
	}

	return $arr_initiatief_type_icon;

}

//========================================================================================================
/*
 * deze functie formatteert een list-item voor de lijst met initiatieven
 */
function get_metadata_for_post( $postobject, $taxonomy = CT_PROJECTORGANISATIE, $arguments = array() ) {

	$return = array();

	if ( $taxonomy ) {

		if ( function_exists( 'rhswp_translateposttypes' ) ) {
			$return['label'] = rhswp_translateposttypes( $taxonomy );
		} else {
			$type            = get_taxonomy( $taxonomy );
			$return['label'] = $type->labels->singular_name;
		}
		$return['items'] = array();
		$terms           = get_the_terms( $postobject->ID, $taxonomy );

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$return['items'][] = $term;
			}
		}
	}

	return $return;
}

//========================================================================================================
/*
 * deze functie formatteert een list-item voor de lijst met initiatieven
 */
function led_get_list_item_archive( $postobject, $initiatieficons = array(), $selected_taxonomy = CT_PROJECTORGANISATIE ) {

	$return = '';

	// use the location attributes to create data-attributes for the map
	// second term false: current post
	$locationField  = get_field( 'openstreet_map', $postobject->ID );
	$title          = get_the_title( $postobject->ID );
	$permalink      = led_get_initiatief_permalink( $postobject->ID );
	$jaren          = get_metadata_for_post( $postobject, CT_PROJECTJAAR );
	$organisaties   = get_metadata_for_post( $postobject, CT_PROJECTORGANISATIE );
	$dossiers       = get_metadata_for_post( $postobject, RHSWP_CT_DOSSIER );
	$meta_data      = '';
	$taxonomy_terms = '';
	$classes        = array();

	/*
	 * haal de intitieftypes op. Dit kunnen er meerdere zijn, op dit moment.
	 * dit is de taxonomie $selected_taxonomy
	 * aan elke waarde hiervan zou een icoontje moeten hangen
	 * dus bijv. type 'Community' krijgt een icoontje 'community'
	 */


	if ( $organisaties && ! is_wp_error( $organisaties ) ) :
		// check in welk initiatieftype dit initiatieftype zit
		// aan dit type hangt o.m. het icoontje
		// NB op dit moment is het praktisch mogelijk om een initiatief aan
		// MEERDERE initiatieftypes te hangen

		$counter        = 0;
		$taxonomy_terms = '<span>';
		$taxonomy_terms .= $organisaties['label'] . ': ';
		$taxonomy_terms .= '</span>';

		foreach ( $organisaties['items'] as $item ) {
			$counter ++;
			// het icoontje dat bij dit initiatieftype hoort, staat in de array $initiatieficons
			if ( isset( $initiatieficons[ $item->slug ] ) ) {
				array_push( $classes, $initiatieficons[ $item->slug ]['name'] );
			} else {
				array_push( $classes, $item->slug );
			}
			if ( $counter > 1 ) {
				$taxonomy_terms .= ', ';
			}
			$taxonomy_terms .= '<span class="' . $item->slug . '">' . $item->name . '</span>';
		}

		$meta_data = '<span class="initiatieftype">';
		$meta_data .= $taxonomy_terms;
		$meta_data .= '</span>';

	endif;

	if ( $jaren['items'] && ! is_wp_error( $jaren ) ) :

		$counter = 0;
		if ( $meta_data ) {
			$meta_data .= '<span>' . DO_SEPARATOR;
		} else {
			$meta_data .= '<span>';
		}
		$meta_data .= $jaren['label'] . ': ';
		$meta_data .= '</span>';

		foreach ( $jaren['items'] as $item ) {
			$counter ++;
			if ( $counter > 1 ) {
				$meta_data .= ', ';
			}
			$meta_data .= '<span class="' . $item->slug . '">' . $item->name . '</span>';
		}

	endif;


	if ( $dossiers['items'] && ! is_wp_error( $dossiers ) ) :

		$counter = 0;
		if ( $meta_data ) {
			$meta_data .= '<span>' . DO_SEPARATOR;
		} else {
			$meta_data .= '<span>';
		}
		$meta_data .= $dossiers['label'] . ': ';
		$meta_data .= '</span>';

		foreach ( $dossiers['items'] as $item ) {
			$counter ++;
			if ( $counter > 1 ) {
				$meta_data .= ', ';
			}
			$meta_data .= '<span class="' . $item->slug . '">' . $item->name . '</span>';
		}

	endif;



	if ( $locationField != false ) {
		// er zijn locatie-gegevens voor dit initiatief
		$plaatsnaam = get_field( 'locatie_plaatsnaam', $postobject->ID );

		// TB: gebruik de lat/lon van de eerste marker als locatie
		// zie https://github.com/mcguffin/acf-openstreetmap-field/wiki/Usage
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
		$return .= sprintf( "\n" . '<div class="meta">%s</div>', $meta_data );

		// iets van een samenvatting, beschrijving tonen hier
		$return .= sprintf( '<p>%s</p>', wp_strip_all_tags( get_the_excerpt() ) );
		$return .= "\n</li>";
	} else {
		// geen locationField , wel een list item toevoegen, maar zonder de data attributen voor locatie?
		// nog bepalen wat te doen, obv daarvan evt refactoren met code hierboven
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

	$customizer_pageid_led = get_theme_mod( 'customizer_led_pageid_overview' );

	if ( $customizer_pageid_led ) {

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
		'title'       => _x( 'Kaart innovatiebudget', 'customizer menu', 'initiatieven-kaart' ),
		'capability'  => 'edit_theme_options',
		'description' => _x( 'Instellingen voor de pagina met lijst van innovatieprojecten.', 'customizer menu', 'initiatieven-kaart' ),
	) );

	// settings voor de pagina met innovatiebudgetprojecten
	// add two text fields
	$wp_customize->add_setting( 'innovatiebudget_text_before_list', array(
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'led_sanitize_text_block',
	) );
	$wp_customize->add_control( 'innovatiebudget_text_before_list', array(
		'type'        => 'textarea',
		'section'     => 'customizer_led_initiatievenkaart', // Add a default or your own section
		'label'       => _x( 'Tekst vóór de innovatieprojecten-kaart', 'customizer menu', 'initiatieven-kaart' ),
		'description' => _x( 'Tekst die <em>direct voor</em> de innovatieprojecten-kaart staat', 'customizer menu', 'initiatieven-kaart' ),
	) );

	$wp_customize->add_setting( 'innovatiebudget_text_after_list', array(
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'led_sanitize_text_block',
	) );
	$wp_customize->add_control( 'innovatiebudget_text_after_list', array(
		'type'        => 'textarea',
		'section'     => 'customizer_led_initiatievenkaart', // Add a default or your own section
		'label'       => _x( 'Tekst ná de innovatieprojecten-kaart', 'customizer menu', 'initiatieven-kaart' ),
		'description' => _x( 'Tekst die <em>direct na</em> de data-initiatiefkaart staat', 'customizer menu', 'initiatieven-kaart' ),
	) );

	// add dropdown with pages to appoint the new slug for the CPT
	$wp_customize->add_setting( 'customizer_innovatieproject_pageid_overview', array(
		'capability'        => 'edit_theme_options',
		'sanitize_callback' => 'led_sanitize_project_pagina',
	) );
	$wp_customize->add_control( 'customizer_innovatieproject_pageid_overview', array(
		'type'        => 'dropdown-pages',
		'section'     => 'customizer_led_initiatievenkaart', // Add a default or your own section
		'label'       => _x( 'Pagina met alle innovatie-projecten', 'customizer menu', 'initiatieven-kaart' ),
		'description' => _x( 'In het kruimelpad en in de URL voor een innovatie-project zal deze pagina terugkomen.', 'customizer menu', 'initiatieven-kaart' ),
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
function led_sanitize_project_pagina( $page_id, $setting ) {

	$value = $setting->default;

	// Alleen een geldige ID accepteren
	$page_id = absint( $page_id );

	if ( $page_id ) {

		// Is de pagina wel gepubliceerd?
		if ( 'publish' != get_post_status( $page_id ) ) {
			// niet gepubliceerd, return default
			return $value;
		}

		$value = $page_id;

	}

	error_log( 'innovatiekaart default: ' . $setting->default . ', value: ' . $value . ', page_id: ' . $page_id );

	return $value;

}

//========================================================================================================

// zorg dat een geldige pagina wordt teruggegeven
function led_sanitize_initiatief_pagina( $page_id, $setting ) {

	$value = $setting->default;

	// Alleen een geldige ID accepteren
	$page_id = absint( $page_id );

	if ( $page_id ) {

		if ( 'publish' != get_post_status( $page_id ) ) {
			// alleen geubliceerde pagina's accepteren
			return $value;
		}

		$value = $page_id;

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

	if ( is_search() ) {
		return;
	}

	$archive_title                      = _x( 'Innovatieprojecten', 'Archive initiatieven', 'initiatieven-kaart' );
	$archive_description                = '';
	$return                             = '';
	$count                              = $wp_query->post_count;
	$customizer_pageid_innovatieproject = get_theme_mod( 'customizer_innovatieproject_pageid_overview' );
	$customizer_pageid_led              = get_theme_mod( 'customizer_led_pageid_overview' );

	if ( is_page() || is_post_type_archive( CPT_INNOVATIEPROJECT ) ) {

		if ( is_post_type_archive( CPT_INNOVATIEPROJECT ) ) {
			// anders is de paginatitel het label dat we aan het CPT hebben gegeven
			// info ophalen voor custom post type CPT_INNOVATIEPROJECT
			$obj = get_post_type_object( CPT_INNOVATIEPROJECT );

			if ( $obj->labels->singular_name ) {
				$archive_title = $obj->labels->archives;
			}

		} else {

			$pageid = get_the_id();

			if ( $customizer_pageid_led === $pageid ) {
				$content_post        = get_post( $customizer_pageid_led );
				$archive_title       = get_the_title( $customizer_pageid_led );
				$content             = $content_post->post_content;
				$archive_description = apply_filters( 'the_content', $content );

			} elseif ( $customizer_pageid_innovatieproject === $pageid ) {

				$content_post        = get_post( $customizer_pageid_innovatieproject );
				$archive_title       = get_the_title( $customizer_pageid_innovatieproject );
				$content             = $content_post->post_content;
				$archive_description = apply_filters( 'the_content', $content );

			}

		}
		$return = '<h1>' . $archive_title . '</h1>';

	} elseif ( ( is_tax( CT_PROJECTJAAR ) ) || ( is_tax( CT_PROJECTORGANISATIE ) ) ) {
		// we kijken naar een lijst van initiatieven per initiatieftype of provincie

		$thetax = CT_PROJECTORGANISATIE;
		if ( is_tax( CT_PROJECTJAAR ) ) {
			$thetax       = CT_PROJECTJAAR;
			$filter_label = _x( 'uit %s', "filter by year", 'initiatieven-kaart' );
		} else {
			$filter_label = _x( 'voor \'%s\'', "filter by other than year", 'initiatieven-kaart' );
		}

		$term_id = get_queried_object_id();
		$term    = get_term( $term_id, $thetax );

		if ( $term && ! is_wp_error( $term ) ) {
			$archive_title = $term->name;
			if ( $term->description ) {
				$archive_description = $term->description;
			}
		}

		if ( $count ) {
			$return = '<h1>' . sprintf( _n( '1 innovatieproject voor %s', "Alle innovatieprojecten %s", $count, 'initiatieven-kaart' ), sprintf( $filter_label, strtolower( $archive_title ) ) ) . '</h1>';
		} else {
			// Niks geen initiatieven niet. Nul, nada, noppes, nihil.
			// Zeeland en Drenthe, laat van je HO-REN!
			$return              = '<h1>' . sprintf( _x( 'Geen innovatieprojecten gevonden voor %s', "Geen innovatieprojecten", 'initiatieven-kaart' ), $archive_title ) . '</h1>';
			$archive_description = 'Sorry.';
		}

	} else {
		$return = '<h1>' . $archive_title . '</h1>';

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

	$return        = '';
	$page_template = get_post_meta( get_the_id(), '_wp_page_template', true );

	if ( is_tax( CT_PROJECTORGANISATIE ) ) {
		// toon de lijst van ANDERE type organisaties
		$return .= led_initiatieven_show_taxonomy_list( CT_PROJECTORGANISATIE, __( 'Andere organisaties', 'taxonomie-lijst', 'initiatieven-kaart' ), false, get_queried_object_id() );
	} elseif ( is_tax( CT_PROJECTJAAR ) ) {
		// toon de lijst van ANDERE projectjaren
		$return .= led_initiatieven_show_taxonomy_list( CT_PROJECTJAAR, __( 'Andere jaren', 'taxonomie-lijst', 'initiatieven-kaart' ), false, get_queried_object_id() );
	} elseif ( is_post_type_archive( CPT_INNOVATIEPROJECT ) || 'page-innovatieproject.php' == $page_template ) {
		// toon de lijst van organisatietypes
		$return .= led_initiatieven_show_taxonomy_list( CT_PROJECTORGANISATIE, __( 'Type organisaties', 'taxonomie-lijst', 'initiatieven-kaart' ), false, get_queried_object_id() );
		// toon de lijst van jaren
		$return .= led_initiatieven_show_taxonomy_list( CT_PROJECTJAAR, __( 'Jaren', 'taxonomie-lijst', 'initiatieven-kaart' ), false, get_queried_object_id() );
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

function projecten_initiatieven_filter_breadcrumb( $crumb = '', $args = '' ) {

	global $post;

	$object = get_post_type_object( CPT_INNOVATIEPROJECT );

	if ( ! (
		is_singular( CPT_INNOVATIEPROJECT ) ||
		is_post_type_archive( CPT_INNOVATIEPROJECT ) ||
		is_tax( CT_PROJECTORGANISATIE ) ||
		is_tax( CT_PROJECTJAAR ) ) ) {
		// niks doen we niet met een initiatief bezig zijn
		return $crumb;
	}


	// uit siteopties de pagina ophalen die het overzicht is van alle links

	$page_initatieven = get_theme_mod( 'customizer_innovatieproject_pageid_overview' );
	$currentitem      = explode( '</span>', $crumb );
	$parents          = array();
	$return           = '';
	$termid           = '';
	if (
		is_tax( CT_PROJECTORGANISATIE ) ||
		is_tax( CT_PROJECTJAAR ) ) {
		$termid = get_queried_object_id();
	}

	if ( $page_initatieven ) {

		// haal de ancestors op voor deze pagina
		$ancestors = get_post_ancestors( $page_initatieven );

		$parents[] = array(
			'url'  => get_page_link( $page_initatieven ),
			'text' => get_the_title( $page_initatieven ),
		);

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

		// er is geen pagina bekend waaronder de items getoond worden
		if ( is_singular( CPT_INNOVATIEPROJECT ) ) {
			return $crumb;
		}

		$obj = get_post_type_object( CPT_INNOVATIEPROJECT );

		if ( is_post_type_archive( CPT_INNOVATIEPROJECT ) ) {

			$parents[] = array(
				'text' => $obj->label,
			);

		} elseif ( is_tax( CT_PROJECTORGANISATIE ) ) {
			$parents[] = array(
				'url'  => get_post_type_archive_link( CPT_INNOVATIEPROJECT ),
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

	if ( isset( $post->ID ) && $post->ID === $page_initatieven ) {
		//
	} else {
		if ( $termid ) {
			$term   = get_term( $termid );
			$return .= $term->name;
		} elseif ( is_singular( CPT_INNOVATIEPROJECT ) ) {
			$return .= get_the_title( $post->ID );
		} else {
			//
		}
	}

	return $return;

}


//========================================================================================================

function led_initiatieven_list_after( $doreturn = true ) {

	$text_after_list = get_theme_mod( 'innovatiebudget_text_after_list' );
	$return          = '';
	if ( $text_after_list ) {
		$return = '<div class="led-initiatievenkaart-warning-after"><p>' . $text_after_list . '</p></div>';
	}

	if ( $doreturn ) {
		return $return;
	} else {
		echo $return;
	}

}

//========================================================================================================

function led_initiatieven_list_before( $doreturn = true ) {

	$text_before_list = get_theme_mod( 'innovatiebudget_text_before_list' );
	$return           = '';
	if ( $text_before_list ) {
		$return = '<div class="led-initiatievenkaart-warning-before"><p>' . $text_before_list . '</p></div>';
	}

	if ( $doreturn ) {
		return $return;
	} else {
		echo $return;
	}

}

//========================================================================================================
