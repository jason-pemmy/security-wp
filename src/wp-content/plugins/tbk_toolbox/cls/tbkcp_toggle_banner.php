<?php
/*
Description: Will add an open/close banner for displaying promotions/ads via a shortcode (adds editing on dashboard widget)
Author: Paul MacLean
Usage: 
1. Add the markup to the dashboard widget (follow widget instructions)
2. After saving , widget will display the shortcode.
*/

define('TBKCP_TOGGLE_BANNER_PRE', 'tbkcp_toggle_banner_');


class tbkcp_toggle_banner {

    function __construct() {
	add_shortcode('tbkcp_toggle_banner', array(&$this, 'toggle_banner'));
	add_action('wp_dashboard_setup', array(&$this, 'add_dashboard_widget'));
    }

    function add_dashboard_widget() {
	wp_add_dashboard_widget('dashboard_widget', 'Toggle Banner', array(&$this, 'dashboard_widget_function'));
    }

    function dashboard_widget_function() {
	?>
	<form enctype="multipart/form-data" style ="width:500px" id ="tbkcp_primary_form" name= "<?php echo TBKCP_TOGGLE_BANNER_PRE . 'form' ?>" method="post" action ="">
	    <?php
	    $this->form_options(false);
	    ?>
	</form>
	<?php
    }

    function toggle_banner() {
	extract(shortcode_atts(array(
		    'banner_id' => 0,
			), $atts));
	$toggle_banners = get_option(TBKCP_TOGGLE_BANNER_PRE . 'toggle_banners');
	?>
	<script>

	    jQuery(function($){
		$('.tbkcp_toggle_banner_button.open').hide();
		$('body').on("click",'.tbkcp_toggle_banner_button', function(event){
		    var $wrap = $(this).closest('.tbkcp_toggle_banner_wrap');
		    var wrap_height = $('.tbkcp_toggle_banner_wrap').height();
		    var slider_data = {}
		    var $close_button;
		    var $open_button;
		    var close_button_css = {}
		    var open_button_css = {}
		    if($(this).hasClass('close')){
			$close_button = $(this);
			$open_button = $wrap.find( '.tbkcp_toggle_banner_button.open' );
			slider_data = {
			    marginTop: -wrap_height
			}
			open_button_css ={
			    'position':'absolute',
			    'margin-top':  '35px',
			    'right':'0px',
			    'display' : 'block'

			}
			close_button_css ={
			    'display' : 'none'

			}

		    }else{
			$open_button = $(this);
			$close_button = $wrap.find( '.tbkcp_toggle_banner_button.close' );
			$(this).fadeOut();
			slider_data = {
			    marginTop: 0
			}
			close_button_css ={
			    'position':'absolute',

			    'right':'0px',
			    'display' : 'block'

			}

		    }

		    $wrap.animate(
		    slider_data
		    ,{
			duration: 'slow',
			easing: 'easeOutBounce',
			specialEasing: {

			    height: 'easeOutBounce'
			},
			complete: function() {
			    $close_button.css(close_button_css);
			    $open_button.css(open_button_css);

			}
		    });

		});

	    })
	</script>

	<?php
	return $script . stripcslashes(htmlspecialchars_decode($toggle_banners[$banner_id]['banner_content']));
    }

    function form_options($show_headers=true) {
	//Form Handling
	if (isset($_POST[TBKCP_TOGGLE_BANNER_PRE . 'update'])) {
	    delete_option(TBKCP_TOGGLE_BANNER_PRE . 'toggle_banners');
	    $toggle_banners = array();
	    $banner_contents = $_POST[TBKCP_TOGGLE_BANNER_PRE . 'content'];
	    $banner_positions = $_POST[TBKCP_TOGGLE_BANNER_PRE . 'position'];
	    $i = 0;
	    foreach ($banner_contents as $banner_content) {
		$toggle_banners[$i] = array(
		    'banner_content' => $banner_content,
		    'banner_position' => $banner_positions[$i]
		);
		$i++;
	    }
	    update_option(TBKCP_TOGGLE_BANNER_PRE . 'toggle_banners', $toggle_banners);

	    if (isset($_POST[TBKCP_TOGGLE_BANNER_PRE . 'num'])) {
		update_option(TBKCP_TOGGLE_BANNER_PRE . 'num', $_POST[TBKCP_TOGGLE_BANNER_PRE . 'num']);
	    }
	}
	$num_banners = get_option(TBKCP_TOGGLE_BANNER_PRE . 'num');
	if (!$num_banners)
	    $num_banners = 1;
	//Dashboard widget does not require the extra settings..for client use
	if ($show_headers) {
	    ?>

	    Number of Banners (enter 2 for top & bottom)<input style ="width:50px" name ="<?php echo TBKCP_TOGGLE_BANNER_PRE . 'num' ?>" value ="<?php echo $num_banners ?>" type ="text"/> <em>If you enter 2, the second set of banner options will not show until you click Update Banner Options</em>

	    <?php
	}
	for ($i = 0; $i < $num_banners; $i++) {


		$settings = array(
		    'textarea_rows' => 10,
		    'textarea_name' => TBKCP_TOGGLE_BANNER_PRE . 'content[]'
		);
		$banners = get_option(TBKCP_TOGGLE_BANNER_PRE . 'toggle_banners');
	  if ($show_headers) {
	      echo '<h3>Banner Settings</h3>';
		?>
		<p>Banner Content<br/></p>
		<p><strong>Shortcode:</strong> [tbkcp_toggle_banner id = "<?php echo $i ?>"]</p>
		<p><em>Give the wrap the class: <strong><?php echo TBKCP_TOGGLE_BANNER_PRE . 'wrap' ?></strong></em></p>
		<p><em>Give the close button the classes: <strong><?php echo TBKCP_TOGGLE_BANNER_PRE ?>button</strong> <strong><?php echo strtolower($banners[$i]['banner_position']) . ' close' ?></strong></em></p>
		<p><em>Give the open button the classes: <strong><?php echo TBKCP_TOGGLE_BANNER_PRE ?>button</strong> <strong><?php echo strtolower($banners[$i]['banner_position']) . ' open' ?></strong></em></p>
		<?php
	    }
	    wp_editor(stripcslashes(htmlspecialchars_decode($banners[$i]['banner_content'])), TBKCP_TOGGLE_BANNER_PRE . $i, $settings);
	    ?>
	    <?php if ($show_headers) { ?>
		<br/>Banner Position: <select name ="<?php echo TBKCP_TOGGLE_BANNER_PRE . 'position[]' ?>">
		    <option <?php echo $banners[$i]['banner_position'] == 'Top' ? 'selected=selected' : '' ?> >Top</option>
		    <option <?php echo $banners[$i]['banner_position'] == 'Bottom' ? 'selected=selected' : '' ?>>Bottom</option>
		</select>


		<br/><hr/><br/>
	    <?php }
	} ?>
	<input name ="<?php echo TBKCP_TOGGLE_BANNER_PRE . 'update' ?>" type ="submit" class ="button-primary" value ="Update Banner Options"/>
	<?php
    }

}
?>
