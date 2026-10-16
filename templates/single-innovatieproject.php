<?php


if ( function_exists( 'genesis' ) ) {

	// Genesis wordt gebruikt als framework
	// dit geldt voor o.m. het theme voor Digitale Overheid

	// kruimelpad bijwerken
	add_filter( 'genesis_single_crumb', 'projecten_initiatieven_filter_breadcrumb', 10, 2 );

	// geen meuk over publicatiedatums etc tonen
	remove_action( 'genesis_entry_header', 'genesis_post_info', 12 );

	// extra informatie toevoegen
	add_action( 'genesis_after_entry_content', 'led_project_single_info' );

    // extra widget ruimte
    add_action( 'genesis_entry_footer', 'rhswp_append_widgets_before_socmed_footer', 16 );

    // social media share buttons
    add_action( 'genesis_entry_footer', 'rhswp_append_socialbuttons', 18 );

    // extra widget ruimte
    add_action( 'genesis_entry_footer', 'rhswp_append_widgets_after_socmed_footer', 20 );


	// make it so
	genesis();

} else {

	// geen Genesis
	global $post;

	get_header(); ?>

	<div id="primary" class="content-area">
		<div id="content" class="clearfix">

			<h1><?php the_title() ?></h1>
			<?php the_content() ?>
			<?php echo led_project_single_info() ?>

		</div><!-- #content -->
	</div><!-- #primary -->


	<?php

	get_sidebar();

	get_footer();


}

//========================================================================================================

