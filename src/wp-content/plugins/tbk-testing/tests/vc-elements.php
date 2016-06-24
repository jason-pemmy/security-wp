<?php
class VC_Elements_Test {
	static $vc_elements = array();
	static $instance = false;

	private function __construct() {
		add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
		add_action('init', array( &$this, 'create_virtual_page' ) );
	}

	public function admin_menu(){
		add_theme_page('VC Elements', 'VC Elements', 'manage_options', 'vc-elements', array( &$this, 'vc_element_view' ) );
	}

	public function instantiate() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return null;
	}

	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public static function register_element_test($params) {
		self::$vc_elements[] = $params;
	}

	function display_elements() {
		foreach(self::$vc_elements as $e) {
			echo '<div>';
			echo '<h4>'.$e['name'].'</h4>';
			echo '<em>Category: '.$e['category'].'</em>';
			echo wpautop($e['description']);
			$atts = array();
			$values = array();
			$content = null;
			if( ! empty($e['params']) && is_array($e['params'])) {
				foreach ( $e['params'] as $param ) {

					$has_dropdown = array_search( 'dropdown', $param );
					$has_image = array_search( 'image', $param );
					$has_images = array_search( 'images', $param );
					$has_link = array_search( 'vc_link', $param);
					$has_content = array_search( 'content', $param);

					if($has_image !== false) {
						$atts[$param['param_name']] = Thermo_Theme::get_post_thumbnail_id();
					}
					if($has_images !== false) {
						$image_id = Thermo_Theme::get_post_thumbnail_id();
						$atts[$param['param_name']] = $image_id.','.$image_id.','.$image_id;
					}
					if($has_content !== false) {
						$content = ! empty($param['value'])?$param['value']:'Lorem ipsum dolor sit amet,
						consectetur adipiscing elit. Duis id nibh quis vitae ornare suscipit.';
					}
					if($has_link !== false) {
						$atts[$param['param_name']] = 'url:'.urlencode(site_url('element-test')).'|title:'.urlencode('Element Test');
					}
					//if there are multiple values for the dropdown, lets loop through and display all options!
					if ( $has_dropdown !== false ) {
						foreach ( $param['value'] as $v ) {
							$values[] = array(
								'heading' => $param['heading'] . ': ' . $v,
								'value' => array(
									$param['param_name'] => $v,
								),
							);
						}
					}
				}
				if( ! empty($values)) {
					foreach($values as $v) {
						echo '<h6 style="margin-top: 20px;">'.$v['heading'].'</h6>';
						echo self::build_shortcode($e['base'], array_merge($atts,
							$v['value']), $content);
					}
				} else {
					echo self::build_shortcode($e['base'], $atts, $content);
				}
			} else {
				echo self::build_shortcode($e['base']);
			}
			echo '<div class="clearfix"></div>';
			echo '</div>';
			echo '<hr/>';
		}
	}

	public static function build_shortcode($tag, $atts = null, $content = null) {
		$output = '';
		if(isset($atts)) {
			if(is_array($atts)) {
				foreach($atts as $k => $v) {
					$output .= ' '.$k.'="'.$v.'"';
				}
			} else {
				$output .= $atts;
			}
		}
		return do_shortcode('['.$tag.$output.']'.$content.'[/'.$tag.']');
	}



	function create_virtual_page() {
		$url = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
		$segments = explode('/', $url);
		if (in_array('all-vc-elements', $segments)) {
			$args = array(
				'slug' => 'all-vc-elements',
				'title' => 'Visual Composer Elements',
				'content' => '',
				'type' => 'page',
			);
			$pg = new DJVirtualPage($args);
		}
	}

	function vc_element_view(){
		?>
			<div class="wrap">
				<h2>View Visual Composer Elements</h2>
				<p class="description">
					If you have created visual composer elements, you can add VC_Elements_Test::register_element_test that
					has all of the vc_map elements.
				</p>
<pre>
vc_map( $vc_map );
if(class_exists('VC_Elements_Test')) {
	VC_Elements_Test::register_element_test($vc_map);
}
</pre>
				<p>
					<a href="<?php echo site_url('all-vc-elements');?>" class="button-secondary" target="_blank">
						View All VC Elements
					</a>
				</p>
			</div>
		<?php
	}
}
VC_Elements_Test::get_instance();