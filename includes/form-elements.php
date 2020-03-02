<?php

use MailPoet\Models\Subscriber;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add MailPoet form elements in the form elements select box
 *
 * @param $elements_select_options
 *
 * @return mixed
 */
function buddyforms_mailpoet_elements_to_select( $elements_select_options ) {
	global $post;

	if ( $post->post_type != 'buddyforms' ) {
		return $elements_select_options;
	}
	$elements_select_options['mailpoet']['label']              = 'MailPoet';
	$elements_select_options['mailpoet']['class']              = 'bf_show_if_f_type_all';
	$elements_select_options['mailpoet']['fields']['mailpoet'] = array(
		'label' => __( 'MailPoet Field', 'bf-mailpoet' ),
	);

	return $elements_select_options;
}

add_filter( 'buddyforms_add_form_element_select_option', 'buddyforms_mailpoet_elements_to_select', 1, 2 );


/**
 * Create the new MailPoet Form Builder Form Elements
 *
 * @param $form_fields
 * @param $form_slug
 * @param $field_type
 * @param $field_id
 * @param $customfield
 *
 * @return mixed
 * @throws Exception
 */
function buddyforms_mailpoet_form_builder_form_elements( $form_fields, $form_slug, $field_type, $field_id, $customfield ) {
	global $buddyforms;


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

			$name                           = isset( $customfield['name'] ) ? stripcslashes( $customfield['name'] ) : __( 'MailPoet', 'bf-mailpoet' );
			$form_fields['general']['name'] = new Element_Textbox( '<b>' . __( 'Label', 'bf-mailpoet' ) . '</b>', "buddyforms_options[form_fields][" . $field_id . "][name]", array(
				'data'     => $field_id,
				'class'    => "use_as_slug",
				'value'    => $name,
				'required' => 1
			) );

			$mailpost_list = 'false';
			if ( isset( $buddyforms[ $form_slug ]['form_fields'][ $field_id ]['mailpost_lists'] ) ) {
				$mailpost_list = $buddyforms[ $form_slug ]['form_fields'][ $field_id ]['mailpost_lists'];
			}
			$form_fields['general']['mailpost_lists'] = new Element_Checkbox( 'Select the lists available in the frontend', "buddyforms_options[form_fields][" . $field_id . "][mailpost_lists]", $mailpost_lists, array(
				'value' => $mailpost_list,
				'class' => '',
			) );

			$multiple                        = isset( $buddyforms[ $form_slug ]['form_fields'][ $field_id ]['multiple'] ) ? $buddyforms[ $form_slug ]['form_fields'][ $field_id ]['multiple'] : 'false';
			$form_fields['general']['multi'] = new Element_Checkbox( __( 'Multiple Selection', 'bf-mailpoet' ), "buddyforms_options[form_fields][" . $field_id . "][multiple]", array( 'multiple' => __( 'Multiple', 'bf-mailpoet' ) ), array(
				'value' => $multiple,
				'class' => ''
			) );

			$multiple                           = isset( $buddyforms[ $form_slug ]['form_fields'][ $field_id ]['checkbox'] ) ? $buddyforms[ $form_slug ]['form_fields'][ $field_id ]['checkbox'] : 'false';
			$form_fields['general']['checkbox'] = new Element_Checkbox( __( 'Use Checkbox instead of Select', 'bf-mailpoet' ), "buddyforms_options[form_fields][" . $field_id . "][checkbox]", array( 'checkbox' => __( 'Checkboxes', 'bf-mailpoet' ) ), array(
				'value'     => $multiple,
				'class'     => '',
				'shortDesc' => 'Will become a radio buttons if multiple selections is deactivated.'
			) );


			$hidden = isset( $customfield['hidden_field'] ) ? $customfield['hidden_field'] : false;

			$form_fields['general']['hidden_field'] = new Element_Checkbox( __( 'Hidden?', 'bf-mailpoet' ), "buddyforms_options[form_fields][" . $field_id . "][hidden_field]", array( 'hidden_field' => '<b>' . __( 'Make this field Hidden', 'bf-mailpoet' ) . '</b>' ), array(
				'value'     => $hidden,
				'shortDesc' => 'If this is a hidden field the list selection will be used to automatically assign the user to the lists  '
			) );


			break;
	}

	return $form_fields;
}

add_filter( 'buddyforms_form_element_add_field', 'buddyforms_mailpoet_form_builder_form_elements', 1, 5 );

/**
 * Display the new MailPoet Fields in the frontend form
 *
 * @param $form
 * @param $form_args
 *
 * @return mixed
 * @throws \MailPoet\API\MP\v1\APIException
 */
