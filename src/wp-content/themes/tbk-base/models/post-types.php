<?php

class TBK_Post_Types extends Base_Factory { //TODO rename to custom post type (plural)

	static $post_type = '';

	function __construct() {
	}

	public static function default_query_args() {
		$args = parent::default_query_args();
		$args['post_type'] = self::$post_type;

		return $args;
	}

	public static function get_( $args = null ) {
		$args = wp_parse_args( $args, self::default_query_args() );
		$post_type_query = new WP_Query( $args );

		return $post_type_query->posts;
	}
}

TBK_Post_Types::instantiate();
