<?php
/*
Plugin Name: Slider Plugin
Description: jQuery Slides plugin
Author: Jonelle Carroll-Berube
Version: 1.0
*/

/**
 * This plugin, by default uses http://www.slidesjs.com/
 */
function _create_slider_post_type()
{
	/** Custom Content **/
	$labels = array(
		'name' => _x('Content Slider', 'post type general name'),
		'singular_name' => _x('slider', 'post type singular name'),
		'add_new' => _x('Add New Slider', 'Slider'),
		'add_new_item' => __('Add New Slider'),
		'edit_item' => __('Edit Slider'),
		'new_item' => __('New Slider'),
		'view_item' => __('View Slider'),
		'search_items' => __('Search Sliders'),
		'not_found' => __('Nothing found'),
		'not_found_in_trash' => __('Nothing found in Trash'),
		'parent_item_colon' => '',
	);

	$args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => false,
		'show_ui' => true,
		'query_var' => true,
		'menu_icon' => plugins_url('/images/image.png', __FILE__ ),
		'rewrite' => true,
		'capability_type' => 'page',
		'hierarchical' => true,
		'menu_position' => 20,
		'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'page-attributes' ),
	);

	//Register the post type.
	register_post_type('slider' , $args);
	$dimensions = get_option('content_slider_dimensions');
	add_image_size('slider-image', (isset($dimensions['w'])?$dimensions['w']:940), (isset($dimensions['h'])?$dimensions['h']:300), true);
}
add_action('init', '_create_slider_post_type');

function _slider_menu()
{
	add_submenu_page('edit.php?post_type=slider', 'Slider Settings', 'Slider Settings', 'manage_options', 'slider_settings', '_slider_settings');
}
add_action('admin_menu', '_slider_menu');

function _slider_settings()
{
	//save settings..
	if(isset($_POST['save_slider_settings']))
	{
		$settings = array();
		foreach(_slider_setting_vars() as $vars)
		{
			if(isset($_POST[$vars['name']]))
				$settings[$vars['name']] = $_POST[$vars['name']];
		}
		update_option('content_slider', $settings);

		//add new image size..
		update_option('content_slider_dimensions', array( 'w' => $_POST['width'], 'h' => $_POST['height'] ));
	}
	include_once(dirname(__FILE__).'\admin\slider_settings.php');
}

function _slider_setting_vars()
{
	$args = array(
		array( 'name' => 'preload', 'value' => true, 'text' => 'Preload', 'descr' => 'Set true to preload images in an image based slideshow.', 'type' => 'checkbox' ),
		array( 'name' => 'play', 'value' => 5000, 'text' => 'Play', 'descr' => 'Autoplay slideshow, a positive number will set to true and be the time between slide animation in milliseconds.', 'type' => 'text' ),
		array( 'name' => 'pause', 'value' => 5000, 'text' => 'Pause', 'descr' => 'Autoplay slideshow, a positive number will set to true and be the time between slide animation in milliseconds.', 'type' => 'text' ),
		array( 'name' => 'fadeSpeed', 'value' => 1000, 'text' => 'Fade Speed', 'descr' => 'Autoplay slideshow, a positive number will set to true and be the time between slide animation in milliseconds.', 'type' => 'text' ),
		array( 'name' => 'slideSpeed', 'value' => 5000, 'text' => 'Slide Speed', 'descr' => 'Autoplay slideshow, a positive number will set to true and be the time between slide animation in milliseconds.', 'type' => 'text' ),
		array( 'name' => 'hoverPause', 'value' => true, 'text' => 'Pause on Hover', 'descr' => 'Autoplay slideshow, a positive number will set to true and be the time between slide animation in milliseconds.', 'type' => 'checkbox' ),
		array( 'name' => 'crossfade', 'value' => true, 'text' => 'Crossfade', 'descr' => 'Autoplay slideshow, a positive number will set to true and be the time between slide animation in milliseconds.', 'type' => 'checkbox' ),
		array( 'name' => 'pagination', 'value' => 1, 'text' => 'Display Pagination', 'descr' => '', 'type' => 'checkbox' ),
		array( 'name' => 'effect', 'value' => 'fade', 'text' => 'Effect', 'descr' => 'Set <strong>effect</strong>, <strong>slide</strong> or <strong>fade</strong> for next/prev and pagination. If you use just one effect name it\'ll be applied to both or you can state two effect names. The first name will be for next/prev and the second will be for pagination. Must be separated by a comma.', 'type' => 'text' ),
		array( 'name' => 'width', 'value' => '960', 'text' => 'Image Width', 'descr' => '<em><small>Please note that with changing these values, you may need to re-upload/regenerate slider images.</small></em>', 'type' => 'text' ),
		array( 'name' => 'height', 'value' => '500', 'text' => 'Image Height', 'descr' => '<em><small>Please note that with changing these values, you may need to re-upload/regenerate slider images.</small></em>', 'type' => 'text' ),
		array( 'name' => 'generateNextPrev', 'value' => 'true', 'text' => 'Show Previous/Next', 'descr' => 'Show the Previous and Next Slider Controls', 'type' => 'checkbox' ),
	);
	return $args;
}

function get_slider_settings()
{
	return get_option('content_slider');
}

function setup_slider_settings()
{
	/** I would have used
	 * json_encode here,
	 * however, the variable names aren't
	 * strings as json_encode would output
	 * string:string.
	 */
	$js_settings = array();
	$settings = get_slider_settings();
	//get all settings to find ignore keys..
	$setting_vars = _slider_setting_vars();
	if (!$settings){
		$settings = array();
	}

	foreach($settings as $key => $s)
	{
		$ignore = false;
		foreach($setting_vars as $k => $sv)
		{
			if($key == $sv['name'] && isset( $sv['ignore'] ))
				$ignore = true;
		}
		if(isset($s) && $s != '' && $ignore === false)
		{
			$val = intval($s);
			if(isset($s)&&($val == 0))
				$s = '"'.$s.'"';
			elseif($key == 'pagination' && $s == 1)
				$s = '{active: true}';

			$js_settings[] = $key.': '.$s;
		}
	}
	return implode(', ', $js_settings);
}

function display_slider($args = null, $_slider_class = 'slider')
{
	global $slider_class;
	$slider_class = $_slider_class;
	$defaults = array(
		'orderby' => 'menu_order',
		'order' => 'ASC',
		'post_status' => 'publish',
		'posts_per_page' => - 1,
	);
	$args = wp_parse_args($args, $defaults);
	$args['post_type'] = 'slider';
	query_posts($args);

	//load template...
	include_once(slider_view($slider_class));

	wp_reset_query();
}

function slider_view($class = '')
{
	/**
	 * If this was an actual template, we would call this function from
	 * add_filter('template_include', array('slider_view'));
	 */
	$templates = array( 'views/slider-'.$class.'.php', 'views/slider.php' );
	$file = locate_template($templates);
	if($file == '')
		$file = 'views/slider.php';
	return $file;
}

function _slider_custom_scripts()
{
	wp_enqueue_script('jquery-slides', plugins_url('/js/slides.min.jquery.js', __FILE__ ), array( 'jquery' ));
	wp_enqueue_style('slider', plugins_url('/css/slider.css', __FILE__ ));
}
add_action('init', '_slider_custom_scripts');