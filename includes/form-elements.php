<?php

/*
 * Add MAILPOET form elementrs in the form elements select box
 */
function buddyforms_mailpoet_elements_to_select( $elements_select_options ) {
	global $post;

	if ( $post->post_type != 'buddyforms' ) {
		return;
	}
	$elements_select_options['mailpoet']['label']              = 'MailPoet';
	$elements_select_options['mailpoet']['class']              = 'bf_show_if_f_type_post';
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


			if ( class_exists( \MailPoet\API\API::class ) ) {
				$mailpoet_api = \MailPoet\API\API::MP( 'v1' );
			}

//			echo '<pre>';
//			print_r($mailpoet_api->getLists());
//			echo '</pre>';

			$lists          = $mailpoet_api->getLists();
			$mailpost_lists = array();
			foreach ( $lists as $key => $list ) {
				$mailpost_lists[ $list['id'] ] = $list['name'];
			}

//			echo '<pre>';
//			print_r($mailpost_lists);
//			echo '</pre>';


			$mailpost_list = 'false';
			if ( isset( $buddyforms[ $form_slug ]['form_fields'][ $field_id ]['mailpost_lists'] ) ) {
				$mailpost_list = $buddyforms[ $form_slug ]['form_fields'][ $field_id ]['mailpost_lists'];
			}

			$form_fields['general']['mailpost_lists'] = new Element_Checkbox( 'Select the lists available in the frontend', "buddyforms_options[form_fields][" . $field_id . "][mailpost_lists]", $mailpost_lists, array(
				'value'         => $mailpost_list,
				'class'         => 'bf_pods_field_group_select',
				'data-field_id' => $field_id
			) );

			$multiple                           = isset( $buddyforms[ $form_slug ]['form_fields'][ $field_id ]['multiple'] ) ? $buddyforms[ $form_slug ]['form_fields'][ $field_id ]['multiple'] : 'false';
			$form_fields['general']['multiple'] = new Element_Checkbox( '<b>' . __( 'Multiple Selection', 'buddyforms' ) . '</b>', "buddyforms_options[form_fields][" . $field_id . "][multiple]", array( 'multiple' => '<b>' . __( 'Multiple', 'buddyforms' ) . '</b>' ), array(
				'value' => $multiple,
				'class' => ''
			) );


			$multiple                           = isset( $buddyforms[ $form_slug ]['form_fields'][ $field_id ]['checkbox'] ) ? $buddyforms[ $form_slug ]['form_fields'][ $field_id ]['checkbox'] : 'false';
			$form_fields['general']['checkbox'] = new Element_Checkbox( '<b>' . __( 'Use Checkbox instead of Select', 'buddyforms' ) . '</b>', "buddyforms_options[form_fields][" . $field_id . "][checkbox]", array( 'checkbox' => '<b>' . __( 'Checkboxes', 'buddyforms' ) . '</b>' ), array(
				'value' => $multiple,
				'class' => '',
				'shortDesc' => 'will become a radio button if multiple selections is deactivated.'
			) );


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

			$element_attr = isset( $customfield['required'] ) ? array(
				'required'  => true,
				'value'     => $customfield_val,
				'class'     => 'settings-input bf-select2',
				'shortDesc' => $customfield['description']
			) : array(
				'value'     => $customfield['description'],
				'class'     => 'settings-input bf-select2',
				'shortDesc' => $customfield['description']
			);


			if ( class_exists( \MailPoet\API\API::class ) ) {
				$mailpoet_api = \MailPoet\API\API::MP( 'v1' );
			}

			$original_lists          = $mailpoet_api->getLists();
			$mailpost_lists = array();
			foreach ( $original_lists as $key => $list ) {
				$mailpost_lists[ $list['id'] ] = $list['name'];
			}


			foreach ( $customfield['mailpost_lists'] as $key => $list_id ) {

				if(isset($mailpost_lists[$list_id])){
					$form_element_options[ $list_id ] = $mailpost_lists[$list_id];
				}

			}


			ob_start();
//			echo '<pre>';
//			print_r($customfield_val);
//			echo '</pre>';

//			echo '<pre>';
//			print_r($mailpost_lists);
//			echo '</pre>';
			$tmp = ob_get_clean();


//			$form->addElement( new Element_HTML( $tmp) );




			$element = new Element_Select( $customfield['name'], $customfield['slug'], $form_element_options, $element_attr );

			if ( isset( $customfield['multiple'] ) ) {
				$element->setAttribute( 'multiple', 'multiple' );
			}

			$form->addElement( $element );

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

