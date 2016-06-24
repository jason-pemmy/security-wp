<?php
/*
Description: Add a CASL message to the footer of a Gravity Form
Version:     1.0.0
Author:      Jonelle Carroll-Berube | tbk Creative
*/
$gform_casl = new GForm_CASL();

class GForm_CASL {
	function __construct() {

		add_filter( 'gform_add_field_buttons', array( &$this, 'add_casl_field' ));
		add_filter( 'gform_field_type_title' , array( &$this, 'casl_title' ));
		add_action( 'gform_editor_js', array( &$this, 'gform_editor_js' ));
		add_action( 'gform_field_advanced_settings' , array( &$this, 'casl_settings' ), 10, 2 );
		add_action( 'gform_enqueue_scripts' , array( &$this, 'gform_enqueue_scripts' ), 10 , 2 );
		add_action( 'gform_field_css_class', array( &$this, 'custom_class' ), 10, 3);
		add_action( 'gform_field_input' , array( &$this, 'casl_field_input' ), 10, 5 );
		add_filter( 'gform_submit_button', array( &$this, 'display_casl' ), 99, 2 );
		add_filter('gform_field_content', array( &$this, 'subsection_field' ), 10, 5);

	}

	function add_casl_field( $field_groups ) {
		foreach( $field_groups as &$group ){
			if( $group['name'] == 'advanced_fields' ){
				$group['fields'][] = array(
					'class'=>'button',
					'value' => __('CASL', 'gravityforms'),
					'onclick' => 'StartAddField(\'casl\');',
				);
				break;
			}
		}
		return $field_groups;
	}

	function casl_title($type) {
		if ( $type == 'casl' ) {
			return __( 'CASL', 'gravityforms' );
		}
	}

	function casl_field_input($input, $field, $value, $lead_id, $form_id){
		if ( $field['type'] == 'casl' ) {
			//we aren't going to output anything!
			return '';
		}
		return $input;
	}

	function gform_editor_js(){
		?>
		<script type='text/javascript'>
			jQuery(document).ready(function($) {
				fieldSettings['casl'] = '.label_setting, .description_setting, .admin_label_setting, .size_setting, .default_value_textarea_setting, .error_message_setting, .css_class_setting, .visibility_setting, .casl_setting'; //this will show all the fields of the Paragraph Text field minus a couple that I didn't want to appear.
				//binding to the load field settings event
				$(document).bind('gform_load_field_settings', function(event, field, form){
					jQuery('#field_casl').attr('checked', field['field_casl'] == true);
					$('#field_casl_value').val(field['casl']);
				});
			});
		</script>
	<?php
	}

	function casl_settings( $position, $form_id ){
		// Create settings on position 50 (right after Field Label)
		if( $position == 50 ){
			?>
			<li class="casl_setting field_setting">
				<input type="checkbox" id="field_casl" onclick="SetFieldProperty('field_casl', this.checked);" />
				<label for="field_casl" class="inline">
					<?php _e('Disable Submit Button', "gravityforms"); ?>
					<?php gform_tooltip('form_field_casl'); ?>
				</label>
			</li>
		<?php
		}
	}

	function gform_enqueue_scripts( $form, $ajax ) {
		// cycle through fields to see if casl is being used
		foreach ( $form['fields'] as $field ) {
			if ( ( $field['type'] == 'casl' ) && ( isset( $field['field_casl'] ) ) ) {
				$url = plugins_url( 'gform_casl.js' , __FILE__ );
				wp_enqueue_script( 'gform_casl_script', $url , array( 'jquery' ), '1.0' );
				break;
			}
		}
	}

	function custom_class($classes, $field, $form){
		if( $field['type'] == 'casl' && ! is_admin() ){
			$classes .= ' hidden';
		}
		return $classes;
	}

	function display_casl( $button, $form ) {
		$casl = null;
		//find CASL message
		foreach($form['fields'] as $f) {
			if (rgar($f, 'type' ) == 'casl') {
				$casl = '<div class="casl-message">' . wpautop($f['description']) . '</div>';
			}
		}
		return $button.$casl;
	}

	function subsection_field($content, $field, $value, $lead_id, $form_id){
		if ( ! is_admin()) {
			if ( rgar( $field, 'type' ) == 'casl' ) {
				$content = null;
			}
		}
		return $content;
	}
}