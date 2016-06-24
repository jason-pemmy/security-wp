<?php

$frontend_tool = new Tbk_Frontend_Tools();
class Tbk_Frontend_Tools {

	function __construct() {
		add_action( 'tbk_add_to_top_of_body', array( &$this, 'frontend_tools' ), 100);
	}

	public function frontend_tools(){
		if( ! is_admin() && is_user_logged_in() && current_user_can('manage_options') ) {
			echo do_shortcode('[frontend-tool]');
		}
	}

}
