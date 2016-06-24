<?php

$tbk_careers_shortcodes = new TBK_Careers_Shortcodes();
class TBK_Careers_Shortcodes  {
	function __construct() {
		$this->register('careers-module',
			array(
				'description' => 'Display the Careers Module',
			));
		$this->register('apply-online', false);
		$this->register('career-share', false);
		$this->register('careers-available');
	}

	public function register($tag, $vc_map = true) {
		add_shortcode($tag, array( &$this, strtolower(str_replace('-', '_', $tag )) ));
		if(function_exists('vc_map') && $vc_map !== false) {
			$vc_map = shortcode_atts(
				array(
					'name' => TBK_Careers::humanize($tag),
					'base' => $tag,
					'category' => 'tbk',
					'show_settings_on_create' => false,
					'description' => null,
					'params' => null,
				), $vc_map
			);
			$vc_map = apply_filters('register_'.$tag, $vc_map);
			vc_map($vc_map);
		}
	}

	function careers_module($atts, $content) {
		global $wp_query;
		$args = array(
			'post_status' => 'publish',
			'post_type' => 'career',
			'posts_per_page' => -1,
		);
		$careers = new WP_Query($args);
		$career_page = get_page_by_path('careers');
		$content = $career_page->post_content;
		ob_start();
		include(TBK_Careers::load_template('careers-content-intro'));
		include(TBK_Careers::load_template('loop-careers'));
		return ob_get_clean();
	}

	function apply_online($atts, $content) {
		$apply_online = get_field('apply_online', get_the_ID());

		if(strpos(strtolower($apply_online), 'subject') !== false) {
			$parse_url = parse_url($apply_online);
			if( ! empty($parse_url['query'])) {
				$p_query = explode('=', $parse_url['query']);
				foreach($p_query as $k => $p) {
					$p_query[$k] = rawurlencode($p);
				}
				$parse_url['query'] = implode('=', $p_query);
			}
			$apply_online = http_build_url($apply_online, $parse_url);
		}

		ob_start();
		include(TBK_Careers::load_template('apply-online'));
		return ob_get_clean();
	}

	function career_share($atts, $content) {
		ob_start();
		include(TBK_Careers::load_template('career-share'));
		return ob_get_clean();
	}

	function careers_available(){
		$args = array(
			'post_status' => 'publish',
			'post_type' => 'career',
			'posts_per_page' => -1,
		);
		$careers = new WP_Query($args);
		ob_start();
		include(TBK_Careers::load_template('careers-available'));
		return ob_get_clean();
	}

}

