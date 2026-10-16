<?php

// filter-functions.php

//========================================================================================================

/**
 * Get all terms of a taxonomy, sorted ascending by name.
 */
function initiatievenkaart_projects_get_terms( $taxonomy ) {
	$return = array();
	$terms  = get_terms( array(
		'taxonomy'   => $taxonomy,
		'orderby'    => 'name',
		'order'      => 'ASC',
		'hide_empty' => true, // set to false to also list terms without projects
	) );

	if ( is_wp_error( $terms ) ) {

	} else {
		foreach ( $terms as $term ) {
			$return[] = array( 'label' => $term->name, 'ID' => $term->term_id );

		}
	}

	return $return; //

}

//========================================================================================================

function initiatievenkaart_terms_for_post_type( string $taxonomy, string $post_type, array $args = [] ) {

	$return = array();

	$tax_query = array(
		'relation' => 'AND',
		array(
			'taxonomy' => $taxonomy,
			'operator' => 'EXISTS',
		)
	);

	if ( ( is_tax( CT_PROJECTJAAR ) ) || ( is_tax( CT_PROJECTORGANISATIE ) ) ) {
		// this is an archive. Filter the list projects, to only projects that have the get_queried_object_id()
		$secondtaxonomy = CT_PROJECTORGANISATIE;
		$term_id        = get_queried_object_id();

		if ( is_tax( CT_PROJECTJAAR ) ) {
			$secondtaxonomy = CT_PROJECTJAAR;
		}

		$tax_query[] = array(
			'taxonomy' => $secondtaxonomy,
			'field'    => 'term_id',
			'terms'    => (int) $term_id,
		);

	}

	$args_postids = array(
		'post_type'     => $post_type,
		'post_status'   => 'publish',
		'numberposts'   => - 1,
		'fields'        => 'ids',
		'no_found_rows' => true,
		'tax_query'     => $tax_query
	);

	$post_ids = get_posts( $args_postids );

	if ( empty( $post_ids ) ) {
		return $return; // empty array
	} else {

		$terms = wp_get_object_terms(
			$post_ids,
			$taxonomy,
			wp_parse_args( $args, [ 'orderby' => 'name' ] )
		);

		if ( is_wp_error( $terms ) ) {

		} else {
			foreach ( $terms as $term ) {
				$return[] = array( 'label' => $term->name, 'ID' => $term->term_id );
			}
		}

	}

	return $return; //
}

//========================================================================================================

function innovatieprojecten_form_write( $args = array(), $doreturn = false ) {

	$filter_by_year = isset( $_GET['filter_by_year'] ) ? sanitize_title( wp_unslash( $_GET['filter_by_year'] ) ) : '';
	if ( FILTER_ALL === $filter_by_year ) {
		$filter_by_year = '';
	}
	$filter_by_dossier = isset( $_GET['filter_by_dossier'] ) ? sanitize_title( wp_unslash( $_GET['filter_by_dossier'] ) ) : '';
	if ( FILTER_ALL === $filter_by_dossier ) {
		$filter_by_dossier = '';
	}
	$remove_filter_by_year = isset( $_GET['remove_filter_by_year'] ) ? sanitize_title( wp_unslash( $_GET['remove_filter_by_year'] ) ) : '';
	if ( $remove_filter_by_year === $filter_by_year ) {
		$filter_by_year = '';
	}
	$remove_filter_by_dossier = isset( $_GET['remove_filter_by_dossier'] ) ? sanitize_title( wp_unslash( $_GET['remove_filter_by_dossier'] ) ) : '';
	if ( $remove_filter_by_dossier === $filter_by_dossier ) {
		$filter_by_dossier = '';
	}

	$show_map = ( get_query_var( QUERYVAR_SHOW_MAP ) ) ? sanitize_text_field( get_query_var( QUERYVAR_SHOW_MAP ) ) : QUERYVAR_SHOW_MAP_FALSE;
	if ( $show_map !== QUERYVAR_SHOW_MAP_TRUE ) {
		$show_map = QUERYVAR_SHOW_MAP_FALSE;
	}

	$return = '';


	if ( $doreturn ) {
		return $return;
	} else {
		echo $return;
	}

}

