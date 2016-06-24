<?php
/*
 * Plugin Name: tbk Testing Module
 * Version: 1.0.1
 * Description: This will provide us more indepth look at the various areas of our website
 * Author: tbk Creative
 * Author URI: http://www.tbkcreative.com/
 * Requires at least: 4.1
 * Tested up to: 4.1
 */

class TBK_Testing {

	static $instance = false;
	private function __construct() {
		//include all tests
		self::iterate_mvc(array(
			'library',
			'tests',
		));
	}

	public function instantiate() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}
		return null;
	}

	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public static function iterate_mvc( $dir = 'controllers', $ext = '.php' ) {
		if ( ! is_array( $dir ) ) {
			$dir = array( $dir );
		}

		foreach ( $dir as $d ) {
			$assets = glob( dirname(__FILE__) . '/' . $d . '/*' . $ext );
			foreach ( $assets as $asset ) {
				include($asset);
			}
		}
	}

	public static function maybe_allow_tests(){
		if(defined('TBK_ENVIRONMENT')) {
			if(TBK_ENVIRONMENT == 'prod') {
				return false;
			}
		}
	}
}

TBK_Testing::get_instance();