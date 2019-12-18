<?php

/*
 * Add MailPoet form elementrs in the form elements select box
 */
add_filter( 'buddyforms_add_form_element_select_option', 'buddyforms_mailpoet_elements_to_select', 1, 2 );
function buddyforms_mailpoet_elements_to_select( $elements_select_options ) {
	global $post;

	if ( $post->post_type != 'buddyforms' ) {
		return;
	}
	$elements_select_options['mailpoet']['label']              = 'MailPoet';
	$elements_select_options['mailpoet']['class']              = 'bf_show_if_f_type_all';
	$elements_select_options['mailpoet']['fields']['mailpoet'] = array(
		'label' => __( 'Newsletter Field', 'buddyforms' ),
	);

	return $elements_select_options;
}


/*
 * Create the new MailPoet Form Builder Form Elements
 *
 */
add_filter( 'buddyforms_form_element_add_field', 'buddyforms_mailpoet_form_builder_form_elements', 1, 5 );
function buddyforms_mailpoet_form_builder_form_elements( $form_fields, $form_slug, $field_type, $field_id ) {
	global $field_position, $buddyforms;


	switch ( $field_type ) {

		case 'mailpoet':

			if ( class_exists( \MailPoet\API\API::class ) ) {
				$mailpoet_api = \MailPoet\API\API::MP( 'v1' );
			}

			// Get all mailpoet lists
			$lists          = $mailpoet_api->getLists();
			$mailpost_lists = array();
			foreach ( $lists as $key => $list ) {
				$mailpost_lists[ $list['id'] ] = $list['name'];
			}

			$mailpost_list = 'false';
			if ( isset( $buddyforms[ $form_slug ]['form_fields'][ $field_id ]['mailpost_lists'] ) ) {
				$mailpost_list = $buddyforms[ $form_slug ]['form_fields'][ $field_id ]['mailpost_lists'];
			}
			$form_fields['general']['mailpost_lists'] = new Element_Checkbox( 'Select the lists available in the frontend', "buddyforms_options[form_fields][" . $field_id . "][mailpost_lists]", $mailpost_lists, array(
				'value' => $mailpost_list,
				'class' => '',
			) );

			$multiple                        = isset( $buddyforms[ $form_slug ]['form_fields'][ $field_id ]['multiple'] ) ? $buddyforms[ $form_slug ]['form_fields'][ $field_id ]['multiple'] : 'false';
			$form_fields['general']['multi'] = new Element_Checkbox( __( 'Multiple Selection', 'buddyforms' ), "buddyforms_options[form_fields][" . $field_id . "][multiple]", array( 'multiple' => __( 'Multiple', 'buddyforms' ) ), array(
				'value' => $multiple,
				'class' => ''
			) );

			$multiple                           = isset( $buddyforms[ $form_slug ]['form_fields'][ $field_id ]['checkbox'] ) ? $buddyforms[ $form_slug ]['form_fields'][ $field_id ]['checkbox'] : 'false';
			$form_fields['general']['checkbox'] = new Element_Checkbox( __( 'Use Checkbox instead of Select', 'buddyforms' ), "buddyforms_options[form_fields][" . $field_id . "][checkbox]", array( 'checkbox' => __( 'Checkboxes', 'buddyforms' ) ), array(
				'value'     => $multiple,
				'class'     => '',
				'shortDesc' => 'Will become a radio buttons if multiple selections is deactivated.'
			) );


			$hidden = isset( $customfield['hidden_field'] ) ? $customfield['hidden_field'] : false;

			$form_fields['general']['hidden_field'] = new Element_Checkbox( __( 'Hidden?', 'buddyforms' ), "buddyforms_options[form_fields][" . $field_id . "][hidden_field]", array( 'hidden_field' => '<b>' . __( 'Make this field Hidden', 'buddyforms' ) . '</b>' ), array(
				'value'     => $hidden,
				'shortDesc' => 'If this is a hidden field the list selection will be used to automatically assign the user to the lists  '
			) );


			break;
	}

	return $form_fields;
}

/*
 * Display the new MailPoet Fields in the frontend form
 *
 */
add_filter( 'buddyforms_create_edit_form_display_element', 'buddyforms_mailpoet_frontend_form_elements', 1, 2 );
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


			if ( ! isset( $customfield['mailpost_lists'] ) ) {
				return $form;
			}

			if ( class_exists( \MailPoet\API\API::class ) ) {
				$mailpoet_api = \MailPoet\API\API::MP( 'v1' );
			}

			// Get the lists
			$original_lists = $mailpoet_api->getLists();
			$mailpost_lists = array();

			// Loop the lists
			foreach ( $original_lists as $key => $list ) {
				$mailpost_lists[ $list['id'] ] = $list['name'];
			}


			// Loop all lists selected in the form element options
			foreach ( $customfield['mailpost_lists'] as $key => $list_id ) {
				if ( isset( $mailpost_lists[ $list_id ] ) ) {
					$form_element_options[ $list_id ] = $mailpost_lists[ $list_id ];
				}
			}

			// Get the current user
			$current_user = wp_get_current_user();

			// Get the logged in user subscription
			$mailpoet_subscriber = $mailpoet_api->getSubscriber( $current_user->user_email );
			$subscriptions       = $mailpoet_subscriber['subscriptions'];
			$user_subscriptions  = array();
			foreach ( $subscriptions as $key => $subscription ) {
				if ( $subscription['status'] != 'unsubscribed' && isset( $form_element_options[ $subscription['segment_id'] ] ) ) {
					$user_subscriptions[] = $subscription['segment_id'];
				}
			}