//========================================================================================================


/**
 * Returns a fieldset with radio buttons.
 *
 * @return string HTML
 */
function innovatieprojecten_form_object_radiobutton( $fieldset_name, $name, $options, $currentvalue, $doreturn = false ) {

	$html = '<fieldset id="' . htmlspecialchars( $name ) . '" name="' . htmlspecialchars( $name ) . '">';
	$html .= '<legend>' . $fieldset_name . '</legend>';
	$html .= '<div class="container__radiobutton">';

	foreach ( $options as $value => $label ) {
		$id      = $name . '_' . $value;
		$checked = ( (string) $currentvalue === (string) $value ) ? ' checked' : '';

		$html .= '<div>';
		$html .= '<input type="radio" id="' . htmlspecialchars( $id ) . '" name="' . htmlspecialchars( $name ) . '" value="' . htmlspecialchars( $value ) . '"' . $checked . '>';
		$html .= ' <label for="' . htmlspecialchars( $id ) . '">' . htmlspecialchars( $label ) . '</label>';
		$html .= '</div>';
	}

	$html .= '</div>';
	$html .= '</fieldset>';


	if ( $doreturn ) {
		return $html;
	} else {
		echo $html;
	}


}

//========================================================================================================

/**
 * Render one <select> with an "All" option.
 */
function innovatieprojecten_form_object_select( $id, $label, $terms, $current, $alles_label, $doreturn = false ) {

	$return = '';
	$return .= '<div class="row">';
	$return .= '        <label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label>';
	$return .= '        <select name="' . esc_attr( $id ) . '" id="' . esc_attr( $id ) . '">';
	$return .= '            <option value="' . FILTER_ALL . '">' . esc_html( $alles_label ) . '</option>';
	foreach ( $terms as $term ) :

		$selected = '';
		if ( isset( $current['ID'] ) && ( (int) $current['ID'] === (int) $term['ID'] ) ) {
			$selected = ' selected="selected"';
		}
		$return .= '              <option value="' . esc_attr( $term['ID'] ) . '"' . $selected . '>';
		$return .= esc_html( $term['label'] );
		$return .= '               </option>';
	endforeach;
	$return .= '        </select>';
	$return .= ' </div >';

	if ( $doreturn ) {
		return $return;
	} else {
		echo $return;
	}

}

//========================================================================================================

