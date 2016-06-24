<?php
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