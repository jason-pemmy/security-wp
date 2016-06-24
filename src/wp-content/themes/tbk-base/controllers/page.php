<?php

class TBK_Page extends Base_Factory {

	public function __construct() {
		add_action( 'init', array( &$this, 'setup_acf' ), 100 );
		add_filter( 'hero_banner', array( &$this, 'hero_banner' ), 20, 1 );
	}

	public static function setup_acf() {
		if ( function_exists( 'acf_add_local_field_group' ) ) {

			acf_add_local_field_group( array(
				'key' => 'group_56bcbf0f33037',
				'title' => 'Dropdown Menu Data',
				'fields' => array(
					array(
						'key' => 'field_56bcbf1a4042f',
						'label' => 'Dropdown Menu Image',
						'name' => 'dropdown_menu_image',
						'type' => 'image',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '50',
							'class' => '',
							'id' => '',
						),
						'return_format' => 'id',
						'preview_size' => 'dropdown-menu-thumb',
						'library' => 'all',
						'min_width' => '',
						'min_height' => '',
						'min_size' => '',
						'max_width' => '',
						'max_height' => '',
						'max_size' => '',
						'mime_types' => '',
					),
					array(
						'key' => 'field_dropdown_menu_icon',
						'label' => 'Dropdown Menu Icon',
						'name' => 'dropdown_menu_icon',
						'type' => 'text',
						'formatting' => 'html',
						'wrapper' => array(
							'width' => '50',
							'class' => '',
							'id' => '',
						),
					),
					array(
						'key' => 'field_56bcbf3240430',
						'label' => 'Dropdown Menu Description',
						'name' => 'dropdown_menu_description',
						'type' => 'text',
						'instructions' => 'The one liner that displays below the link to the page.',
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
				'location' => array(
					array(
						array(
							'param' => 'post_type',
							'operator' => '==',
							'value' => 'page',
						),
					),
					array(
						array(
							'param' => 'post_type',
							'operator' => '==',
							'value' => 'practice-area',
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

		/*acf_add_local_field_group( array(
			'key' => 'group_page_details',
			'title' => 'Page Details',
			'fields' => array(
				array(
					'key' => 'field_hide_hero_banner',
					'label' => 'Hide Hero Banner',
					'name' => 'hide_hero_banner',
					'type' => 'true_false',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'message' => '',
					'default_value' => 0,
				),
				array(
					'key' => 'field_hero_banner_text',
					'label' => 'Hero Banner Text',
					'name' => 'hero_banner_text',
					'type' => 'text',
					'instructions' => 'Add text to the hero banner on the page. Hero banner must not be hidden (above).',
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
			'location' => array(
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'page',
					),
				),
			),
			'menu_order' => 0,
			'position' => 'side',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
			'active' => 1,
			'description' => '',
		) );*/
	}

	function hero_banner( $atts ) {
		if ( is_page() ) {
			$title_override = get_field('hero_banner_text');

			if ( ! empty( $title_override ) ) {
				$atts['title'] = '<h1 class="hero-banner-heading">' . $title_override . '</h1>';
			}
		}

		return $atts;
	}
}

TBK_Page::instantiate();
