<?php
/*
Description: Adds extra gravity form assets to enhance the plugin (validation and columns css)
Author: Paul MacLean / Trevor Mills
Usage:
Note: You need to include gravity forms as a seperate plugin. Support Key: 87e10e323c3ded19bcf49ce8094b5b0a

*AVAILABLE CLASSES*
To style a form like TBK likes to style forms (no actual labels and icons within the text fields), you should give your form a class of *no-labels*.

To layout a form with the inputs packed nicely, you should take advantage of the built in Gravity classes such as *gf_left_half*, *gf_right_half*, *gf_left_third*, *gf_middle_third*, *gf_right_third*, *gf_left_two_thirds*, and *gf_right_two_thirds*

If you want the Submit button aligned right, add the class *submit-on-right* to your form.

*Custom Validation*
1. Use the jquery function $('#your-form-id-here').tbkcp_form_validation();
2. Remove the required checkboxes from the gforms editor. Add the class .required to each required item (in the advanced section in the form editor) usage example
   var args ={
	show_star:false, //Show the required star
	msg_box_selector: '.gform_footer' //Where the validation text will be places
        msg_text : '**There were problems submitting the form, please enter required fields**' //Add what the validation says
    }
    $('#estimates_form').tbkcp_gforms_validation(args); //Selector is the id of the form wrapper
*/

define('TBKCP_GFORMS_PRE', 'tbkcp_gforms_');

class tbkcp_gravity_forms {
    /* Function: __construct
      Adds the enqueue script actions
     */

    function __construct() {
		$this->location = '';
		$this->slug = 'gravityforms';
		$this->support_key = '87e10e323c3ded19bcf49ce8094b5b0a';
		add_action('wp_enqueue_scripts', array(&$this, 'load_gform_assets'));
		add_action('activate_gravityforms/gravityforms.php',array(&$this,'plugin_activation'));
		add_filter('puc_get_plugin_info-gravityforms',array(&$this,'plugin_update_checker'));

		/* Add a custom field to the field editor (See editor screenshot) */
		add_action('gform_field_standard_settings', array(&$this,'standard_settings'), 10, 2);
		/* Now we execute some javascript technicalitites for the field to load correctly */
		add_action('gform_editor_js', array(&$this,'gform_editor_js'));
		/* We use jQuery to read the placeholder value and inject it to its field */
		add_action('gform_enqueue_scripts',array(&$this,'gform_enqueue_scripts'), 10, 2);

		add_filter('gform_tabindex', create_function('', 'return null;'));

    }

    /* Function: load_gform_assets
      loads the extra js and css
     */

    function load_gform_assets() {
		wp_register_style('gforms_extra', TBKCP_PLUGIN_URL .'assets/css/gforms_extra.css');
		wp_enqueue_style('gforms_extra');
		wp_enqueue_script('tbkcp_gform_script', TBKCP_PLUGIN_URL .'assets/js/gforms.js', array('jquery'));
		if(! wp_script_is( 'gform_placeholder', 'enqueued')){
			wp_register_script('jquery.placeholder', TBKCP_PLUGIN_URL .'assets/js/jquery.placeholder.min.js',array('jquery'));
			wp_enqueue_script('jquery.placeholder');
		}
    }

