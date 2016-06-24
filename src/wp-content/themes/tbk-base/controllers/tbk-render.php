<?php
if ( ! class_exists( 'TBK_Render' ) ) {
	class TBK_Render extends Base_Factory {

		static $params = array();

		function __construct( $params = array() ) {
			self::$params = shortcode_atts( array(
				'folder' => THEME_PATH . 'views/',
				'library' => THEME_PATH . 'lib/',
			), $params );
		}

		public static function shortcode_view( $render_view, $atts = null ) {
			return self::render( 'shortcodes/' . $render_view, $atts );
		}

		public static function render( $render_view, $atts = null ) {
			if ( file_exists( self::$params['folder'] . $render_view . '.php' ) ) {
				if ( ! empty( $atts ) ) {
					extract( $atts );
				}
				ob_start();
				include( self::$params['folder'] . $render_view . '.php' );

				return ob_get_clean();
			}

			return __( 'Error loading view.', 'the-theme' ) . ' ' . $render_view;
		}

		public static function load( $libraries ) {
			if ( ! is_array( $libraries ) ) {
				$libraries = array( $libraries );
			}
			foreach ( $libraries as $library ) {
				if ( file_exists( self::$params['library'] . $library . '.php' ) ) {
					include( self::$params['library'] . $library . '.php' );
				} else {
					echo __( 'Error loading', 'the-theme' ) . ' ' . $l . ' ' . __( 'library', 'the-theme' ) . ' ' . $library . '.';
					die;
				}
			}
		}
	}

	TBK_Render::instantiate();
}