function led_project_single_info( $doreturn = false ) {
	$bla = '';
	global $post;

	$desciptionlist  = '';
	$return          = '';
	$jaren           = array();
	$organisatietype = get_the_terms( get_the_id(), CT_PROJECTORGANISATIE );

	// haal de waarden op uit de ACF-velden
	$locationField         = get_field( 'openstreet_map' );
	$straatnaam_huisnummer = get_field( 'locatie_straatnaam_huisnummer' );
	$locatie_postcode      = get_field( 'locatie_postcode' );
	$regievoerder          = get_field( 'innovatieproject_regievoerder' );
	$partners              = get_field( 'innovatieproject_partners' );
	$plaatsnaam            = get_field( 'locatie_plaatsnaam' );


	if ( $organisatietype && ! is_wp_error( $organisatietype ) ) :
		// check in welk initiatieftype dit initiatieftype zit
		// aan dit type hangt o.m. het icoontje
		// NB op dit moment is het praktisch mogelijk om een initiatief aan
		// MEERDERE initiatieftypes te hangen

		$classes               = array();
		$organisatietype_items = '';
		$jaren_items           = '';
		$jaren_label           = _x( 'Jaar', 'Label type organisatie', 'initiatieven-kaart' );

		foreach ( $organisatietype as $term ) {
			$organisatietype_items .= '<dd class="' . $term->slug . '">' . $term->name . '</dd>';
		}

		// bepalen aan welke provincie(s) dit initiatief hangt
		$terms = get_the_terms( get_the_id(), CT_PROJECTJAAR );
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			$counter = 0;
			if ( count( $terms ) > 1 ) {
				$jaren_label = _x( 'Jaren', 'Label type organisatie', 'initiatieven-kaart' );
			}
			$jaren_items .= '<dd class="' . $term->slug . '">';
			foreach ( $terms as $term ) {
				$counter++;
				if ( $counter > 1) {
					$jaren_items .= ', ';
				}
				$jaren_items .= $term->name;
			}
			$jaren_items .= '</dd>';
		}

        if ( has_term( '', RHSWP_CT_DOSSIER, get_the_id() ) ) {
            // get dossier terms and their links

            $terms = get_the_terms( $post->ID, RHSWP_CT_DOSSIER );
            if ( $terms && ! is_wp_error( $terms ) ) {

                $labels = '';

                foreach ( $terms as $term ) {

                    $labels .= '<dd>';
                    $labels .= '<a href="';
                    if ( function_exists( 'rhswp_get_pagelink_for_dossier' ) ) {
                        $labels .= rhswp_get_pagelink_for_dossier( $term );
                    } else {
                        $labels .= get_term_link( $term->term_id, RHSWP_CT_DOSSIER );
                    }
                    $labels .= '">' . $term->name . '</a>';

                    if ( next( $terms ) ) {
                        $labels .= ', ';
                    }
                    $labels .= '</dd>';

                }


                if ( $labels ) {
                    $dossiers_label = _x( 'Onderwerpen', 'Label type dossiers', 'initiatieven-kaart' );
                    $dossiers = $labels;
                }

            }
        }


        if ( $organisatietype_items || $jaren_items || $regievoerder || $partners ) {
            $desciptionlist = '<dl>';
            if ( $regievoerder ) {
                $desciptionlist .= '<div>';
                $desciptionlist .= '<dt>' . _x( 'Regievoerder', 'Label type organisatie', 'initiatieven-kaart' ) . '</dt>';
                $desciptionlist .= '<dd>' . $regievoerder . '</dd>';
                $desciptionlist .= '</div>';
            }
            if ( $partners ) {
                $desciptionlist .= '<div>';
                $desciptionlist .= '<dt>' . _x( 'Partners', 'Label type organisatie', 'initiatieven-kaart' ) . '</dt>';
                $desciptionlist .= '<dd>' . $partners . '</dd>';
                $desciptionlist .= '</div>';
            }
            if ( $organisatietype_items ) {
                $desciptionlist .= '<div>';
                $desciptionlist .= '<dt>' . _x( 'Type organisatie', 'Label type organisatie', 'initiatieven-kaart' ) . '</dt>';
                $desciptionlist .= $organisatietype_items;
                $desciptionlist .= '</div>';
            }
            if ( $jaren_items ) {
                $desciptionlist .= '<div>';
                $desciptionlist .= '<dt>' . $jaren_label . '</dt>';
                $desciptionlist .= $jaren_items;
                $desciptionlist .= '</div>';
            }
            if ( $dossiers ) {
                $desciptionlist .= '<div>';
                $desciptionlist .= '<dt>' . $dossiers_label . '</dt>';
                $desciptionlist .= $dossiers;
                $desciptionlist .= '</div>';
            }
            $desciptionlist .= '</dl>';

        }


	endif;


	if ( $locationField != false ) {
		// er zijn locatie-gegevens voor dit initiatief
		// TODO: misschien willen we bij een single initiatief ook een kaart tonen?
		//		$return .= sprintf( '<li class="map-item" data-latitude="%s" data-longitude="%s" data-map-item-type="%s">', $locationField["lat"], $locationField["lng"], join( " ", $classes ) );
		//		$return .= '</li>';
	}

	// Initiatieftype
	if ( $desciptionlist ) {
		$return .= sprintf( '%s', $desciptionlist );
	}

	// Adresgegevens
	if ( $jaren || $straatnaam_huisnummer || $plaatsnaam || $locatie_postcode ) {
		// TODO: schema markup voor locatie
		$return .= '<h2>' . _x( 'Adres', 'Single initiatief', 'initiatieven-kaart' ) . '</h2>';
		$adres  = '';
		if ( $straatnaam_huisnummer ) {
			$adres .= wp_strip_all_tags( $straatnaam_huisnummer ) . '<br>';
		}
		if ( $locatie_postcode ) {
			$adres .= ( ( $adres ) ? wp_strip_all_tags( $locatie_postcode ) . '<br>' : wp_strip_all_tags( $locatie_postcode ) . ', ');
		}
		if ( $plaatsnaam ) {
			$adres .= ( ( $adres ) ? wp_strip_all_tags( $plaatsnaam ) . '<br>' : wp_strip_all_tags( $plaatsnaam ) );
		}
		if ( $jaren ) {
			$adres .= ( ( $adres ) ? join( ", ", $jaren ) . '<br>' : join( ", ", $jaren ) );
		}
		$return .= '<p>' . $adres . '</p>';
	}

	if ( $doreturn ) {
		return $return;
	} else {
		echo $return;
	}
}

//========================================================================================================