	function plugin_update_checker($request){
		// This code was hacked together by looking through the Gravity Forms code base
		// It is the code necessary to allow the toolbox to automatically download and
		// activate Gravity Forms, based on the support_key we have
		$url = 'http://www.gravityhelp.com/wp-content/plugins/gravitymanager';
		$options = array('method' => 'POST', 'timeout' => 20);
		$options['headers'] = array(
			'Content-Type' => 'application/x-www-form-urlencoded; charset=' . get_option('blog_charset'),
			'User-Agent' => 'WordPress/' . get_bloginfo("version"),
			'Referer' => get_bloginfo("url")
		);
		global $wpdb;
		$remote_request_parms = sprintf("of=GravityForms&key=%s&v=%s&wp=%s&php=%s&mysql=%s", urlencode(md5($this->support_key)), urlencode('1.6.11'), urlencode(get_bloginfo("version")), urlencode(phpversion()), urlencode($wpdb->db_version()));
		$request_url = $url . "/version.php?" . $remote_request_parms;
		$raw_response = wp_remote_request($request_url, $options);
		if ( !is_wp_error( $raw_response ) && 200 == $raw_response['response']['code']){
			list($is_valid_key, $version, $url, $exp_time) = array_pad(explode("||", $raw_response['body']), 4, false);
			$info = array("is_valid_key" => $is_valid_key, "version" => $version, "url" => $url);
			if($exp_time)
				$info["expiration_time"] = $exp_time;

			$spoof_info = (object)array(
				'version' => $info['version'],
				'name' => 'Gravity Forms',
				'slug' => $this->slug,
				'download_url' => $info['url']
			);
			$raw_response['body'] = json_encode($spoof_info);
		}
		return $raw_response;
	}

	function plugin_activation(){
		// This automatically installs the support key, however, you may still need to go to the
		// Gravity Forms settings page and hit update settings, just to get GForms to really
		// recognize the key.
		update_option('rg_gforms_key', md5($this->support_key));
	}

	function standard_settings($position, $form_id){

		// Create settings on position 25 (right after Field Label)

		if($position == 25){
			?>

			<li class="admin_label_setting field_setting" style="display: list-item; ">
				<label for="field_placeholder">Placeholder Text

				<!-- Tooltip to help users understand what this field does -->
				<a href="javascript:void(0);" class="tooltip tooltip_form_field_placeholder" tooltip="&lt;h6&gt;Placeholder&lt;/h6&gt;Enter the placeholder/default text for this field.">(?)</a>

			</label>

			<input type="text" id="field_placeholder" class="fieldwidth-3" size="35" onkeyup="SetFieldProperty('placeholder', this.value);">

				</li>
			<?php
		}
	}

	function gform_editor_js(){
		?>
		<script>
			//binding to the load field settings event to initialize the checkbox
		jQuery(document).bind("gform_load_field_settings", function(event, field, form){
			jQuery("#field_placeholder").val(field["placeholder"]);
		});
		</script>

		<?php
	}

	function gform_enqueue_scripts($form, $is_ajax = false){
		static $done;
		if (!isset($done)){ $done = array(); }
		if (isset($done[$form['id']])) return;

		$done[$form['id']] = true;
		ob_start();
		?>
		var doIt = function(){
			$('#gform_<?php echo $form['id']; ?>').find('input[type="text"],select,textarea,.chzn-container.chzn-container-single,.chzn-drop').each(function(){
				// resize every input to fill the entire space of the parent container
				// leaving enough room for the padding
				$(this).width($(this).parents('li').width() - parseInt($(this).css('padding-left')) - parseInt($(this).css('padding-right')));
			});
				//*/ ?>
		<?php foreach($form['fields'] as $i => $field) : ?>
			<?php if(isset($field['placeholder']) && !empty($field['placeholder'])) : ?>
			$('#input_<?php echo $form['id']?>_<?php echo $field['id']?>').attr('placeholder','<?php echo esc_js($field['placeholder'])?>').placeholder();
			<?php endif; ?>
		<?php endforeach; ?>
		}
		doIt(); // Do it now
		$('#gform_ajax_frame_<?php echo $form['id']; ?>').on('load',doIt); // Do it on AJAX return

		<?php
		$the_script = ob_get_clean();
		$this->gform_script($the_script);
		add_action('wp_footer',array(&$this,'gform_script'));
	}

	function gform_script($script=null){
		static $the_script;
		if (!empty($script)){
			if (!isset($the_script)){
				$the_script = '';
			}
			$the_script .= $script;
		}
		else{
			?>
<script type="text/javascript">
jQuery(function($){
<?php echo $the_script; ?>
});
</script>
		<?php
		}
	}
}
?>