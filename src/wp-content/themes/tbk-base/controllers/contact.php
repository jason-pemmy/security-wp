<?php

/**
 * Class TBK_Contact
 * Meant to handle contact/Gravity Forms
 *
 * $form_ids = TBK_Contacts::get_gform_ids();
 */
class TBK_Contact extends Base_Factory {

	function __construct() {
		add_action( 'init', array( &$this, 'acf_add_options_page' ), 1 );
		add_action( 'init', array( &$this, 'setup_acf' ), 120 );

		add_filter( 'gform_submit_button', array( &$this, 'form_submit_button' ), 10, 2 );
		add_filter( 'gform_ajax_spinner_url', array( &$this, 'custom_gform_spinner' ), 10 );
		add_filter( 'gform_confirmation_anchor', create_function( '', 'return true;' ) );
		add_filter( 'gform_tabindex', array( &$this, 'gform_tabindexer' ), 10, 2 );
	}

	function acf_add_options_page() {
		if ( function_exists( 'acf_add_options_page' ) ) {
			acf_add_options_page( array(
				'page_title' => 'Email Contact Settings',
				'menu_title' => 'Contact Settings',
				'menu_slug' => 'contact-settings',
				'capability' => 'edit_posts',
				'redirect' => false,
			) );
		}
	}

	function setup_acf() {
		if ( function_exists( 'acf_add_local_field_group' ) ) {

			acf_add_local_field_group( array(
				'key' => 'group_contact_form_settings',
				'title' => 'Form Settings',
				'fields' => array(
					array(
						'key' => 'field_contact_gravity_forms',
						'label' => 'Gravity Forms',
						'name' => 'contact_gravity_forms',
						'type' => 'repeater',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'collapsed' => '',
						'min' => 1,
						'max' => 1,
						'layout' => 'block',
						'button_label' => 'Add Row',
						'sub_fields' => array(
							array(
								'key' => 'field_contact_form_id',
								'label' => 'Contact Form ID',
								'name' => 'contact_form_id',
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
								'readonly' => 0,
								'disabled' => 0,
							),
						),
					),
				),
				'location' => array(
					array(
						array(
							'param' => 'options_page',
							'operator' => '==',
							'value' => 'contact-settings',
						),
					),
				),
				'menu_order' => 0,
				'position' => 'normal',
				'style' => 'default',
				'label_placement' => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen' => '',
				'active' => 1,
				'description' => '',
			) );
		}

	}

	function form_submit_button( $button, $form ) {
		//get the value
		preg_match_all( '/value=\'([A-Za-z0-9-\s\!]+)\'/', $button, $matches );

		return '<button class="button" id="gform_submit_button_' . $form['id'] . '"><span>' . ( isset( $matches[1][0] ) ? $matches[1][0] : 'Submit' ) . '</span><i class="fa fa-angle-right"></i></button>';
	}

	function custom_gform_spinner( $src ) {
		return get_stylesheet_directory_uri() . '/images/loader.gif';
	}


	function gform_tabindexer( $tab_index, $form = false ) {
		$starting_index = 1000 * $form['id'];
		if ( ! empty( $form ) ) {
			add_filter( 'gform_tabindex_' . $form['id'], array( &$this, 'gform_tabindexer' ) );
		}

		return $starting_index;
	}

	/**
	 * @param array $field
	 * @param array $form
	 */
	public static function find_field_id( $search = array(), $form = array() ) {
		$type = key( $search );
		$name = $search[ key( $search ) ];
		foreach ( $form['fields'] as $field ) {
			if ( $name == $field[ $type ] ) {
				return $field['id'];
			}
		}

		return false;
	}
}

TBK_Contact::instantiate();
