<?php
$admin = new Admin();

class Admin {

	function __construct() {
		add_action( 'wp_dashboard_setup', array( &$this, 'remove_dashboard_widgets' ), 15 );
		add_action( 'init', array( &$this, 'acf_add_options_page' ), 1 );
		add_action( 'init', array( &$this, 'setup_acf' ), 100);
	}

	function remove_dashboard_widgets() {
		global $wp_meta_boxes;
		unset($wp_meta_boxes['dashboard']);
	}

	function acf_add_options_page(){
		if( function_exists('acf_add_options_page') ) {
			acf_add_options_page(array(
				'page_title' => 'Options',
				'menu_title' => 'Options',
				'menu_slug' => 'options',
				'capability' => 'edit_posts',
				'redirect' => false,
			));
		}
	}

	function setup_acf(){
		if(function_exists('register_field_group')) {

			global $acf;
			if(floatval($acf->settings['version']) > 5) {
				$options_page = array(
					array(
						array(
							'param' => 'options_page',
							'operator' => '==',
							'value' => 'options',
						),
					),
				);
			} else {
				$options_page = array(
					array(
						array(
							'param' => 'options_page',
							'operator' => '==',
							'value' => 'acf-options',
							'order_no' => 0,
							'group_no' => 0,
						),
					),
				);
			}

			register_field_group(array(
				'id' => 'acf_contact-information',
				'title' => 'Contact Information',
				'fields' => array(
					array(
						'key' => 'field_company_name',
						'label' => 'Company Name',
						'name' => 'company_name',
						'type' => 'text',
						'formatting' => 'html',
					),
					array(
						'key' => 'field_address',
						'label' => 'Address',
						'name' => 'address',
						'type' => 'textarea',
						'formatting' => 'br',
					),
					array(
						'key' => 'field_toll_free',
						'label' => 'Toll Free',
						'name' => 'toll_free',
						'type' => 'text',
						'formatting' => 'html',
					),
					array(
						'key' => 'field_phone',
						'label' => 'Phone',
						'name' => 'phone',
						'type' => 'text',
						'formatting' => 'html',
					),
					array(
						'key' => 'field_email',
						'label' => 'Email',
						'name' => 'email',
						'type' => 'text',
						'formatting' => 'html',
					),
					array(
						'key' => 'field_fax',
						'label' => 'Fax',
						'name' => 'fax',
						'type' => 'text',
						'formatting' => 'html',
					),
					array(
						'key' => 'field_analytics',
						'label' => 'Analytics',
						'name' => 'analytics',
						'type' => 'textarea',
						'formatting' => 'html',
					),
					array(
						'key' => 'field_ga_remarketing',
						'label' => 'Remarketing',
						'name' => 'ga_remarketing',
						'type' => 'textarea',
						'formatting' => 'html',
					),
				),
				'location' => $options_page,
				'options' => array(
					'position' => 'normal',
					'layout' => 'default',
					'hide_on_screen' => array(),
				),
				'menu_order' => 0,
			));

			register_field_group(array(
				'id' => 'acf_site-options',
				'title' => 'Site Options',
				'fields' => array(
					array(
						'key' => 'field_default_image',
						'label' => 'Default Image',
						'name' => 'default_image',
						'type' => 'image',
						'save_format' => 'id',
						'preview_size' => 'thumbnail',
						'library' => 'all',
					),
				),
				'location' => $options_page,
				'options' => array(
					'position' => 'normal',
					'layout' => 'default',
					'hide_on_screen' => array(),
				),
				'menu_order' => 0,
			));
		}
	}
}