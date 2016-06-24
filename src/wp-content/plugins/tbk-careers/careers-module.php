<?php
/*
 * Plugin Name: tbk Career Module
 * Version: 1.0.4
 * Description: This will handle the Career section of the website
 * Author: tbk Creative
 * Author URI: http://www.tbkcreative.com/
 * Requires at least: 4.1
 * Tested up to: 4.1
 */

if ( ! defined( 'CAREERS_BASE_FILE' ) ){
	define( 'CAREERS_BASE_FILE', __FILE__ );
}
if ( ! defined( 'CAREERS_BASE_DIR' ) ){
	define( 'CAREERS_BASE_DIR', dirname( CAREERS_BASE_FILE ) );
}
if ( ! defined( 'CAREERS_PLUGIN_URL' ) ) {
	define( 'CAREERS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'CAREER_DOMAIN' ) ) {
	define( 'CAREER_DOMAIN', 'tbk_careers' );
}

class TBK_Careers {

	static $instance = false;
	static $post_obj = array();
	static $post_page = null;
	static $career_image_size = null;

	private function __construct() {
		add_action('init', array( &$this, 'setup_custom_posts' ), 100);
		add_action('init', array( &$this, 'setup_acf' ), 100);
		add_filter('template_include', array( &$this, 'template_include' ), 100);
		add_filter('single_template', array( &$this, 'maybe_change_template' ), 100);
		add_action('wp_print_styles', array( &$this, 'enqueue_styles' ) );
		add_action('after_setup_theme', array( &$this, 'setup_image_sizes' ));
		add_filter('careers_back_to', array( &$this, 'careers_back_to' ), 1, 1);
		//custom filters
		add_filter('career_image', array( &$this, 'career_image' ), 1);
		add_filter('career_image_size', array( &$this, 'career_image_size' ));

		include_once('library/inflector-helper.php');
		include_once('library/fallbacks.php');
		include_once('library/http-build-url.php');
		include_once('controllers/shortcodes.php');
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

	public function setup_custom_posts() {

		$labels = array(
			'name'               => _x( 'Career', 'post type general name', CAREER_DOMAIN ),
			'singular_name'      => _x( 'Career', 'post type singular name', CAREER_DOMAIN ),
			'menu_name'          => _x( 'Careers', 'admin menu', CAREER_DOMAIN ),
			'name_admin_bar'     => _x( 'Career', 'add new on admin bar', CAREER_DOMAIN ),
			'add_new'            => _x( 'Add New', 'career', CAREER_DOMAIN ),
			'add_new_item'       => __( 'Add New Career', CAREER_DOMAIN ),
			'new_item'           => __( 'New Career', CAREER_DOMAIN ),
			'edit_item'          => __( 'Edit Career', CAREER_DOMAIN ),
			'view_item'          => __( 'View Career', CAREER_DOMAIN ),
			'all_items'          => __( 'All Careers', CAREER_DOMAIN ),
			'search_items'       => __( 'Search Careers', CAREER_DOMAIN ),
			'parent_item_colon'  => __( 'Parent Careers:', CAREER_DOMAIN ),
			'not_found'          => __( 'No Careers found.', CAREER_DOMAIN ),
			'not_found_in_trash' => __( 'No Careers found in Trash.', CAREER_DOMAIN ),
		);

		$args = array(
			'labels'  => $labels,
			'public'  => true,
			'publicly_queryable'  => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'query_var' => true,
			'rewrite' => array( 'slug' => 'careers' ),
			'capability_type' => 'post',
			'has_archive'  => true,
			'hierarchical' => false,
			'menu_position'  => null,
			'public' => true,
			'supports' => array(
				'title', 'editor', 'thumbnail',
			)
		);

		register_post_type( 'career', apply_filters('careers_setup', $args ));
		self::$post_obj = get_post_type_object('career');
	}

	function setup_acf(){
		//set up ACF fields
		if( function_exists('register_field_group') ) {
			//get current version of ACF
			global $acf;
			if(floatval($acf->settings['version']) > 5) {

			} else {

			}

			$career_fields = array(
				array(
					'key' => 'field_location',
					'label' => 'Location',
					'name' => 'location',
					'prefix' => '',
					'type' => 'text',
					'instructions' => '',
				),
				array (
					'key' => 'field_apply_online',
					'label' => 'Apply Online',
					'name' => 'apply_online',
					'prefix' => '',
					'type' => 'text',
					'instructions' => 'Where should users apply online?',
				),
			);

			register_field_group(array(
				'key' => 'group_career_details',
				'title' => 'Career Details',
				'fields' => apply_filters('career_fields', $career_fields),
				'location' => array(
					array(
						array(
							'param' => 'post_type',
							'operator' => '==',
							'value' => 'career',
						),
					),
				),
				'menu_order' => 0,
				'position' => 'normal',
				'style' => 'default',
				'label_placement' => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen' => '',
			));
		}
	}

	public static function load_template($template) {
		$template = strpos($template, '.php') === false?$template.'.php':$template;
		if ( $theme_file = locate_template( array( 'views/' . $template ) ) ) {
			$file = $theme_file;
		} elseif ( $theme_file = locate_template( array( $template ) ) ) {
			$file = $theme_file;
		} else {
			$file = CAREERS_BASE_DIR . '/views/' . $template;
		}
		return apply_filters( 'careers_template_' . $template, $file );
	}

	public static function check_template_exists($template) {
		//check to see if there is a file has been actually located.
		if($template !== false) {
			$pathinfo = pathinfo($template);
			//Wordpress returns a slug if the template has NOT been located. We need to see if it's been found.
			if( ! empty($pathinfo['filename']) && empty($pathinfo['extension'])) {
				return false;
			} else {
				return true;
			}
		}
		return false;
	}

	public static function find_theme_template(){
		$templates = array(
			'archive',
			'single',
		);
		$template = false;
		foreach($templates as $t) {
			$func = 'is_'.$t;
			if($func() === true) {
				$template = $t.'-'.get_post_type();
				if(file_exists(locate_template($template.'.php'))) {
					$template = locate_template($template.'.php');
				} elseif(file_exists(locate_template(plural($template).'.php'))) {
					$template = locate_template(plural($template).'.php');
				} elseif(file_exists(locate_template($t.'.php'))) {
					$template = locate_template($t.'.php');
				}
			}
		}

		if(self::check_template_exists($template) === false) {
			return false;
		}

		return $template;
	}

	public function enqueue_styles() {
		if( ! is_admin()) {
			wp_register_style( 'tbk-careers', CAREERS_PLUGIN_URL.'css/careers.css' );
			wp_enqueue_style('tbk-careers');
		}
	}

	public static function template_include($template) {
		if(is_post_type_archive('career')) {
			//attempt to load our archive page
			$theme_template = self::find_theme_template();
			if($theme_template !== false) {
				return $theme_template;
			}
			//meaning that this is the careers page...
			add_filter('the_content', function(){
				return do_shortcode('[careers-module]');
			});
		}
		return $template;
	}

	public static function maybe_change_template($template) {
		$post_type = get_post_type();
		/*
		 * This will load in the single template (varying on the post type) in this order:
		 * theme/views/single-post.php
		 * theme/single-post.php
		 * then finally, plugin/views/single-post.php
		 * If there isn't a file found in the plugin for it, it looks for single.php in the theme
		 */
		if($post_type == 'career') {
			$theme_template = self::find_theme_template();
			$template = self::load_template('single-'.$post_type);

			if( ! file_exists($template)) {
				$template = self::find_theme_template();
				//by WP default, if false, this will load the template heirarchy within the theme.
				//in this case, we will want to use our single.php
				if( empty($template)) {
					$template = CAREERS_BASE_DIR . '/views/single.php';
				}
			}
		}
		return $template;
	}

	public static function humanize($str) {
		$str = ucwords(preg_replace('/[-]+/', ' ', strtolower(trim($str))));
		return ucwords(preg_replace('/[_]+/', ' ', strtolower(trim($str))));
	}

	public static function careers_back_to($text = null){
		if( empty($text)) {
			$text = __('Back to', LEARN_DOMAIN);
			$text .= ' <strong>'.plural(self::$post_obj->labels->name).'</strong>';
		}
		$url = self::careers_url();
		return '<a class="careers-back-to" href="'.$url.'">
		<i class="icon-chevron-left"></i><span class="link-text">'.$text.'</span></a>';
	}

	public static function careers_url() {
		return site_url(self::$post_obj->rewrite['slug']);
	}

	public static function post_type_display(){
		global $wp_query;
		$post_type = get_post_type();
		if($post_type == 'post') {
			//check for display only tag
			$display_only = get_query_var('display_only');
			if( ! empty($display_only)) {
				$post_type = $display_only;
			} else {
				return false;
			}
		}
		return $post_type;
	}

	public static function career_image(){
		if(has_post_thumbnail(get_the_ID())){
			return get_the_post_thumbnail(get_the_ID(), 'careers_image');
		}
		return false;
	}

	function career_image_size() {
		return array( 400, 150, true );
	}

	public static function setup_image_sizes(){
		self::$career_image_size = apply_filters('career_image_size', self::$career_image_size);
		if( ! empty(self::$career_image_size)) {
			list($w, $h, $crop) = self::$career_image_size;
			add_image_size( 'career_image', $w, $h, $crop );
		}
	}

	public static function encode_url($url) {
		if(strpos(strtolower($url), 'subject') !== false) {
			$parse_url = parse_url($url);
			if( ! empty($parse_url['query'])) {
				$p_query = explode('=', $parse_url['query']);
				foreach($p_query as $k => $p) {
					$p_query[$k] = rawurlencode($p);
				}
				$parse_url['query'] = implode('=', $p_query);
			}
			$url = http_build_url($apply_online, $parse_url);
		}
		return $url;
	}
}

TBK_Careers::get_instance();