//			ob_start();
//			echo '<pre>';
//			print_r( $subscriptions );
//			echo '</pre>';
//			echo '<pre>';
//			print_r( $user_subscriptions );
//			echo '</pre>';
//			$tmp = ob_get_clean();
//			$form->addElement( new Element_HTML( $tmp ) );


			// Create the form elements attribute array
			$element_attr = isset( $customfield['required'] ) ? array(
				'required'  => true,
				'value'     => $user_subscriptions,
				'shortDesc' => $customfield['description']
			) : array(
				'value'     => $user_subscriptions,
				'shortDesc' => $customfield['description']
			);

			if ( isset( $customfield['checkbox'] ) ) {
				if ( isset( $customfield['multiple'] ) ) {
					$element = new Element_Checkbox( $customfield['name'], $customfield['slug'], $form_element_options, $element_attr );
				} else {
					$element_attr['value'] = $user_subscriptions[0];
					$element               = new Element_Radio( $customfield['name'], $customfield['slug'], $form_element_options, $element_attr );
				}
			} else {
				$element_attr['class'] = 'settings-input bf-select2';
				$element               = new Element_Select( $customfield['name'], $customfield['slug'], $form_element_options, $element_attr );
				BuddyFormsAssets::load_select2_assets();
			}

			if ( isset( $customfield['multiple'] ) ) {
				$element->setAttribute( 'multiple', 'multiple' );
			}

			$form->addElement( $element );

			break;
	}

	return $form;
}


/*
 * Save MailPoet Fields
 *
 */
add_action( 'buddyforms_update_post_meta', 'buddyforms_mailpoet_update_post_meta', 10, 2 );
function buddyforms_mailpoet_update_post_meta( $customfield, $post_id ) {
	if ( $customfield['type'] == 'mailpoet' ) {

		if ( class_exists( \MailPoet\API\API::class ) ) {
			$mailpoet_api = \MailPoet\API\API::MP( 'v1' );
		}

		// Get the current user
		$current_user = wp_get_current_user();

		// Get the logged in user subscription
		$mailpoet_subscriber = $mailpoet_api->getSubscriber( $current_user->user_email );

		// Get the lists
		$lists = $mailpoet_api->getLists();

		// Loop the lists
		foreach ( $lists as $key => $list ) {
			$mailpoet_api->unsubscribeFromList( $mailpoet_subscriber['id'], $list['id'] );
		}

		if ( ! isset( $_POST[ $customfield['slug'] ] ) ) {
			return;
		}
		if ( isset( $customfield['multiple'] ) ) {
			$mailpoet_api->subscribeToLists( $mailpoet_subscriber['id'], $_POST[ $customfield['slug'] ] );
		} else {
			$mailpoet_api->subscribeToList( $mailpoet_subscriber['id'], $_POST[ $customfield['slug'] ] );
		}

	}

}


/*
 * Sync MailPoet Fields
 *
 */
add_filter( 'buddyforms_formbuilder_fields_options', 'buddyforms_mailpoet_formbuilder_fields_options', 10, 4 );
function buddyforms_mailpoet_formbuilder_fields_options( $form_fields, $field_type, $field_id, $form_slug = '' ) {
	global $buddyforms;

	if ( $field_type == 'mailpoet' ) {
		return $form_fields;
	}

	if ( class_exists( \MailPoet\API\API::class ) ) {
		$mailpoet_api = \MailPoet\API\API::MP( 'v1' );
	}
	$subscriberfields = $mailpoet_api->getSubscriberFields();

//	ob_start();
//	echo '<pre>';
//	print_r( $field_type );
//	echo '</pre>';
//	$tmp                                              = ob_get_clean();
//	$form_fields['MailPoet']['mapped_mailpoet_dump'] = new Element_HTML( $tmp );

	$mailpoet_form_fields = array();
	foreach ( $subscriberfields as $key => $subscriberfield ) {
		$mailpoet_form_fields[ $subscriberfield['id'] ] = $subscriberfield['name'];
	}

	$mapped_mailpoet_field = isset( $buddyforms[ $form_slug ]['form_fields'][ $field_id ]['mapped_mailpoet_field'] ) ? $buddyforms[ $form_slug ]['form_fields'][ $field_id ]['mapped_mailpoet_field'] : '';

	$form_fields['MailPoet']['mapped_mailpoet_field'] = new Element_Select( '<b>' . __( 'Map with existing Mailpoet Field', 'buddyforms' ) . '</b>', "buddyforms_options[form_fields][" . $field_id . "][mapped_mailpoet_field]", $mailpoet_form_fields, array(
		'value'    => $mapped_mailpoet_field,
		'class'    => 'bf_tax_select',
		'field_id' => $field_id,
		'id'       => 'buddyforms_mailpoet_' . $field_id,
	) );

	return $form_fields;
}

add_action( 'buddyforms_process_submission_end', 'buddyforms_mailpoet_process_submission_end', 10, 1 );
function buddyforms_mailpoet_process_submission_end( $args ) {
	global $buddyforms;

	extract( $args );

	if ( ! isset( $post_id ) ) {
		return;
	}

	if ( isset( $buddyforms[ $form_slug ] ) ) {
		if ( isset( $buddyforms[ $form_slug ]['form_fields'] ) ) {

			foreach ( $buddyforms[ $form_slug ]['form_fields'] as $field_key => $field ) {

				if ( isset( $field['mapped_mailpoet_field'] ) && $field['mapped_mailpoet_field'] != 'none' ) {

					// Sanitise and Update SubscriberField s$_POST[ $field['slug'] ];

				}

			}
		}
	}

}