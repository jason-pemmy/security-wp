<?php
class Related_Archive extends Base_Factory {

	function __construct() {
		add_filter( 'wp', array( &$this, 'setup_related_page' ), 10);
		add_filter( 'the_content', array( &$this, 'alter_content' ) );
	}

	function setup_related_page(){
		if(is_archive() || is_category() || is_home()){
			if(is_tax()) {
				//get taxonomy's background image.
				$taxonomy = get_queried_object();
				set_query_var('related_thumb_id', get_field(apply_filters('related_thumb_field', 'page_banner'), $taxonomy));
				set_query_var('banner_text_color', get_field('banner_text_color', $taxonomy));

				//get post type's related page
				$related_page = get_page_by_path(The_Theme::get_current_post_type());
				if( empty($related_page)) {
					//let's look for the associated post type as plural.
					$related_page = get_page_by_path(plural(The_Theme::get_current_post_type()));
				}
				if( ! empty($related_page)) {
					set_query_var('related', $related_page);
				}
			} else {
				if ( class_exists( 'TBK_Learning' ) && (is_category() || is_home())) {
					$related_page = get_post(TBK_Learning::$post_page);
				} else {
					//attempt to find the archive type's related page.
					$queried_object = get_queried_object();
					if( ! empty($queried_object->rewrite['slug'])) {
						$related_page = get_page_by_path($queried_object->rewrite['slug']);
					}
				}

				if( ! empty($related_page)) {
					set_query_var('related', $related_page);
					set_query_var('related_thumb_id', get_post_thumbnail_id($related_page->ID));
					set_query_var('banner_text_color', get_post_meta($related_page->ID, 'banner_text_color', true));
				}
			}
		}
	}

	function alter_content($content){
		$post_type = get_post_type();
		if(class_exists('TBK_Learning')) {
			$post_types = TBK_Learning::$post_types;
		} else {
			$post_types = array();
		}
		if( ! in_array($post_type, $post_types) || is_archive()) {
			$related_page = self::get_related_content();
			if( ! empty($related_page)) {
				$content = $related_page;
			}
		}
		return $content;
	}

	public static function get_related_content(){
		//grab the relative page content
		$related_page = get_query_var('related');
		if(is_object($related_page)) {
			return do_shortcode($related_page->post_content);
		} else {
			return false;
		}

	}
}
Related_Archive::instantiate();