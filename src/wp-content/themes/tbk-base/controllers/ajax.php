<?php
$ws_ajax = new WS_Ajax();

function ajax_url($action = '') {
	$url = admin_url('/admin-ajax.php');
	$url .= $action != ''?'?action='.$action:'';
	return $url;
}

class WS_Ajax {

	function __construct() {
		add_action('wp_head', array( &$this, 'ajax_js' ));
		if( ! empty($_REQUEST['action'])) {
			add_action( 'wp_ajax_' . $_REQUEST['action'], array( &$this, 'ajax_call' ) );
			add_action( 'wp_ajax_nopriv_' . $_REQUEST['action'], array( &$this, 'ajax_call' ) );
		}
	}

	function ajax_js(){
		echo '<script type="text/javascript">var ajaxurl = "'.ajax_url().'";</script>';
	}

	public static function ajax_call() {
		$class = get_class();
		if(method_exists($class, $_REQUEST['action'])){
			$class::$_REQUEST['action']();
			die();
		}
	}

	function portfolio_share(){
		echo do_shortcode('[social-share title="<span>Share: </span>" img="'.$_REQUEST['img'].'" post_id="'.$_REQUEST['id'].'" thumb="'.$_REQUEST['thumb'].'"]');
	}

	/* this was created mainly for the eguides */
	function load_more_posts() {
		global $_args;
		$_args = null;
		$defaults = array(
			'post_type' => 'eguide',
			'post_status' => 'publish',
			'orderby' => 'menu_order',
			'order' => 'ASC',
			'posts_per_page' => 2,
		);
		$args = array(
			'post_type' => isset($_REQUEST['post_type'])?$_REQUEST['post_type']:null,
			'posts_per_page' => isset($_REQUEST['num_posts'])?$_REQUEST['num_posts']:null,
			'paged' => $_REQUEST['page_number'],
		);
		$_args = wp_parse_args( $args, $defaults );
		get_template_part( 'views/shortcodes/eguides-ajax' );
	}
}
