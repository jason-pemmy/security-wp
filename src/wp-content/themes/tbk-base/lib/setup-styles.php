<?php
/*
Description: Setup the common styles/shortcodes that TBK uses
Version:     1.0.0
Author:      Jonelle Carroll-Berube | tbk Creative
*/

$tbk_setup_styles = new tbk_Setup_Styles();

class tbk_Setup_Styles {

	static $shortcodes = array(
		'style-guide',
		'wptest-io',
	);

	function __construct() {
		add_filter( 'after_switch_theme', array( &$this, 'add_custom_styles' ));
	}

	function add_custom_styles(){
		//check to see if there is a sample page
		$sample_page = get_page_by_path('sample-page');
		if( empty($sample_page)) {
			$sample_page = array(
				'post_title' => 'Sample Page',
				'post_type' => 'page',
				'post_status' => 'publish',
				'post_content' => self::build_content(),
			);
		} else {
			$sample_page->post_content = self::build_content();
		}
	}

	function build_content(){
		$content = '';
		foreach(self::$shortcodes as $s) {
			if(function_exists('vc_map')) {
				//it means that VC is turned on!
				$content .= '[vc_row][vc_column width="1/1"]['.$s.'][/vc_column][/vc_row]';
			} else {
				$content .= '['.$s.']';
			}
		}
		return $content;
	}

}