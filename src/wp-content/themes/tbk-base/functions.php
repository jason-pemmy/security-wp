<?php
define( 'THEME_PATH', dirname( __FILE__ ) . '/' );
define( 'THEME_URL', trailingslashit( get_stylesheet_directory_uri() ) );

require_once THEME_PATH . '/lib/class.TBK-Theme.php';

class The_Theme extends TBK_Theme {

	/**
	 * Static property to hold our singleton instance
	 */
	static $instance = false;

	/**
	 * This is our constructor, which is private to force the use of
	 * getInstance() to make this a Singleton
	 *
	 * @return void
	 */
	private function __construct() {
		/* init methods */
		$this->init();
	}

	/**
	 * Simply instantiates the singleton
	 *
	 * @return void
	 */
	public function instantiate() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return null;
	}

	/**
	 * If an instance exists, this returns it.    If not, it creates one and
	 * retuns it.
	 *
	 * @return Theme
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	private function init() {
		$this->iterate_mvc( array(
			'models',
			'controllers',
		) );

		//libraries
		TBK_Render::load( array(
			'Mobile_Detect',
			'inflector_helper',
			'string_helper',
			'disable-updates',
			'setup-styles',
			'social-share',
		) );

		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'menus' );

		add_action( 'init', array( &$this, 'config_custom_posts' ), 5 );
		add_action( 'wp_head', array( &$this, 'add_ie_html5_shim' ) );
		add_action( 'wp_print_scripts', array( &$this, 'enqueue_scripts' ) );
		add_action( 'wp_print_styles', array( &$this, 'enqueue_styles' ) );

		add_filter( 'show_admin_bar', '__return_false' );
		add_filter( 'body_class', array( &$this, 'custom_body_class' ) );
		add_filter( 'wp_footer', array( &$this, 'add_ga' ) );
		add_filter( 'wp_footer', array( &$this, 'custom_footer_code' ) );

		//responsive image sizes
		self::create_image_sizes( 'desktop-lg', 1200, 9999, true );
		self::create_image_sizes( 'desktop-sm', 992, 9999, true );
		self::create_image_sizes( 'tablet', 768, 500, true );
		self::create_image_sizes( 'mobile-lg', 500, 300, true );
		self::create_image_sizes( 'mobile-sm', 380, 300, true );

	}
		
	public function enqueue_scripts() {
		$js_directory = self::get_stylesheet_dir() . '/js/';

		if ( ! is_admin() ) {
			wp_enqueue_script( 'angularjs', $js_directory . 'vendor/angular.min.js' );
			wp_enqueue_script( 'angular-router', $js_directory . 'vendor/angular-route.min.js' );
			wp_enqueue_script( 'angular-animate', $js_directory . 'vendor/angular-animate.min.js' );
			wp_enqueue_script( 'angular-aria', $js_directory . 'vendor/angular-aria.min.js' );
			wp_enqueue_script( 'angular-icons', $js_directory . 'vendor/angular-material-icons.min.js' );
			wp_enqueue_script( 'angular-material', $js_directory . 'vendor/angular-material.min.js' );			
			wp_enqueue_script( 'tbk-bootstrap-js', $js_directory . 'vendor/bootstrap.min.js' );
			wp_enqueue_script( 'typekit', '//use.typekit.net/ynv5tpe.js' );
			wp_enqueue_script( 'modernizr', $js_directory . 'vendor/modernizr.js' );	
			wp_enqueue_script( 'angular-controllers', $js_directory . 'tbk-security.js' );
			wp_enqueue_script( 'angular-directives', $js_directory . 'tbk-security-directives.js' );
			wp_enqueue_script( 'main-scripts', $js_directory . 'scripts.js' );
			
		}

		wp_localize_script( 'main-scripts', 'baseURL', home_url() );
	}
	
	public function enqueue_styles() {
		wp_enqueue_style( 'angular-material-icons', get_template_directory_uri() . '/css/unminified/angular-material-icons.css' );
		wp_enqueue_style( 'angular-material-style', get_template_directory_uri() . '/css/unminified/angular-material.css' );
		
		wp_enqueue_style( 'security-monitor-navbar', get_template_directory_uri() . '/css/unminified/security-monitor-navbar.css' );
		wp_enqueue_style( 'security-monitor-content-list', get_template_directory_uri() . '/css/unminified/security-monitor-content-list.css' );
	}
	

	function get_stylesheet_dir( $key = 'url' ) {
		return esc_url( get_stylesheet_directory_uri() );
	}

	function config_custom_posts() {
		global $included_modules;
		if ( class_exists( 'tbkcp_register_post' ) ) {
			// @codingStandardsIgnoreStart
			/*
			$tbkrp = $included_modules ['tbkcp_register_post'];
			$custom_posts = array(
				'Eguide' => array(),
			);
			$tbkrp->add_custom_posts( $custom_posts );
			*/
			// @codingStandardsIgnoreEnd
		}
		if ( class_exists( 'tbkcp_register_taxonomy' ) ) {
			// @codingStandardsIgnoreStart
			/*
			$tbkrt = $included_modules ['tbkcp_register_taxonomy'];
			$taxonomies = array(
				'Story' => array( 'post_types' => 'portfolio' ),
			);
			$tbkrt->add_custom_taxonomies( $taxonomies );
			*/
			// @codingStandardsIgnoreEnd
		}
	}

	function add_ie_html5_shim() {
		echo '<!--[if lt IE 9]>';
		echo '<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script> ';
		echo '<script src="' . get_bloginfo( 'stylesheet_directory' ) . '/js/respond.js"></script> ';
		echo "<link rel='stylesheet' id='ws-ltie9-css'  href='" . get_bloginfo( 'stylesheet_directory' ) . "/ltie9.css' type='text/css' media='all' />";
		echo '<![endif]-->';
	}

	function add_ga() {
		if ( function_exists( 'the_field' ) ) {
			//only display tracking on staging server
			if ( 'stage' == TBK_ENVIRONMENT ) {
				the_field( 'analytics', 'option' );
				the_field( 'ga_remarketing', 'option' );
			}
		}
	}

	function custom_body_class( $classes ) {
		$detect = new Mobile_Detect;
		if ( $detect->isMobile() ) {
			$classes[] = 'is_mobile';
		} else {
			$classes[] = 'is_desktop';
		}
		$ie_ver = $detect->version( 'IE' );
		if ( false !== $ie_ver && (float) $ie_ver <= 9.0 ) {
			$classes[] = 'ltIE9';
		}
		if ( is_404() ) {
			$classes[] = 'page';
		}

		return $classes;
	}

	function custom_footer_code() {
		if ( function_exists( 'the_field' ) ) {
			the_field( 'footer_code', 'options' );
		}
	}

	public static function get_templatera( $slug ) {
		if ( is_404() ) {
			return false;
		}
		$template = get_page_by_path( $slug, OBJECT, 'templatera' );
		if ( ! empty( $template ) ) {
			return do_shortcode( $template->post_content );
		}

		return false;
	}

}

The_Theme::get_instance();