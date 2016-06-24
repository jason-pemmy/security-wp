<?php

/**
 * Class Content_Model
 *
 */
class Content_Model extends Base_Factory {

	function __construct() {
		add_filter( 'filter_phone_for_href', array( &$this, 'phone_href' ) );
		add_filter( 'gather_post_title_names', array( &$this, 'get_post_titles_only' ) );
		add_filter( 'gather_term_names', array( &$this, 'get_term_names_only' ) );
		add_filter( 'string_as_classes', array( &$this, 'string_as_classes' ), 10, 2 );
		add_filter( 'remove_empty_p', array( &$this, 'remove_empty_p' ), 20, 1 );
		add_filter( 'tbk_excerpt', array( &$this, 'tbk_excerpt' ), 10, 5 );
		add_filter( 'maybe_add_comma', array( &$this, 'maybe_add_comma' ), 10, 2);
		add_filter( 'filter_unnecessary_data', array( &$this, 'filter_unnecessary_data' ), 10, 3);
	}

	public static function get_cpt_for_vc( $post_type, $where = null ) {
		global $wpdb;
		$results = array();

		$s = 'select ID, post_title from ' . $wpdb->posts . ' where post_type = "' . $post_type . '" and post_status = "publish"';
		if ( ! empty( $where ) ) {
			$s .= ' and ' . $where;
		}
		foreach ( $wpdb->get_results( $s ) as $p ) {
			$results[ $p->post_title ] = $p->ID;
		}

		return $results;
	}

	public static function prepare_pipe( $arr ) {
		if ( is_empty_array( $arr ) ) {
			$piped = array();
			foreach ( $arr as $a ) {
				$piped[] = $a->ID . ' | ' . $a->post_title;
			}
			$arr = $piped;
		}

		return $arr;
	}

	public static function extract_pipe( $pipe ) {
		if ( ! empty( $pipe ) ) {
			return explode( ' | ', $pipe );
		}

		return false;
	}

	public static function get_siblings( $post = null ) {
		global $wpdb;
		if ( empty( $post ) ) {
			global $post;
		} elseif ( ! is_object( $post ) ) {
			if ( is_numeric( $post ) ) {
				$post = get_post( $post );
			} else {
				$post = get_page_by_path( $post );
			}
		}

		$query = 'select * from ' . $wpdb->posts . ' where post_parent = %d AND post_status = "publish" AND post_type = %s ORDER BY menu_order ASC';
		if ( empty( $post->post_parent ) ) {
			// nav will be all children
			$query = $wpdb->prepare( $query, $post->ID, $post->post_type );
		} else {
			// nav will be all siblings
			$query = $wpdb->prepare( $query, $post->post_parent, $post->post_type );
		}

		$items = $wpdb->get_results( $query );
		$items = array_map( 'wp_setup_nav_menu_item', $items );
		_wp_menu_item_classes_by_context( $items );
		foreach ( $items as $item ) {
			$item->classes[] = 'menu-item-' . $item->post_name;
			if ( isset( $current ) and ( $item->ID == $current or $item->post_name == $current ) ) {
				$item->classes[] = 'current-menu-item';
			}
		}

		return $items;
	}

	function get_family_pages( $post, $order = 'menu_order' ) {
		global $wpdb;
		$query = 'select * from ' . $wpdb->posts . ' where 1=1
		and post_type="' . $post->post_type . '" and post_status ="publish"
		and (post_parent=%s xor ID = %s)
		order by ' . $order . ' asc';

		if ( empty( $post->post_parent ) ) {
			// nav will be all children, with parent
			$query = $wpdb->prepare( $query, $post->ID, $post->ID );
		} else {
			// nav will be all siblings, with parent
			$query = $wpdb->prepare( $query, $post->post_parent, $post->post_parent );
		}

		return $wpdb->get_results( $query );
	}

	function get_post_titles_only( $posts ) {
		$return = array();
		$singular = false;
		if ( is_object( $posts ) ) {
			$posts = array( $posts );
			$singular = true;
		}

		if( is_array($posts)) {
			foreach ( $posts as $k => $v ) {
				$return[] = $v->post_title;
			}
			if ( true === $singular ) {
				return array_pop( $return );
			}
		}

		return $return;
	}

	function get_term_names_only( $terms ) {
		$return = array();
		$singular = false;
		if ( is_object( $terms ) ) {
			$terms = array( $terms );
			$singular = true;
		}

		if( is_array($terms)) {
			foreach ( $terms as $k => $v ) {
				$return[] = $v->name;
			}
			if ( true === $singular ) {
				return array_pop( $return );
			}
		}

		return $return;
	}

	function phone_href( $phone ) {
		$ext = '';
		//if it has an extension, lets set this up
		$phone = str_replace(array(
			'ext',
			'ext.',
			'extension',
		), '|', $phone);
		if(false !== strpos($phone, '|')) {
			$ext = explode('|', $phone);
			$ext = array_pop(preg_replace( '/[^0-9]/', '', $ext ));
		}
		$phone = preg_replace( '/[^0-9]/', '', $phone ); //only allow numbers
		$phone = ! empty($ext)?str_replace($ext, ','.$ext, $phone):$phone;

		return $phone;
	}

	public static function get_slug_by_id( $post_id ) {
		global $wpdb;
		$s = 'select post_name from ' . $wpdb->posts . ' where ID = %d and post_status = "publish" limit 1';

		return $wpdb->get_var( $wpdb->prepare( $s, $post_id ) );
	}