function led_write_innovatieprojecten_list( $doreturn = false ) {

	global $post;

	$return               = '';
	$initiatieficons      = led_get_initiatieficons();
	$filter_label         = '';
	$list_of_all_years    = initiatievenkaart_terms_for_post_type( CT_PROJECTJAAR , CPT_INNOVATIEPROJECT );
	$list_of_all_dossiers = initiatievenkaart_terms_for_post_type( RHSWP_CT_DOSSIER, CPT_INNOVATIEPROJECT );

	$filter_by_year        = isset( $_GET['filter_by_year'] ) ? sanitize_title( wp_unslash( $_GET['filter_by_year'] ) ) : FILTER_NOREQUEST;
	$filter_by_dossier     = isset( $_GET['filter_by_dossier'] ) ? sanitize_title( wp_unslash( $_GET['filter_by_dossier'] ) ) : FILTER_NOREQUEST;
	$remove_filter_by_year = isset( $_GET['remove_filter_by_year'] ) ? sanitize_title( wp_unslash( $_GET['remove_filter_by_year'] ) ) : '';
	if ( $remove_filter_by_year === $filter_by_year ) {
		$filter_by_year = array();
	} else {
		$index_array = array_search( $filter_by_year, array_column( $list_of_all_years, 'ID' ) );
		if ( false !== $index_array ) {
			$filter_by_year = $list_of_all_years[ $index_array ];
		}
	}


	$remove_filter_by_dossier = isset( $_GET['remove_filter_by_dossier'] ) ? sanitize_title( wp_unslash( $_GET['remove_filter_by_dossier'] ) ) : '';
	if ( $remove_filter_by_dossier === $filter_by_dossier ) {
		$filter_by_dossier = array();
	} else {
		$index_array = array_search( $filter_by_dossier, array_column( $list_of_all_dossiers, 'ID' ) );
		if ( false !== $index_array ) {
			$filter_by_dossier = $list_of_all_dossiers[ $index_array ];
		} else {
			$filter_by_dossier = array();
		}
	}

	$filter_is_taxonomy      = false;
	$filter_taxonomy_term_id = '';
	$filter_taxonomy         = '';

	if ( ( is_tax( CT_PROJECTJAAR ) ) || ( is_tax( CT_PROJECTORGANISATIE ) ) ) {
		// this is an archive for CT_PROJECTJAAR / CT_PROJECTORGANISATIE
		$filter_is_taxonomy = true;
		$filter_taxonomy    = CT_PROJECTORGANISATIE;
		$term_id            = get_queried_object_id();

		if ( is_tax( CT_PROJECTJAAR ) ) {
			$filter_taxonomy = CT_PROJECTJAAR;
			$filter_label    = _x( 'uit %s', "filter by year", 'initiatieven-kaart' );
			$index_array     = array_search( $term_id, array_column( $list_of_all_years, 'ID' ) );
			if ( false !== $index_array ) {
				$filter_by_year = $list_of_all_years[ $index_array ];
			}
		} else {
			$filter_label = _x( 'voor \'%s\'', "filter by other than year", 'initiatieven-kaart' );
			$index_array  = array_search( $filter_by_dossier, array_column( $list_of_all_dossiers, 'ID' ) );
			if ( false !== $index_array ) {
				$filter_by_dossier = $list_of_all_dossiers[ $index_array ];
			}
		}

		$term = get_term( $term_id, $filter_taxonomy );

		if ( $term && ! is_wp_error( $term ) ) {
			$filter_taxonomy_term_id = $term_id;
			$filter_label            = sprintf( $filter_label, $term->name );
		}
	} else {
		// not an archive
		$terms = get_terms( array(
			'taxonomy'   => CT_PROJECTJAAR,   // your taxonomy slug
			'orderby'    => 'name',
			'order'      => 'DESC',
			'number'     => 1,
			'hide_empty' => false,
		) );

		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			$term        = $terms[0]; // WP_Term object
			$recent_year = array( 'label' => $term->name, 'ID' => $term->term_id );
		}

		$index_array = array_search( $filter_by_dossier, array_column( $list_of_all_dossiers, 'ID' ) );
		if ( false !== $index_array ) {
			$filter_by_dossier = $list_of_all_dossiers[ $index_array ];
		}

	}


	$show_map = ( get_query_var( QUERYVAR_SHOW_MAP ) ) ? sanitize_text_field( get_query_var( QUERYVAR_SHOW_MAP ) ) : QUERYVAR_SHOW_MAP_FALSE;
	if ( $show_map !== QUERYVAR_SHOW_MAP_TRUE ) {
		$show_map = QUERYVAR_SHOW_MAP_FALSE;
	}

	$open = '';
	if ( $filter_by_year !== FILTER_NOREQUEST || $filter_by_dossier !== FILTER_NOREQUEST || get_query_var( QUERYVAR_SHOW_MAP ) ) {
		$open = ' open';
	}
	if ( FILTER_ALL === $filter_by_year ) {
		$filter_by_year = array();
	} elseif ( FILTER_NOREQUEST === $filter_by_year ) {
		$filter_by_year = $recent_year;
	}
	if ( FILTER_ALL === $filter_by_dossier ) {
		$filter_by_dossier = '';
	} elseif ( FILTER_NOREQUEST === $filter_by_dossier ) {
		$filter_by_dossier = '';
	}


	// Build the query.
	$args = array(
		'post_type'      => CPT_INNOVATIEPROJECT,
		'post_status'    => 'publish',
		'posts_per_page' => - 1,
		'orderby'        => 'title',
		'order'          => 'ASC',
		'tax_query'      => array( 'relation' => 'AND' ),
	);


	if ( $filter_is_taxonomy ) {
		// is archive
		$args['tax_query'][] = array(
			'taxonomy' => $filter_taxonomy,
			'field'    => 'term_id',
			'terms'    => $filter_taxonomy_term_id,
		);
	}


	if ( isset( $filter_by_year['ID'] ) && ( ! ( is_tax( CT_PROJECTJAAR ) ) ) && ( FILTER_ALL !== $filter_by_year ) ) {
		$args['tax_query'][] = array(
			'taxonomy' => CT_PROJECTJAAR,
			'field'    => 'term_id',
			'terms'    => $filter_by_year['ID'],
		);
	}

	if ( isset( $filter_by_dossier['ID'] ) ) {

		$args['tax_query'][] = array(
			'taxonomy' => RHSWP_CT_DOSSIER,
			'field'    => 'term_id',
			'terms'    => $filter_by_dossier['ID'],
		);
	}

	// No filters selected: drop the empty tax_query.
	if ( count( $args['tax_query'] ) === 1 ) {
		unset( $args['tax_query'] );
	}

	$contentblockpostscount = new WP_Query( $args );

	$action = esc_url( remove_query_arg( array(
		'filter_by_year',
		'filter_by_dossier',
		QUERYVAR_SHOW_MAP
	) ) );


	$return .= '<details id="filter_innovatieprojecten"' . $open . '>';
	$return .= '<summary>' . _x( 'Filter innovatieprojecten', 'taxonomy singular name', 'initiatieven-kaart' ) . '</summary>';
	$return .= '<form class="filter-form" method="get" action="' . $action . '">';
	$return .= '<fieldset>';
	$return .= '<legend>' . esc_html( 'Filter innovatieprojecten', 'initiatieven-kaart' ) . '</legend>';

	if ( ! is_tax( CT_PROJECTJAAR ) ) {
		// we are NOT on an archive for CT_PROJECTJAAR, so do display the year filter
		$return .= innovatieprojecten_form_object_select( 'filter_by_year', esc_html_x( 'Jaar toekenning', 'taxonomy singular name', 'initiatieven-kaart' ), $list_of_all_years, $filter_by_year, _x( '-- alle jaren', 'default jaren', 'initiatieven-kaart' ), true ); // phpcs:ignore WordPress.Security.EscapeOutput
	} else {
		// this an archive for CT_PROJECTJAAR, we do not use a filter for years
		$filter_by_year = array();
	}
	if ( $list_of_all_dossiers) {
		$return .= innovatieprojecten_form_object_select( 'filter_by_dossier', __( 'Onderwerpen', 'initiatieven-kaart' ), $list_of_all_dossiers, $filter_by_dossier, _x( '-- alle onderwerpen', 'default onderwerpen', 'initiatieven-kaart' ), true ); // phpcs:ignore
	}
	$return .= '</fieldset>';

	$options = array(
		QUERYVAR_SHOW_MAP_TRUE  => "Ja, toon kaart",
		QUERYVAR_SHOW_MAP_FALSE => "Nee, toon lijst",
	);

	$return .= '<div id="filter_innovatieprojecten__map_submit" class="column">'; // .column
	$return .= '<div id="filter_innovatieprojecten__map" class="row">'; // .row
	$return .= innovatieprojecten_form_object_radiobutton( 'Toon kaart', QUERYVAR_SHOW_MAP, $options, $show_map, true ); // phpcs:ignore
	$return .= '</div>'; // .row

	$return .= '<div id="filter_innovatieprojecten__submit" class="row">'; // .row
	$return .= '<button type="submit" id="filter_innovatieprojecten_types_submit">' . __( 'Filter', 'initiatieven-kaart' ) . '</button>';
	$return .= '</div>'; // .row
	$return .= '</div>'; // .column


	if ( $filter_by_year || $filter_by_dossier ) :
		$return .= '<div class="column" id="filter_remove">'; // .row

		if ( $filter_by_year && $filter_by_dossier ) {
			$filderedby_title = _x( 'Verwijder filters', 'searchform filter', 'wp-rijkshuisstijl' );
		} else {
			$filderedby_title = _x( 'Verwijder filter', 'searchform filter', 'wp-rijkshuisstijl' );
		}


		$return .= '<p id="filderedby_title">' . $filderedby_title . '</p>';
		$return .= '<ul aria-describedby="filderedby_title" id="filderedby_items">'; // .row
		if ( $filter_by_year && ( ! is_tax( CT_PROJECTJAAR ) ) ) {
			$aria_label   = sprintf( _x( $filderedby_title . " %s ", 'Search - no results', 'wp-rijkshuisstijl' ), $filter_by_year['label'] );
			$return       .= '<li><button aria-label="' . $aria_label . '" name="remove_filter_by_year" value="' . $filter_by_year['ID'] . '" class="remove">' . $filter_by_year['label'] . ' <span aria-hidden="true">&times;</span></button></li>';
			$filter_label = sprintf( _x( 'uit %s', "filter by year", 'initiatieven-kaart' ), $filter_by_year['label'] );
		}
		if ( $filter_by_dossier ) {
			$truncated_label = $filter_by_dossier['label'];
			$aria_label      = sprintf( _x( $filderedby_title . " %s ", 'Search - no results', 'wp-rijkshuisstijl' ), $truncated_label );
			$return          .= '<li><button aria-label="' . $aria_label . '" name="remove_filter_by_dossier" value="' . $filter_by_dossier['ID'] . '" class="remove">' . $filter_by_dossier['label'] . ' <span aria-hidden="true">&times;</span></button></li>';

			$dossier_label = sprintf( _x( 'voor \'%s\'', "filter by other than year", 'initiatieven-kaart' ), $filter_by_dossier['label'] );

			if ( is_tax( CT_PROJECTORGANISATIE )) {
				$organisatie = get_query_var( CT_PROJECTORGANISATIE );
				$organisatie_label = sprintf( _x( 'en \'%s\'', "filter by year", 'initiatieven-kaart' ), $organisatie);
				$filter_label = sprintf( _x( '%s %s', "filter by year and dossier", 'initiatieven-kaart' ), $dossier_label, $organisatie_label );

			} elseif ( $filter_by_year || is_tax( CT_PROJECTJAAR )) {
				if ($filter_by_year ) {
					$year = $filter_by_year['label'];
				} else {
					$year = get_query_var( CT_PROJECTJAAR );
				}
				$year_label = sprintf( _x( 'uit %s', "filter by year", 'initiatieven-kaart' ), $year);
				$filter_label = sprintf( _x( '%s %s', "filter by year and dossier", 'initiatieven-kaart' ), $year_label, $dossier_label );



			} else {
				$filter_label = sprintf( _x( 'voor \'%s\'', "filter by other than year", 'initiatieven-kaart' ), $filter_by_dossier['label'] );
			}
		}

		$return .= '</ul>';
		$return .= '</div>'; // .column
	endif;

	$return .= '</form>';
	$return .= '</details>';

