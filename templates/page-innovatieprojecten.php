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
	if ( is_post_type_archive( CPT_PROJECT ) || is_page() ) {
		// check is nodig om te voorkomen dat in zoekresultaten rare dingen gebeuren

		remove_action( 'genesis_loop', 'genesis_do_loop' );

		// lijstje met initiatieven toevoegen
		add_action( 'genesis_loop', 'led_innovatieprojecten_page_list', 8 );
		// lijstjes toevoegen met de diverse custom taxonomieen
		add_action( 'genesis_loop', 'led_initiatieven_taxonomy_list' );
	}


	// make it so
	genesis();

} else {

	// geen Genesis, maar wel dezelfde content, ongeveer, soort van
	global $post;

	get_header(); ?>

	<div id="primary" class="content-area">
		<div id="content" class="clearfix">

			<?php echo led_initiatieven_archive_title() ?>
			<?php echo led_innovatieprojecten_page_list() ?>
			<?php echo led_initiatieven_taxonomy_list() ?>

		</div><!-- #content -->
	</div><!-- #primary -->

	<?php

	get_sidebar();

	get_footer();


}

//========================================================================================================

function led_innovatieprojecten_page_list( $doreturn = false ) {

	global $post;

	$argscount = array(
		'post_type'      => CPT_PROJECT,
		'post_status'    => 'publish',
		'orderby'        => 'title',
		'order'          => 'ASC',
		'posts_per_page' => - 1,
	);

	// Assign predefined $args to your query
	$contentblockpostscount = new WP_query();
	$contentblockpostscount->query( $argscount );

	$return = '';

	if ( $contentblockpostscount->have_posts() ) {

		$initiatieficons = led_get_initiatieficons();
		$return .= led_initiatieven_list_before( true );

		$return .= '<ul id="map-items">';

		while ( $contentblockpostscount->have_posts() ) : $contentblockpostscount->the_post();

			$return .= led_get_list_item_archive( $post, $initiatieficons, CT_PROJECTORGANISATIE );

		endwhile;

		$return .= '</ul>';

	} else {
		$return .= 'geen initiatieven';
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


