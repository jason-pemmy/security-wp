<?php
/*
 * Plugin Name: tbk Learning Module
 * Version: 1.0.10
 * Description: This will handle the learning section of the website
 * Author: tbk Creative
 * Author URI: http://www.tbkcreative.com/
 * Requires at least: 4.1
 * Tested up to: 4.1
 */

if ( ! defined( 'LEARNING_BASE_FILE' ) ){
	define( 'LEARNING_BASE_FILE', __FILE__ );
}
if ( ! defined( 'LEARNING_BASE_DIR' ) ){
	define( 'LEARNING_BASE_DIR', dirname( LEARNING_BASE_FILE ) );
}
if ( ! defined( 'LEARNING_PLUGIN_URL' ) ) {
	define( 'LEARNING_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'LEARN_DOMAIN' ) ) {
	define( 'LEARN_DOMAIN', 'tbk_learning' );
}

class TBK_Learning {

	static $instance = false;
	static $post_types = array();
	static $post_page = null;
	static $blog_image_size = null;

	private function __construct() {
		self::$post_types = get_option('options_learning_post_types');
		if(empty(self::$post_types)) {
			self::$post_types = array(
				'post',
			);
		}

		add_action('init', array( &$this, 'setup_acf' ), 100);
		add_action('init', array( &$this, 'setup_posts_archive' ), 100);
		add_filter('template_include', array( &$this, 'template_include' ), 100);
		add_filter('single_template', array( &$this, 'maybe_change_template' ), 100);

		add_action('init', array( &$this, 'custom_rewrite_rule' ), 10, 0);
		add_action('init', array( &$this, 'custom_rewrite_tag' ), 10, 0);
		add_action('wp_print_styles', array( &$this, 'enqueue_styles' ) );
		add_action('admin_notices', array( &$this, 'admin_notices' ) );
		add_filter('the_content', array( &$this, 'eguide_content' ), 10, 1 );
		add_action('after_setup_theme', array( &$this, 'setup_image_sizes' ));
		add_action('after_setup_theme', array( &$this, 'get_page_for_posts' ));

		//custom filters
		add_filter('learning_back_to', array( &$this, 'learning_back_to' ), 1, 1);
		add_filter('post_type_archive_link', array( &$this, 'post_type_archive_link' ), 1, 2);
		add_filter('learning_active_link', array( &$this, 'is_active_url' ), 1, 1);
		add_filter('learning_post_title', array( &$this, 'post_title' ), 1, 1);
		add_filter('learning_blog_image', array( &$this, 'learning_blog_image' ), 1);
		add_filter('learning_blog_image_size', array( &$this, 'learning_blog_image_size' ));
		add_filter( 'body_class', array( &$this, 'custom_body_class' ) );

		//post type learning dates
		add_filter('learning_date', array( &$this, 'learning_date' ), 1, 1);
		if( ! empty(self::$post_types)) {
			foreach ( self::$post_types as $pt ) {
				add_filter( 'learning_' . $pt . '_date', array( &$this, 'learning_date' ), 1, 1 );
			}
		}

		include_once('controllers/shortcodes.php');
	}

	function get_page_for_posts(){
		self::$post_page = get_option('page_for_posts');
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

	function setup_acf(){
		//set up ACF fields
		if( function_exists('register_field_group') ) {
			//get current version of ACF
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

			$post_types = get_post_types(
				array(
					'publicly_queryable' => true,
					'exclude_from_search' => false,
					'show_ui' => true,
				)
			);

			$option_fields = array(
				array(
					'key' => 'field_learning_post_types',
					'label' => 'Post Types',
					'name' => 'learning_post_types',
					'prefix' => '',
					'type' => 'checkbox',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'choices' => $post_types,
					'default_value' => array(
						'post' => 'post',
					),
					'layout' => 'vertical',
				),
			);

			if(in_array('eguide', $post_types)) {
				//set up eguide acf
				$option_fields[] = array(
					'key' => 'field_eguide_form_id',
					'label' => 'eGuide Form ID',
					'name' => 'eguide_form_id',
					'default_value' => '',
				);

				$eguide_fields = array(
					array(
						'key' => 'field_eguide_blurb',
						'label' => 'Blurb',
						'name' => 'blurb',
						'prefix' => '',
						'type' => 'wysiwyg',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'tabs' => 'all',
						'toolbar' => 'basic',
						'media_upload' => 0,
					),
					array(
						'key' => 'field_eguide_pdf',
						'label' => 'PDF',
						'name' => 'eguide_pdf',
						'prefix' => '',
						'type' => 'file',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'return_format' => 'url',
						'library' => 'all',
					),
				);
				$eguide_fields = apply_filters('learning_eguide_fields', $eguide_fields);

				//register eguide details
				register_field_group(array(
					'key' => 'group_eguide_details',
					'title' => 'eGuide Details',
					'fields' => $eguide_fields,
					'location' => array(
						array(
							array(
								'param' => 'post_type',
								'operator' => '==',
								'value' => 'eguide',
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

			if(in_array('webinar', $post_types)) {
				$webinar_fields = array(
					array(
						'key' => 'field_webinar_url',
						'label' => 'Webinar URL',
						'name' => 'webinar_url',
						'prefix' => '',
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
					array(
						'key' => 'field_webinar_date',
						'label' => 'Webinar Date',
						'name' => 'webinar_date',
						'prefix' => '',
						'type' => 'date_picker',
						'instructions' => '',
						'required' => 1,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'display_format' => 'F j, Y',
						'return_format' => 'm/d/Y',
						'first_day' => 0,
					),
					array(
						'key' => 'field_webinar_time',
						'label' => 'Webinar Time',
						'name' => 'webinar_time',
						'default_value' => '',
					),
				);
				$webinar_fields = apply_filters('learning_webinar_fields', $webinar_fields);

				//register eguide details
				register_field_group(array(
					'key' => 'group_webinar_details',
					'title' => 'Webinar Details',
					'fields' => $webinar_fields,
					'location' => array(
						array(
							array(
								'param' => 'post_type',
								'operator' => '==',
								'value' => 'webinar',
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

			$option_fields = apply_filters('learning_options_fields', $option_fields);
			register_field_group( array(
				'key' => 'group_learning_section',
				'title' => 'Learning Section',
				'fields' => $option_fields,
				'location' => $options_page,
				'options' => array(
					'position' => 'normal',
					'layout' => 'default',
					'hide_on_screen' => array(),
				),
				'menu_order' => 10,
				'position' => 'normal',
				'style' => 'default',
				'label_placement' => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen' => '',
			) );
		}
	}

	public static function load_template($template) {
		$template = strpos($template, '.php') === false?$template.'.php':$template;
		if ( $theme_file = locate_template( array( 'views/' . $template ) ) ) {
			$file = $theme_file;
		} elseif ( $theme_file = locate_template( array( $template ) ) ) {
			$file = $theme_file;
		} else {
			$file = LEARNING_BASE_DIR . '/views/' . $template;
		}
		return apply_filters( 'learning_template_' . $template, $file );
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
			wp_register_style( 'tbk-learning', LEARNING_PLUGIN_URL.'css/learning.css' );
			wp_enqueue_style('tbk-learning');
		}
	}

	public static function template_include($template) {
		if((is_home() || in_array(get_post_type(), self::$post_types)) && ! is_single()) {
			//check to see if the theme is overriding this view..
			$theme_template = self::find_theme_template();
			if($theme_template !== false) {
				return $theme_template;
			}
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
		if(in_array($post_type, self::$post_types)) {
			$theme_template = self::find_theme_template();
			$template = self::load_template('single-'.$post_type);

			if( ! file_exists($template)) {
				$template = self::find_theme_template();
				//by WP default, if false, this will load the template heirarchy within the theme.
				//in this case, we will want to use our single.php
				if( empty($template)) {
					$template = LEARNING_BASE_DIR . '/views/single.php';
				}
			}
		}
		return $template;
	}

	public static function humanize($str) {
		$str = ucwords(preg_replace('/[-]+/', ' ', strtolower(trim($str))));
		return ucwords(preg_replace('/[_]+/', ' ', strtolower(trim($str))));
	}

	public static function get_search_ids($keyword) {
		global $wpdb;
		$s = 'select ID from '.$wpdb->posts.' where (post_title like %s or post_content like %s) and post_type in ("'.
			 implode('","', self::$post_types).'")';
		$keyword = '%'.trim($keyword).'%';
		$ids = $wpdb->get_col($wpdb->prepare($s, $keyword, $keyword));
		if( empty($ids)) {
			//if there weren't any posts found, lets not search for any post ids
			$ids = array( 0 );
		}
		return $ids;
	}

	public static function learning_back_to($text = null){
		if( empty($text)) {
			$text = __('Back to <strong>Learning</strong>', LEARN_DOMAIN);
		}
		return '<a class="learning-back-to" href="'.get_permalink(self::$post_page).'">
		<i class="icon-chevron-left"></i><span class="link-text">'.$text.'</span></a>';
	}

	public static function post_type_archive_link($link, $post_type){
		if($post_type == 'post') {
			//set up our link to have rewrite catch posts only
			$link = trailingslashit(get_bloginfo('url')).'posts';
		}
		return $link;
	}

	public static function setup_posts_archive() {
		global $wp_post_types;
		$wp_post_types['post']->has_archive = true;
	}

	function custom_rewrite_rule() {
		add_rewrite_rule('^posts', 'index.php?post_type=post&display_only=post', 'top');
	}

	function custom_rewrite_tag() {
		add_rewrite_tag('%display_only%', '([^&]+)');
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

	public static function is_active_url($page){
		global $wp;
		$classes = null;
		$is_active = false;
		$current_url = home_url(add_query_arg(array(), $wp->request));
		if(empty($page)) {
			return $classes;
		}
		if(is_numeric($page)) {
			//lets find the current page id
			global $wp_query;
			$is_active = $wp_query->queried_object_id == intval($page);
		} else {
			$in_singular = strpos($current_url, $page);
			$in_plural = strpos($current_url, plural($page));
			$is_active = ($in_singular || $in_plural)?true:false;
		}

		if($is_active) {
			$classes = 'active';
		}
		return $classes;
	}

	public static function admin_notices(){
		if( empty(self::$post_page)) {
			echo '<div class="error">'.
				 wpautop(__('Please select the site posts page', LEARN_DOMAIN).
						 ' <a href="'.admin_url('options-reading.php').'">here</a>').
				 '</div>';
		}
	}

	public static function learning_date($date = null) {
		$format = 'F j, Y';
		if( empty($date)) {
			$post_type = get_post_type(get_the_ID());
			if($post_type == 'webinar') {
				//look for the webinar date
				$date = get_field('webinar_date', get_the_ID());
				if( ! empty($date)) {
					$date = date($format, strtotime($date));
				}
			} else {
				$date = get_the_date($format, get_the_ID());
			}
		}
		return ! empty($date)?date($format, strtotime($date)):null;
	}

	public static function post_title($title = null) {
		$post_type = get_post_type(get_the_ID());
		if(empty($title)) {
			$title = get_the_title(get_the_ID());
		}
		$html = do_shortcode('[learning_heading title="'.$title.'" post_type="'.$post_type.'"]');
		////add webinar date
		if($post_type == 'webinar') {
			$html .= do_shortcode('[learning_webinar_date]');
		} else {
			$html .= do_shortcode('[learning_date_published]');
		}

		return $html;
	}

	public static function learning_blog_image(){
		if(has_post_thumbnail(get_the_ID())){
			return get_the_post_thumbnail(get_the_ID(), 'learning_blog_image');
		}
		return false;
	}

	public static function eguide_content($content) {
		if(get_post_type() == 'eguide') {
			//lets add the form to the content.
			$form_id = get_field( 'eguide_form_id', 'options' );
			if ( ! empty($form_id )) {
				$eguide_pdf = get_field( 'eguide_pdf', get_the_ID());
				if( ! empty($eguide_pdf)) {
					if(is_array($eguide_pdf) && ! empty($eguide_pdf['url'])) {
						$eguide_pdf = $eguide_pdf['url'];
					}
					ob_start();
					gravity_form_enqueue_scripts( $form_id, true );
					gravity_form( $form_id, false, true, false, array(
						'eguide_title' => get_the_title(),
						'eguide_pdf' => $eguide_pdf,
					), true, 1 );
					$form = ob_get_clean();
					$content = '<div class="content-column eguide-column">'.$content.'</div>';
					$content .= '<div class="form-column eguide-column">'.$form.'</div>';
				}
			}
		}
		return $content;
	}

	function learning_blog_image_size() {
		return array( 400, 150, true );
	}

	public static function setup_image_sizes(){
		self::$blog_image_size = apply_filters('learning_blog_image_size', self::$blog_image_size);
		if( ! empty(self::$blog_image_size)) {
			list($w, $h, $crop) = self::$blog_image_size;
			add_image_size( 'learning_blog_image', $w, $h, $crop );
		}
	}

	function custom_body_class($classes) {
		$post_type = get_post_type();
		if(in_array($post_type, self::$post_types) || is_archive('category')) {
			if(is_category() || is_tag() || is_tax() || is_archive()){
				$classes[] = 'blog';
			}
		}
		return $classes;
	}
}

TBK_Learning::get_instance();

if ( ! function_exists('plural')) {
	function plural($str, $force = false) {
		$result = strval($str);

		$plural_rules = array(
			'/^(ox)$/'                 => '\1\2en',     // ox
			'/([m|l])ouse$/'           => '\1ice',      // mouse, louse
			'/(matr|vert|ind)ix|ex$/'  => '\1ices',     // matrix, vertex, index
			'/(x|ch|ss|sh)$/'          => '\1es',       // search, switch, fix, box, process, address
			'/([^aeiouy]|qu)y$/'       => '\1ies',      // query, ability, agency
			'/(hive)$/'                => '\1s',        // archive, hive
			'/(?:([^f])fe|([lr])f)$/'  => '\1\2ves',    // half, safe, wife
			'/sis$/'                   => 'ses',        // basis, diagnosis
			'/([ti])um$/'              => '\1a',        // datum, medium
			'/(p)erson$/'              => '\1eople',    // person, salesperson
			'/(m)an$/'                 => '\1en',       // man, woman, spokesman
			'/(c)hild$/'               => '\1hildren',  // child
			'/(buffal|tomat)o$/'       => '\1\2oes',    // buffalo, tomato
			'/(bu|campu)s$/'           => '\1\2ses',    // bus, campus
			'/(alias|status|virus)/'   => '\1es',       // alias
			'/(octop)us$/'             => '\1i',        // octopus
			'/(ax|cris|test)is$/'      => '\1es',       // axis, crisis
			'/s$/'                     => 's',          // no change (compatibility)
			'/$/'                      => 's',
		);

		foreach ($plural_rules as $rule => $replacement) {
			if (preg_match($rule, $result)) {
				$result = preg_replace($rule, $replacement, $result);
				break;
			}
		}

		return $result;
	}
}

if ( ! function_exists('get_field')) {
	function get_field($key, $post_id = null) {
		if($post_id == null){
			$post_id = get_the_ID();
		}
		return get_post_meta($post_id, $key, true);
	}
}

if ( ! function_exists('the_field')) {
	function the_field($key, $post_id = null) {
		echo get_field($key, $post_id);
	}
}