	public static function get_post_by_slug( $slug, $post_type ) {
		//this does not require parent/child relationships like get_page_by_path does
		global $wpdb;
		$s = 'select * from ' . $wpdb->posts . ' where post_name = %s and post_status = "publish" and post_type = %s limit 1';

		return $wpdb->get_row( $wpdb->prepare( $s, $slug, $post_type ) );
	}

	function string_as_classes( $string, $prefix = '' ) {
		$classes = array();
		if( ! is_array($string)) {
			$string = explode( ',', $string );
		}

		//check to see if last character has a dash
		if ( ! empty( $prefix ) && substr( $prefix, - 1 ) != '-' ) {
			$prefix .= '-';
		}
		if ( is_array( $string ) ) {
			foreach ( $string as $str ) {
				$classes[] = sanitize_title( $prefix . trim( $str ) );
			}
		} else {
			$classes[] = sanitize_title( $prefix . trim( $string ) );
		}

		return implode( ' ', $classes );
	}

	public static function get_taxonomy_terms( $tax = 'position-type' ) {
		global $wpdb;
		$query = 'SELECT ' . $wpdb->terms . '.term_id, ' . $wpdb->terms . '.slug, ' . $wpdb->terms . '.name FROM ' . $wpdb->terms . '
				left join ' . $wpdb->term_taxonomy . ' on ' . $wpdb->terms . '.term_id = ' . $wpdb->term_taxonomy . '.term_id
				where ' . $wpdb->term_taxonomy . '.taxonomy = "' . $tax . '"';
		$terms = $wpdb->get_results( $query );

		return $terms;
	}

	public static function get_taxonomy_terms_for_vc($tax) {
		$terms = self::get_taxonomy_terms($tax);
		$return = array(
			'Please select a term' => '',
		);
		foreach($terms as $term) {
			$return[ $term->name ] = $term->term_id;
		}
		return $return;
	}

	public static function get_repeater( $acf_field, $post_id = null ) {
		/*
		 * since we're creating dynamic repeaters, for some reason ACF cannot read these.
		 */
		if ( empty( $post_id ) ) {
			$post_id = get_the_ID();
		}

		global $wpdb;
		$repeater_count = get_post_meta( $post_id, $acf_field, true );
		$repeater_metadata = array();
		if ( ! empty( $repeater_count ) ) {
			for ( $i = 0; $i < $repeater_count; $i ++ ) {
				$s = 'select replace(meta_key, %s, "") as meta_key, meta_value from ' . $wpdb->postmeta . ' where meta_key like %s and post_id = %d';
				$meta = $wpdb->get_results( $wpdb->prepare( $s, $acf_field . '_' . $i . '_', $acf_field . '_' . $i . '_%', $post_id ) );
				if ( ! empty( $meta ) ) {
					$repeater_meta = array();
					foreach ( $meta as $key => $m ) {
						$repeater_meta[ $i ][ trim( $m->meta_key ) ] = trim( $m->meta_value );
					}
					if ( ! empty( $repeater_meta ) ) {
						$repeater_metadata = array_merge( $repeater_metadata, $repeater_meta );
					}

				}
			}
		}

		return $repeater_metadata;
	}

	function remove_empty_p( $content ) {
		$content = wpb_js_remove_wpautop( $content, true );
		return preg_replace( '#<p>(\s|&nbsp;|</?\s?br\s?/?>)*</?p>#', '', reduce_multiples( trim($content), ' ', true ) );
	}

	function tbk_excerpt($content, $length = 55, $read_more = '...', $highlight = false, $allowed_tags = '') {
		return The_Theme::trim_excerpt(strip_tags(do_shortcode($content), $allowed_tags), $length, $read_more, $highlight);
	}

	public static function setup_meta_query_like($value, $meta_key) {
		return array(
			'key' => $meta_key,
			'value' => '"'.$value.'"',
			'compare' => 'LIKE',
		);
	}

	function maybe_add_comma( $current_key, $array ) {
		return ( $current_key < count( $array ) - 1 ? ', ' : '' );
	}

	function get_child_pages($parent_id) {
		$args = array(
			'post_type' => 'page',
			'post_parent' => $parent_id,
		);
		$query = new WP_Query($args);
		return $query->posts;
	}

	public static function filter_unnecessary_data($values, $post_id = null, $field = null) {
		$was_object = false;
		if(is_object($values)) {
			$values = array( $values );
			$was_object = true;
		}
		if(is_array($values)) {
			//lets remove unnessary data
			$not_needed = array(
					'post_date_gmt',
					'guid',
					'post_status',
					'comment_status',
					'comment_count',
					'to_ping',
					'post_content',
					'ping_status',
					'pinged',
					'post_password',
					'post_modified',
					'post_modified_gmt',
					'post_content_filtered',
					'menu_order',
					'post_mime_type',
					'filter',
			);
			foreach($values as $arr_key => $value_objs) {
				foreach($value_objs as $obj_key => $value) {
					if (in_array( $obj_key, $not_needed ) ) {
						unset($values[$arr_key]->$obj_key);
					}
				}
			}
			if(true === $was_object) {
				$values = array_pop($values);
			}
		}

		return $values;
	}

}
Content_Model::instantiate();