//---
	if ( $contentblockpostscount->post_count ) {
		$counter = $contentblockpostscount->post_count;
	} else {
		$counter = _x( 'Geen', 'Geen project gevonden', 'initiatieven-kaart' );
	}
	$return .= '<h2>' . sprintf( _n( '%s innovatieproject %s', "%s innovatieprojecten %s", $contentblockpostscount->post_count, 'initiatieven-kaart' ), $counter, strtolower( $filter_label ) ) . '</h2>';

	if ( $contentblockpostscount->have_posts() ) {

		$return .= led_initiatieven_list_before( true );

		if ( $show_map === QUERYVAR_SHOW_MAP_TRUE ) {
			$return .= '<ul id="map-items" class="map-items">';
		} else {
			$return .= '<ul class="map-items">';
		}

		while ( $contentblockpostscount->have_posts() ) : $contentblockpostscount->the_post();

			$return .= led_get_list_item_archive( $post, $initiatieficons, CT_PROJECTORGANISATIE );

		endwhile;

		$return .= '</ul>';

	} else {
		$return .= '<p>' . _x( 'Geen innovatieprojecten gevonden. Probeer een filter weg te halen om te zien of er dan wel projecten gevonden worden.', 'led_write_innovatieprojecten_list', 'initiatieven-kaart' ) . '</p>';
	}
	$return .= led_initiatieven_list_after( true );

	wp_reset_query();
	wp_reset_postdata();

	if ( $doreturn ) {
		return $return;
	} else {
		echo $return;
	}

}

//========================================================================================================

