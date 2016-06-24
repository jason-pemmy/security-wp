<?php
$tbk_dummy_content = new tbk_Dummy_Content();
class tbk_Dummy_Content {

	function __construct() {
		add_filter( 'the_content', array( &$this, 'setup_dummy_content' ), 1000 );
	}

	function setup_dummy_content($content) {
		if(empty($content)) {
			$shortcodes = array();
			if(shortcode_exists('style-guide')){
				$shortcodes[] = '[style-guide]';
			}
			if(shortcode_exists('wptest-io')){
				$shortcodes[] = '[wptest-io]';
			}
			$content = do_shortcode(apply_filters('tbk_dummy_content_shortcodes', join('', $shortcodes)));
		}
		return $content;
	}
}