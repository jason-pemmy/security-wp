<?php

$tbk_learning_shortcodes = new TBK_Learning_Shortcodes();
class TBK_Learning_Shortcodes  {
	function __construct() {
		$this->register('learning-module',
			array(
				'description' => 'Display the Learning Module',
			));
		$this->register('learning_webinar_date', false);
		$this->register('learning_date_published', false);
		$this->register('learning_heading', false);
	}

	public function register($tag, $vc_map) {
		add_shortcode($tag, array( &$this, strtolower(str_replace('-', '_', $tag )) ));
		if(function_exists('vc_map') && $vc_map !== false) {
			$vc_map = shortcode_atts(
				array(
					'name' => TBK_Learning::humanize($tag),
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

	function learning_module($atts, $content) {
		global $wp_query;
		$current = TBK_Learning::post_type_display();
		$post_display = $post_types = TBK_Learning::$post_types;
		if( ! empty($current)) {
			$post_display = array(
				$current,
			);
		}

		$args = array(
			'post_status' => 'publish',
			'post_type' => array_values($post_display),
			'posts_per_page' => -1,
		);

		if(isset($_REQUEST['filter-keyword'])) {
			$args['post__in'] = TBK_Learning::get_search_ids($_REQUEST['filter-keyword']);
		}

		//if we're on a category page lets add the params in
		if(is_category() || is_tag() || is_tax()){
			$args = wp_parse_args($wp_query->query, $args);
		}

		$content = $wp_query->queried_object->post_content;
		$args = apply_filters('learning_module_args', $args);
		$learning = new WP_Query($args);
		ob_start();
		include(TBK_Learning::load_template('learning-content-intro'));
		include(TBK_Learning::load_template('learning-filter-bar'));
		include(TBK_Learning::load_template('loop-learning'));
		return ob_get_clean();
	}

	function learning_webinar_date(){
		ob_start();
		$date = get_field('webinar_date', get_the_ID());
		$time = get_field('webinar_time', get_the_ID());
		$url = get_field('webinar_url', get_the_ID());
		include(TBK_Learning::load_template('learning-webinar-date'));
		return ob_get_clean();
	}

	function learning_date_published(){
		$post_type = get_post_type();
		ob_start();
		include(TBK_Learning::load_template('learning-date-published'));
		return ob_get_clean();
	}

	function learning_heading($atts){
		extract( shortcode_atts( array(
			'title' => null,
			'post_type' => null,
		), $atts ) );
		if(empty($post_type)) {
			$post_type = get_post_type();
		}
		if(empty($title)) {
			$title = get_the_title();
		}
		ob_start();
		include(TBK_Learning::load_template('learning-heading'));
		return ob_get_clean();
	}

}

