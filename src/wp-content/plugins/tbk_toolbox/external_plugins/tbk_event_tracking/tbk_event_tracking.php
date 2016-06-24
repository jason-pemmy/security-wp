<?php
/*
Plugin Name: Tbk's Event Tracking App
Plugin URI: http://www.www.tbkcreative.com
Description: Google Event Tracking App - Requires Google Anlytics Tracking Code
Author: P.Maclean
Version: 1.0
Author URI: http://www.tbkcreative.com


/*  Copyright 2010  Paul MacLean  (email : paul@tbkcreative.com)
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
?>
<?php
//Set up settings page

add_action('admin_menu', 'tbk_events_tracking_menu');

function tbk_events_tracking_menu() {
	add_options_page('Tbk Events Tracking', 'Tbk Events Tracking', 'manage_options', 'tbk-events-tracking', 'tbk_events_tracking_settings_page');
}

function tbk_events_tracking_settings_page() {

	if (isset($_POST["submit"])) {
	delete_option('tbk_et_tracking_options');
	update_option('tbk_et_tracking_options', serialize($_POST['tbk_et_form']));

	}
	if (!current_user_can('manage_options')) {
	wp_die(__('You do not have sufficient permissions to access this page.'));
	}
	?>
	<style type ="text/css">
		.tracking_info label{ width:90px;display:block;float: left;margin-top: 3px; }
	.tracking_info input[type=text]{ width:250px;}
		.tracking_info{margin-top:15px;}
	.tracking_info_blank{display:none}
	</style>
	<script>
		jQuery(document).ready(function($){
		$('#add_tracking_code').click(function(){
			$('.tracking_info_blank:first').clone().appendTo('#tracking_info_wrap').fadeIn();
		})
		})
	</script>
	<div class="wrap">
	<h2>Tbk Events Tracking</h2>
		<button id ="add_tracking_code">Add a new GA tracker</button>
		<div class ="tracking_info_blank">
		<?php add_tracking_info(); ?>
	</div>
		<form method="post" action="">
		<div id ="tracking_info_wrap">
		<?php
		//delete_option('tbk_et_tracking_options');
		$tracking_options = unserialize(get_option('tbk_et_tracking_options'));
		$num_opts = count($tracking_options['tracking_selector']);
		if($num_opts){
			for ($i = 0; $i < $num_opts; $i++) {
			add_tracking_info($i, $tracking_options);
			}
		}else{
			add_tracking_info();
		}

		?>
		</div>
		<?php submit_button(); ?>
		</form>

	</div>
	<?php
}

function add_tracking_info($i=false, $tracking_options=false) {
	?>
	<div class ="tracking_info">
		<label>Selector:</label>
		<input type ="text" name ="tbk_et_form[tracking_selector][<?php echo $i ?>]" value ="<?php echo $tracking_options['tracking_selector'][$i] ?>"/><br/>
		<label>Category: </label>
		<input type ="text" name="tbk_et_form[tracking_category][<?php echo $i ?>]" value ="<?php echo $tracking_options['tracking_category'][$i] ?>"/><br/>
		<label>Action: </label>
		<input type ="text" name ="tbk_et_form[tracking_action][<?php echo $i ?>]" value ="<?php echo $tracking_options['tracking_action'][$i] ?>" /><br/>
		<label>Label: </label>
		<input type ="text" name ="tbk_et_form[tracking_label][<?php echo $i ?>]"  value ="<?php echo $tracking_options['tracking_label'][$i] ?>"/>
	</div>
	<?php
}
wp_enqueue_script('jquery');
add_action('wp_footer', 'tbk_et_wp_footer');

function tbk_et_wp_footer() {
	$tracking_options = unserialize(get_option('tbk_et_tracking_options'));
	$num_opts = count($tracking_options['tracking_selector']);

	if ($num_opts) {
	?>
	<script>
		jQuery(document).ready(function($){
	<?php for ($i = 0; $i < $num_opts; $i++) { ?>
			$('body').on('mousedown',"<?php echo $tracking_options['tracking_selector'][$i] ?>",function(e){
				_gaq.push(['_trackEvent', "<?php echo $tracking_options['tracking_category'][$i] ?>","<?php echo $tracking_options['tracking_action'][$i] ?>", "<?php echo $tracking_options['tracking_label'][$i] ?>"]);
			});
			$('body').on('keydown',"<?php echo $tracking_options['tracking_selector'][$i] ?>",function(e){
				if(e.which == 13 || e.which == 32){
					_gaq.push(['_trackEvent', "<?php echo $tracking_options['tracking_category'][$i] ?>","<?php echo $tracking_options['tracking_action'][$i] ?>", "<?php echo $tracking_options['tracking_label'][$i] ?>"]);
				}
			});
	<?php } ?>
		});
	</script>
	<?php
	}
}