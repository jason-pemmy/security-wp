<?php

class TBK_Theme_Testing extends Base_Factory {

	function __construct() {
		if ( ! empty( $_REQUEST['action'] ) ) {
			add_action( 'wp_ajax_' . $_REQUEST['action'], array( &$this, 'ajax_call' ) );
			add_action( 'wp_ajax_nopriv_' . $_REQUEST['action'], array( &$this, 'ajax_call' ) );
		}
	}

	public static function ajax_call() {
		$class = get_class();
		if ( method_exists( $class, $_REQUEST['action'] ) ) {
			$class::$_REQUEST['action']();
			die();
		}
	}
}

TBK_Theme_Testing::instantiate();