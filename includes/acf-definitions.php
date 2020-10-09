<?php

//========================================================================================================

// hard-coded field definition for intitiateven
// please use the 'ACF OpenStreetMap Field' plugin to add
// coordinates to an initiatief

if( function_exists('acf_add_local_field_group') ):

	acf_add_local_field_group(array(
		'key' => 'group_5f801513dfa08',
		'title' => 'Initiatief-type',
		'fields' => array(
			array(
				'key' => 'field_5f801544f2817',
				'label' => 'Welk icoontje hoort hierbij?',
				'name' => 'initiatief_type_icon',
				'type' => 'radio',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'choices' => array(
					'community' => 'Community',
					'lab' => 'Lab',
					'onbekend' => 'Onbekend',
					'portaal' => 'Portaal',
					'strategie' => 'Strategie',
					'visualisatie' => 'Visualisatie',
				),
				'allow_null' => 0,
				'other_choice' => 0,
				'default_value' => 'onbekend',
				'layout' => 'vertical',
				'return_format' => 'value',
				'save_other_choice' => 0,
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'taxonomy',
					'operator' => '==',
					'value' => 'initiatieftype',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		'active' => true,
		'description' => '',
	));

	acf_add_local_field_group(array(
		'key' => 'group_5f589cb3955cc',
		'title' => 'Initiatief: locatie en adres',
		'fields' => array(
			array(
				'center_lat' => 52.2345283,
				'center_lng' => 5.526123,
				'zoom' => 7,
				'key' => 'field_5f58c08e264c0',
				'label' => 'Locatie op de kaart',
				'name' => 'openstreet_map',
				'type' => 'open_street_map',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'return_format' => 'raw',
				'layers' => array(
					0 => 'OpenStreetMap.Mapnik',
				),
				'allow_map_layers' => 1,
				'height' => 600,
				'max_markers' => '',
			),
			array(
				'key' => 'field_5f80206f9c2f5',
				'label' => 'Straatnaam + huisnummer',
				'name' => 'locatie_straatnaam_huisnummer',
				'type' => 'text',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'maxlength' => '',
			),
			array(
				'key' => 'field_5f8020c09c2f6',
				'label' => 'Postcode',
				'name' => 'locatie_postcode',
				'type' => 'text',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'maxlength' => '',
			),
			array(
				'key' => 'field_5f8020ca9c2f7',
				'label' => 'Plaatsnaam',
				'name' => 'locatie_plaatsnaam',
				'type' => 'taxonomy',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'taxonomy' => 'plaatsnaam',
				'field_type' => 'radio',
				'allow_null' => 0,
				'add_term' => 1,
				'save_terms' => 0,
				'load_terms' => 0,
				'return_format' => 'id',
				'multiple' => 0,
			),
			array(
				'key' => 'field_5f8021a69c2f8',
				'label' => 'Website',
				'name' => 'locatie_website',
				'type' => 'url',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'placeholder' => '',
			),
			array(
				'key' => 'field_5f8021bb9c2f9',
				'label' => 'Contactpersoon',
				'name' => 'locatie_contactpersoon',
				'type' => 'textarea',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'placeholder' => '',
				'maxlength' => '',
				'rows' => '',
				'new_lines' => '',
			),
			array(
				'key' => 'field_5f8021d09c2fa',
				'label' => 'Contactgegevens',
				'name' => 'locatie_contactgegevens',
				'type' => 'textarea',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'placeholder' => '',
				'maxlength' => '',
				'rows' => '',
				'new_lines' => '',
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'initiatief',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'acf_after_title',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => array(
			0 => 'categories',
		),
		'active' => true,
		'description' => '',
	));

endif;

//========================================================================================================