function buddyforms_mailpoet_frontend_form_elements( $form, $form_args ) {
	global $buddyforms;

	$form_slug   = '';
	$action      = 'new';
	$customfield = array();

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

			$user_subscriptions = array();

			//Process the frontend for not hidden field
			if ( empty( $customfield['hidden_field'] ) ) {
				// Get the current user data
				if ( is_user_logged_in() && $action !== 'new' ) {
					$current_user = wp_get_current_user();
					if ( ! empty( $current_user ) && ! is_wp_error( $current_user ) ) {
						// Get the logged in user subscription
						$mailpoet_subscriber = $mailpoet_api->getSubscriber( $current_user->user_email );
						$subscriptions       = $mailpoet_subscriber['subscriptions'];
						foreach ( $subscriptions as $key => $subscription ) {
							if ( $subscription['status'] != 'unsubscribed' && isset( $form_element_options[ intval( $subscription['segment_id'] ) ] ) ) {
								$user_subscriptions[] = $subscription['segment_id'];
							}
						}
					}
				}

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
						$element = new Element_Checkbox( $customfield['name'], $customfield['slug'], $form_element_options, $element_attr, $customfield );
					} else {
						$element_attr['value'] = isset( $user_subscriptions[0] ) ? $user_subscriptions[0] : '';
						$element               = new Element_Radio( $customfield['name'], $customfield['slug'], $form_element_options, $element_attr, $customfield );
					}
				} else {
					$element_attr['class'] = 'settings-input bf-select2';
					$element               = new Element_Select( $customfield['name'], $customfield['slug'], $form_element_options, $element_attr, $customfield );
					BuddyFormsAssets::load_select2_assets();
				}

				if ( isset( $customfield['multiple'] ) ) {
					$element->setAttribute( 'multiple', 'multiple' );
				}

				$form->addElement( $element );
			} else {
				//The hidden field is a mandatory groups from the field options
				$hidden_field = new Element_Hidden( $customfield['slug'], join( ',', array_keys( $form_element_options ) ) );
				$form->addElement( $hidden_field );
			}
			break;
	}

	return $form;
}

add_filter( 'buddyforms_create_edit_form_display_element', 'buddyforms_mailpoet_frontend_form_elements', 1, 2 );

/**
 * Save MailPoet Fields
 *
 * @param $customfield
 * @param $post_id
 *
 * @param $form_slug
 */
function buddyforms_mailpoet_update_post_meta( $customfield, $post_id, $form_slug ) {
	if ( $customfield['type'] == 'mailpoet' ) {
		try {
			if ( class_exists( \MailPoet\API\API::class ) ) {
				$mailpoet_api = \MailPoet\API\API::MP( 'v1' );
			}

			$mailpoet_subscriber_id = false;
			if ( is_user_logged_in() ) {
				// Get the current user
				$current_user = wp_get_current_user();

				// Get the logged in user subscription
				$mailpoet_subscriber    = $mailpoet_api->getSubscriber( $current_user->user_email );
				$mailpoet_subscriber_id = $mailpoet_subscriber['id'];

				$lists = $mailpoet_api->getLists();

				// Loop the lists
				foreach ( $lists as $key => $list ) {
					$mailpoet_api->unsubscribeFromList( $mailpoet_subscriber_id, $list['id'] );
				}
			}

			if ( empty( $_POST[ $customfield['slug'] ] ) ) {
				return;
			}

			if ( empty( $customfield['hidden_field'] ) ) {
				$mailpoet_list_ids = $_POST[ $customfield['slug'] ];
			} else {
				$mailpoet_list_ids = $customfield['mailpost_lists'];
			}

			if ( is_array( $mailpoet_list_ids ) ) {
				$mailpoet_list_ids = array_map( 'intval', $mailpoet_list_ids );
			} else {
				$mailpoet_list_ids = array( intval( $mailpoet_list_ids ) );
			}

			if ( empty( $customfield['hidden_field'] ) ) {
				if ( ! isset( $customfield['multiple'] ) && is_array( $mailpoet_list_ids ) && isset( $mailpoet_list_ids[0] ) ) {
					$mailpoet_list_ids = array( $mailpoet_list_ids[0] );
				}
			}

			//Check if exist an email field
			$field_email = buddyforms_get_form_field_by( $form_slug, 'user_email', 'type' );
			if ( empty( $field_email ) ) {
				$field_email = buddyforms_get_form_field_by( $form_slug, 'email', 'type' );
			}
			if ( empty( $field_email ) ) {
				throw new Exception( __( 'Email not detected, please check if exist a Form Element to collect the email', 'bf-mailpoet' ) );
			} else {
				//Iterate over the mapped field to sync with the new subscriber
				$subscriber_fields = array();
				//Add the email to the user
				if ( ! empty( $_POST[ $field_email['slug'] ] ) ) {
					$subscriber_email           = sanitize_email( $_POST[ $field_email['slug'] ] );
					$subscriber_fields['email'] = $subscriber_email;
				}
				//Add the user First Name
				$field_user_first = buddyforms_get_form_field_by( $form_slug, 'user_first', 'type' );
				if ( ! empty( $field_user_first ) && ! empty( $_POST[ $field_user_first['slug'] ] ) ) {
					$subscriber_user_first           = sanitize_text_field( $_POST[ $field_user_first['slug'] ] );
					$subscriber_fields['first_name'] = $subscriber_user_first;
				}
				//Add the user Last Name
				$field_user_last = buddyforms_get_form_field_by( $form_slug, 'user_last', 'type' );
				if ( ! empty( $field_user_last ) && ! empty( $_POST[ $field_user_last['slug'] ] ) ) {
					$subscriber_user_first          = sanitize_text_field( $_POST[ $field_user_last['slug'] ] );
					$subscriber_fields['last_name'] = $subscriber_user_first;
				}
//				The sync is disabled until feature update from MailPoet API https://github.com/mailpoet/mailpoet/issues/2627
//				$form_fields = buddyforms_get_form_fields( $form_slug );
//				if ( ! empty( $form_fields ) ) {
//					foreach ( $form_fields as $field ) {
//						$current_field_slug = $field['slug'];
//						if ( $current_field_slug === 'user_email' || $current_field_slug === 'email' ) {
//							continue;
//						}
//						if ( ! empty( $field['mapped_mailpoet_field'] ) && ! empty( $_POST[ $current_field_slug ] ) ) {
//							$subscriber_fields[ $field['mapped_mailpoet_field'] ] = sanitize_text_field( $_POST[ $current_field_slug ] );
//						}
//					}
//				}


				if ( empty( $mailpoet_subscriber_id ) ) {
					if ( ! empty( $subscriber_fields ) ) {
						$mailpoet_api->addSubscriber( $subscriber_fields, $mailpoet_list_ids );
					}
				} else {
					$mailpoet_api->subscribeToLists( $mailpoet_subscriber_id, $mailpoet_list_ids );
//					The sync is disable for feature update from MailPoet API https://github.com/mailpoet/mailpoet/issues/2627
//					// separate data into default and custom fields
//					list( $defaultFields, $customFields ) = Subscriber::extractCustomFieldsFromFromObject( $subscriber_fields );
//					if ( ! empty( $customFields ) ) {
//						$mailpoet_subscriber->saveCustomFields( $customFields );
//					}
				}
			}
		} catch ( Exception $e ) {
			BuddyFormsMailPoet::error_log( $e->getMessage() );
		}
	}

}

