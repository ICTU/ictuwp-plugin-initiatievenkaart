<?php

if ( function_exists( 'genesis' ) ) {

	// Genesis wordt gebruikt als framework
	// dit geldt voor o.m. het theme voor Digitale Overheid

	// in de breadcrumb zetten we de link naar de algemene kaart
	add_filter( 'genesis_single_crumb', 'projecten_initiatieven_filter_breadcrumb', 10, 2 );
	add_filter( 'genesis_page_crumb', 'projecten_initiatieven_filter_breadcrumb', 10, 2 );
	add_filter( 'genesis_archive_crumb', 'projecten_initiatieven_filter_breadcrumb', 10, 2 );

	// titel toevoegen
	add_action( 'genesis_before_loop', 'led_initiatieven_archive_title', 8 );

	/** standard loop vervangen door custom loop */
	remove_action( 'genesis_loop', 'genesis_do_loop' );
	add_action( 'genesis_loop', 'led_initiatieven_archive_list' );

	// lijstjes toevoegen met de diverse custom taxonomieen
	add_action( 'genesis_loop', 'led_initiatieven_taxonomy_list' );

	// make it so
	genesis();

} else {

	// geen Genesis, maar wel dezelfde content, ongeveer, soort van
	global $post;

	get_header(); ?>

	<div id="primary" class="content-area">
		<div id="content" class="clearfix">

			<?php echo led_initiatieven_archive_title() ?>
			<?php echo led_initiatieven_archive_list() ?>
			<?php echo led_initiatieven_taxonomy_list() ?>

		</div><!-- #content -->
	</div><!-- #primary -->

	<?php

	get_sidebar();

	get_footer();


}

//========================================================================================================

function led_initiatieven_archive_list( $doreturn = false ) {

	global $post;

	$return = '';

	if ( have_posts() ) {

		if ( is_post_type_archive( CPT_INITIATIEF ) || is_post_type_archive( CPT_PROJECT ) ) {
			$return .= led_initiatieven_list_before( true );
		}
		$initiatieficons = led_get_initiatieficons();
		$return          .= '<ul id="map-items">';

		while ( have_posts() ) : the_post();

			$return .= led_get_list_item_archive( $post, $initiatieficons, CT_PROJECTORGANISATIE );

		endwhile;

		$return .= '</ul>';

	}

	$return .= led_initiatieven_list_after( true );

	if ( $doreturn ) {
		return $return;
	} else {
		echo $return;
	}
}

//========================================================================================================
