<?php

/**
 * The main plugin class responsible for hooking into the wordpress logic
 *
 * @author Andre LeFort
 *
 */
class TBKGF_ReferralPlugin extends TBK_Plugin {

	/**
	 * The constructor!
	 *
	 * @param string $plugin_path
	 * @param string $plugin_url
	 * @param string $plugin_slug
	 */
	function __construct( $plugin_path = '', $plugin_url = '', $plugin_slug = '' ){

		parent::__construct( $plugin_path, $plugin_url, $plugin_slug);
		/* filters */
		$this->init_filters();
		/* actions */
		$this->init_actions();
		/* helpers */
		$this->init_helpers();
	}

	/**
	 * Init the action hooks for this plugin. This is the glue that binds the code in this plugin with the inner workings of wordpress
	 */
	private function init_actions(){
		add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );

	}

	/**
	 * Init any filters for this plugin
	 */
	private function init_filters(){
		add_filter('gform_field_value_referrer', array(&$this, 'add_referral_url_to_form'));
	}

	/**
	 * Initialize helpers for this plugin
	 *
	 */
	private function init_helpers(){

	}

	/**
	 * Init Admin area
	 */
	public function init_admin(){

	}

	/**
	 * Enqueue the necessary styles and scripts for the plugin in the admin area. This also localizes scripts as needed
	 */
	public function admin_enqueue_scripts(){

	}

	public function enqueue_scripts(){
		wp_register_script( 'tbk-gf-deploy-main', $this->get_plugin_url() . 'assets/js/app-main.js' );
		wp_register_script( 'jquery-cookie', $this->get_plugin_url() . 'assets/js/jquery-cookie.js' );

		wp_enqueue_script( 'tbk-gf-deploy-main' );
		wp_enqueue_script( 'jquery-cookie' );
	}

	public function enqueue_styles(){

	}

	public function add_referral_url_to_form( $value ){
		return $_COOKIE['tbkgf_referrer_cookie'];
	}
}