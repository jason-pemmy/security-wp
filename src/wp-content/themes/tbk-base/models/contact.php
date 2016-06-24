<?php

class TBK_Contacts extends Base_Factory {

	function __construct() {
		parent::__construct();
	}

	public static function get_gform_ids(){
		$form_ids = get_field('contact_gravity_forms', 'options');
		if( ! is_empty_array($form_ids)) {
			return $form_ids[key($form_ids)];
		}
		return array();
	}

	public static function get_form_id($form_name) {
		$form_ids = self::get_gform_ids();
		return isset($form_ids[$form_name])?$form_ids[$form_name]:false;
	}
}

TBK_Contacts::instantiate();