<?php

/*
 * Add MAILPOET form elementrs in the form elements select box
 */
function buddyforms_mailpoet_elements_to_select( $elements_select_options ) {
	global $post;

	if ( $post->post_type != 'buddyforms' ) {
		return;
	}
	$elements_select_options['mailpoet']['label']                = 'MailPoet';
	$elements_select_options['mailpoet']['class']                = 'bf_show_if_f_type_post';
	$elements_select_options['mailpoet']['fields']['mailpoet'] = array(
		'label' => __( 'Newsletter Field', 'buddyforms' ),
	);
	return $elements_select_options;
}

add_filter( 'buddyforms_add_form_element_select_option', 'buddyforms_mailpoet_elements_to_select', 1, 2 );


/*
 * Create the new MAILPOET Form Builder Form Elements
 *
 */
function buddyforms_mailpoet_form_builder_form_elements( $form_fields, $form_slug, $field_type, $field_id ) {
	global $field_position, $buddyforms;


	switch ( $field_type ) {
		case 'mailpoet':

			//unset( $form_fields );


//			$mailpoet_group = 'false';
//			if ( isset( $buddyforms[ $form_slug ]['form_fields'][ $field_id ]['mailpoet_field'] ) ) {
//				$mailpoet_group = $buddyforms[ $form_slug ]['form_fields'][ $field_id ]['mailpoet_field-field'];
//			}
//			$form_fields['general']['mailpoet_group'] = new Element_Select( '', "buddyforms_options[form_fields][" . $field_id . "][mailpoet_group]", $mailpoet_list, array( 'value' => $mailpoet_group ) );
//
//			$name = 'MailPoet-Group';
//			if ( $mailpoet_group != 'false' ) {
//				$name = ' MailPoet Group: ' . $mailpoet_group;
//			}
//			$form_fields['general']['name'] = new Element_Hidden( "buddyforms_options[form_fields][" . $field_id . "][name]", $name );
//
//			$form_fields['general']['slug']  = new Element_Hidden( "buddyforms_options[form_fields][" . $field_id . "][slug]", 'mailpoet-fields-group' );
//			$form_fields['general']['type']  = new Element_Hidden( "buddyforms_options[form_fields][" . $field_id . "][type]", $field_type );
//			$form_fields['general']['order'] = new Element_Hidden( "buddyforms_options[form_fields][" . $field_id . "][order]", $field_position, array( 'id' => 'buddyforms/' . $form_slug . '/form_fields/' . $field_id . '/order' ) );
			break;

	}

	return $form_fields;
}

add_filter( 'buddyforms_form_element_add_field', 'buddyforms_mailpoet_form_builder_form_elements', 1, 5 );

/*
 * Display the new MAILPOET Fields in the frontend form
 *
 */
function buddyforms_mailpoet_frontend_form_elements( $form, $form_args ) {
	global $buddyforms, $nonce;

	extract( $form_args );

	$post_type = $buddyforms[ $form_slug ]['post_type'];

	if ( ! $post_type ) {
		return $form;
	}

	if ( ! isset( $customfield['type'] ) ) {
		return $form;
	}

	switch ( $customfield['type'] ) {
		case 'mailpoet':

			$form->addElement( new Element_HTML( 'was auch immer' ) );
			break;
	}

	return $form;
}

add_filter( 'buddyforms_create_edit_form_display_element', 'buddyforms_mailpoet_frontend_form_elements', 1, 2 );

/*
 * Save MAILPOET Fields
 *
 */
function buddyforms_mailpoet_update_post_meta( $customfield, $post_id ) {
	if ( $customfield['type'] == 'mailpoet' ) {


	}
}

add_action( 'buddyforms_update_post_meta', 'buddyforms_mailpoet_update_post_meta', 10, 2 );

