<?php

class tbkcp_dealer_locator
{

	function __construct()
	{
		$this->slug = 'tbk-dealer-locator';
		$this->location = 'http://plugins.tbkcreative.com/extend/plugins/' . $this->slug . '/update';

		add_filter( 'tbkdl_contact_fields', array( &$this, 'tbkdl_contact_fields' ) );
		add_filter( 'tbkdl_meta_fields', array( &$this, 'tbkdl_meta_fields' ) );

		if ( is_blog_admin() and current_user_can( 'edit_posts' ) ) {
			add_action( 'wp_dashboard_setup', array( &$this, 'dashboard_widget' ) );
		}
	}

	function dashboard_widget()
	{
		wp_enqueue_style( 'tbkcp-toolbox-style' );
		wp_add_dashboard_widget( 'dealer_locator_home_location_widget', 'Home Location', array( &$this, 'form_options' ) );
	}

	function tbkdl_meta_fields( $meta )
	{
		// going to add social links to the Meta for Dealer Locations
		$meta[] = 'google-maps-url';
		$meta[] = 'hours-of-operation';
		return $meta;
	}

	function tbkdl_contact_fields( $contact )
	{
		// going to add social links to the Meta for Dealer Locations
		$contact[] = 'toll-free';
		$contact[] = 'twitter';
		$contact[] = 'facebook';
		$contact[] = 'linkedin';
		$contact[] = 'pinterest';
		$contact[] = 'google';
		$contact[] = 'email';
		return $contact;
	}

	function form_options()
	{
		$module = get_class( $this );
		if ( file_exists( TBKDL_PATH . '/lib/model/LocationModel.php' ) ) {
			require_once(TBKDL_PATH . '/lib/model/LocationModel.php');
			$model = new LocationModel();
			$_locations = $model->getAll();
			$settings = tbk_toolbox::get_settings( $module ); // will save settings as well.

			$locations = array(
				'' => '-- Choose a Location --'
			);
			foreach ( $_locations as $location ) {
				$locations[$location->id] = $location->title;
			}

			$options = array(
				'home_location' => array(
					'type' => 'select',
					'key' => 'index',
					'options' => $locations,
					'label' => 'Home Location',
					'description' => 'If you wish, you may choose one of the Dealer Locations as the Home Location'
				),
			);

			tbk_toolbox::options_form( $module, $options );
		}
	}

	function get_home_location()
	{
		$home_location = tbk_toolbox::get_settings( 'tbkcp_dealer_locator', 'home_location' );
		$model = new LocationModel();
		if ( !($home_location and $home = $model->getOne( $home_location )) ) {
			return false;
		}
		return $home;
	}

}