add_action( 'buddyforms_update_post_meta', 'buddyforms_mailpoet_update_post_meta', 10, 3 );

/**
 * Tab added to all field to sync MailPoet Fields
 *
 * @param $form_fields
 * @param $field_type
 * @param $field_id
 * @param string $form_slug
 *
 * @return mixed
 * @throws Exception
 */
function buddyforms_mailpoet_form_builder_fields_options( $form_fields, $field_type, $field_id, $form_slug = '' ) {
	global $buddyforms;

	if ( $field_type == 'mailpoet' ) {
		return $form_fields;
	}

	if ( class_exists( \MailPoet\API\API::class ) ) {
		$mailpoet_api = \MailPoet\API\API::MP( 'v1' );
	}
	$subscriber_fields = $mailpoet_api->getSubscriberFields();

	$mailpoet_form_fields = array( '' => __( 'None', 'bf-mailpoet' ) );
	foreach ( $subscriber_fields as $key => $subscriber_field ) {
		if ( $subscriber_field === 'email' ) {
			continue;
		}
		$mailpoet_form_fields[ $subscriber_field['id'] ] = $subscriber_field['name'];
	}

	$mapped_mailpoet_field = isset( $buddyforms[ $form_slug ]['form_fields'][ $field_id ]['mapped_mailpoet_field'] ) ? $buddyforms[ $form_slug ]['form_fields'][ $field_id ]['mapped_mailpoet_field'] : '';

	$form_fields['MailPoet']['mapped_mailpoet_field'] = new Element_Select( '<b>' . __( 'Map with existing Mailpoet Field', 'bf-mailpoet' ) . '</b>', "buddyforms_options[form_fields][" . $field_id . "][mapped_mailpoet_field]", $mailpoet_form_fields, array(
		'value'    => $mapped_mailpoet_field,
		'class'    => 'bf_tax_select',
		'field_id' => $field_id,
		'id'       => 'buddyforms_mailpoet_' . $field_id,
	) );

	return $form_fields;
}

//The sync is disabled until feature update from MailPoet API https://github.com/mailpoet/mailpoet/issues/2627
//add_filter( 'buddyforms_formbuilder_fields_options', 'buddyforms_mailpoet_form_builder_fields_options', 10, 4 );


/**
 * Process the form fields after the form was submitted
 *
 * @param $args
 */
function buddyforms_mailpoet_process_submission_end( $args ) {
	global $buddyforms;

	$form_slug = '';

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

add_action( 'buddyforms_process_submission_end', 'buddyforms_mailpoet_process_submission_end', 10, 1 );
