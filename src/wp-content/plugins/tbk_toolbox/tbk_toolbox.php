<?php

/*
Plugin Name: tbk Toolbox
Description: The universal plugin for all client installs.
Version: 1.2.34
Plugin URI: http://tbkcreative.com
Author URI: http://tbkcreative.com
Author: Paul MacLean
 */

/* USAGE
 * For new functionality, write a new class file and stick it in the cls folder. See the tbkcp_favicon.php for a good template to follow
 * Every new class file name will populate the tbk Client settings in the admin with a checkbox whether to module or not
 * If the class has a form_options function, the contents of form options will display in the settings portion in the admin
 * If the class has a get_configuration_errors function, that can be used to show admin errors for the class
 */

define('TBKCP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TBKCP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TBKCP_GLOBAL_PRE', 'tbkcp_');
define('TBKCP_SETTINGS_URL', get_admin_url() . 'admin.php?page=tbkcp-settings');

//Global variable to store the included modules
$included_modules = array(); // Old way of doing things - deprecated

//Global variables for the update check file , see tbk_client_update.php
$this_file = __FILE__;

include_once TBKCP_PLUGIN_DIR . 'assets/lib/class.PluginUpdateChecker.php';

class tbk_toolbox {

	protected $user_set_modules = array();
	protected $enabled_modules = array();
	protected $registered_modules = array();
	protected $include_descriptions = false;
	static $redirects = array();

	function __construct() {
		$this->include_descriptions = (is_admin() and ($_GET['page'] == 'tbkcp-settings' or $_POST['action'] == 'tbk_toolbox_ajax'));
		$this->register_modules();
		$this->instantiate_enabled_modules();

		add_action('admin_menu', array(&$this, 'tbkcp_admin'));
		add_action('wp_enqueue_scripts', array(&$this, 'tbkcp_load_assets'));
		add_action('admin_enqueue_scripts', array(&$this, 'tbkcp_admin_load_assets'));

		add_action('wp_ajax_tbk_toolbox_ajax',array(&$this,'ajax'));
		add_filter('query_vars',array(&$this,'query_vars'));
		add_action( 'pre_get_posts', array(&$this,'do_redirect') );

		wp_register_style('tbkcp-toolbox-style', TBKCP_PLUGIN_URL.'assets/css/tbk_toolbox.css', false, '1.0.0');
	}

	function register_modules(){
		$potential_modules = glob(TBKCP_PLUGIN_DIR . "/cls/*.php");

		foreach ($potential_modules as $module_file){
			$key = $this->remove_extension(basename($module_file));
			$title = str_replace('tbkcp', '', $key);
			$title = str_replace('_', ' ', $title);
			$title = ucwords($title);

			$this->registered_modules[$key] = (object)array(
				'title' => $title,
				'file' => $module_file,
				'description' => ($this->include_descriptions ? $this->tokenize_comments($module_file) : '') // Only do the work to get the description when we're going to use it
			);
		}

		add_action('tbkcp_register_modules',array(&$this, 'manually_declared_modules'));
		do_action_ref_array('tbkcp_register_modules',array(&$this)); // Allow other plugins/themes to register modules

		ksort($this->registered_modules);
	}

	function manually_declared_modules(){
		$this->registered_modules['tbkcp_mobile_quick_links'] = (object)array(
			'title' => 'Mobile Quick Links',
			'location' => 'http://plugins.tbkcreative.com/extend/plugins/tbk_mobile_quick_links/update',
			'slug' => 'tbk_mobile_quick_links',
			'settings_page' => admin_url('admin.php?page=tbkql-settings'),
			'description' => $this->include_descriptions ? $this->tokenize_comments('
/*
Description: Deliver a different landing page to mobile visitors
Author: Trevor Mills
Usage:
1. Fill in details on the Mobile Quick Links settings page
2. Customize the style
*/
			') : ''
		);

		$this->registered_modules['tbkcp_shortcode_ui'] = (object)array(
			'title' => 'Shortcode UI',
			'location' => 'http://wordpress.org/extend/plugins/shortcodes-ui/',
						'description' => $this->include_descriptions ? $this->tokenize_comments('
/*
Description: Create a User Friendlier UI for putting shortcodes into pages
Author: Trevor Mills, Original Plugin by Bainternet
Usage:
*/
			') : ''
		);

	}

	function tbkcp_admin() {
		add_menu_page('tbk Client Settings', 'tbk Toolbox', 'manage_options', 'tbkcp-settings', array(&$this, 'tbkcp_settings'), TBKCP_PLUGIN_URL.'assets/css/images/icon.png');
	}

	function tbkcp_load_assets() {
		//Load jquery once here rather then in the individual class files
		wp_enqueue_script('jquery');
	}

	function tbkcp_admin_load_assets($hook) {
		if ('toplevel_page_tbkcp-settings' != $hook)
			return;
		wp_enqueue_style('tbkcp-toolbox-style');
		add_thickbox();
	}

	function instantiate_enabled_modules() {
		global $included_modules;
		$included_modules = $this->enabled_modules = array(); // reset

		//Get the list of modules that the user has turned on
		$this->user_set_modules = get_option(TBKCP_GLOBAL_PRE . 'user_set_modules');
		//Instantiate classes set in the admin options
		if ($this->user_set_modules) {
			foreach ($this->user_set_modules as $key => $value) {

				$module = $this->try_instantiating($key);
				if ($module){
					//Add in the object ref to a global array for future use if needed
					$included_modules[$key] = $this->enabled_modules[$key] = $module;
					// perhaps save settings
					tbk_toolbox::maybe_save_settings($key);
				}
			}
		}
	}

	function tbkcp_settings() {
		// Form Handling

		wp_enqueue_script('tbk-toolbox-admin',TBKCP_PLUGIN_URL.'assets/js/tbk-toolbox-admin.js','jquery',false,true);
		wp_enqueue_script('jquery-ui-sortable',false,array(),false,true);

		require_once 'views/toolbox-grid.php';
	}

	public static function tokenize_comments($source_file) {
		if (file_exists($source_file)){
			$source = file_get_contents($source_file);
		}
		else{
			$source = $source_file; // allow it to pass in straight comments
		}

		$comments_pattern = '|/\*(.*)\*/|Us';
		$headers = 'description|author|usage|see|function';

		$return = '';
		if (preg_match_all($comments_pattern,$source,$matches,PREG_SET_ORDER)){
			foreach ($matches as $match){
				$lines = explode("\n",$match[1]);
				foreach ($lines as $line){
					if (false and trim($line) == ''){
						continue;
					}
					if (preg_match('/^\s*('.$headers.'):\s*(.*)$/i',$line,$line_matches)){
						if ($return != ''){
							$return.= '</pre>'."\n";
						}
						$return.= '<strong>'.strtoupper($line_matches[1]).'</strong>'."\n".'<pre style="margin:0 0 1em 0;white-space:pre-wrap">';
						$line = $line_matches[2];
					}
					$line = htmlentities($line)."\n";
					$line = preg_replace('/\*([^\*]+)\*/','<strong>$1</strong>',$line);
					$return.= $line;

				}
			}
			if ($return != ''){
				$return.= '</pre>'."\n";
			}
		}

		if ($return == ''){
			$return = 'No Description Available.';
		}
		return "<div style='margin-top:1em'>$return</div>";

	}

	public function get_module($key){
		if (array_key_exists($key,$this->enabled_modules)){
			return $this->enabled_modules[$key];
		}
		return false;
	}

	public function module_enabled($key){
		return array_key_exists($key,$this->enabled_modules);
	}

	public function try_instantiating($key){
		if (class_exists($key)){
			return new $key();
		}
		else{
			if (!file_exists(dirname(__FILE__)."/cls/$key.php")){
				// Maybe it's a special one
				if (array_key_exists($key,$this->registered_modules)){
					return (object)$this->registered_modules[$key];
				}
				return false;
			}
			include_once("cls/$key.php");

			$class_identifier = $key . '_class';
			$class_name = $key;
			if (!class_exists($class_identifier)) {
				if ($class_name) {
					return new $class_name( );
				}
			}
		}
		return false;
	}

	/*
	Function: remove_extension
	Helper to remove extension from a file name
	 */

	function remove_extension($filename) {
		$file = substr($filename, 0, strrpos($filename, '.'));
		return $file;
	}

	function ajax(){
		extract($_POST);

		if (!is_array($this->user_set_modules)){
			$this->user_set_modules = array();
		}

		if (array_key_exists($module,$this->registered_modules)){
			switch($subaction){
			case 'enable':
				$this->user_set_modules[$module] = 'on';
				break;
			case 'disable':
				if (array_key_exists($module,$this->user_set_modules)){
					unset($this->user_set_modules[$module]);
				}
				break;
			}
			update_option(TBKCP_GLOBAL_PRE . 'user_set_modules', $this->user_set_modules);

			$this->instantiate_enabled_modules(); // reset
			$call_again = false;
			$success = true;

			// Let's check if this is a plugin that we need to download from somewhere
			ob_start();

			switch(true){
			case ($subaction == 'enable' and $instance = $this->get_module($module) and isset($instance->location)):
				// Yes, indeed it is
				include_once ABSPATH . 'wp-admin/includes/plugin-install.php'; //for plugins_api..
				include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php'; //for Plugin_Upgrader
				include_once TBKCP_PLUGIN_DIR . 'assets/lib/Toolbox_Installer_Skin.php'; // Our own skin

				$slug = (isset($instance->slug) ? $instance->slug : basename(untrailingslashit($instance->location)));
				if (strpos($instance->location,'wordpress.org') !== false){
					// it's a wordpress plugin, handle the download through the WordPress API
					// continue;
				}
				else{
					// An external plugin.  Make use of the PluginUpdateChecker class.
					//include_once TBKCP_PLUGIN_DIR . 'assets/lib/class.PluginUpdateChecker.php';

					// The $intance->location needs to be a link to the Plugin Update JSON file
					// For plugins on plugins.tbkcreative.com (using Self Hosted Plugins),
					// That URL will be http://plugins.tbkcreative.com/extend/plugins/$slug/update
					// The act of instantiating this Plugin installs all of the necessary hooks to do what's
					// needed when plug when plugins_api is called below.
					new PluginUpdateChecker($instance->location, __FILE__,$slug);
				}
				switch(true){
				case !is_dir(ABSPATH.'wp-content/plugins/'.$slug):
					// Need to download the package
					add_filter('install_plugin_complete_actions', create_function('$install_actions, $api, $plugin_file','return array();'),10,3);
					$api = plugins_api('plugin_information', array('slug' => $slug, 'fields' => array('sections' => false) ) ); //Save on a bit of bandwidth.
					$upgrader = new Plugin_Upgrader( new Toolbox_Installer_Skin( compact('title', 'url', 'nonce', 'plugin', 'api') ) );
					if (!empty($api->download_link)){
						$result = $upgrader->install($api->download_link);
						if ($this->handle_result($result,'Plugin Installed','Problem Installing Plugin')){
							$call_again = true;
						}
						else{
							$success = false;
						}
					}
					else{
						echo 'Unable to determine download url.  Installation aborted';
						$success = false;
					}
					break;
				case !is_plugin_active($plugin = $this->get_plugin_file($slug)):
					// Ready to activate it
					$result = activate_plugin($plugin);
					if ($this->handle_result($result,'Plugin Activated','Problem Activating Plugin')){
						$call_again = true;
					}
					else{
						$success = false;
					}
					break;
				default:
					// It's activated already, off we go
					break;
				}
				break;
			case ($subaction == 'disable' and $instance = $this->try_instantiating($module) and isset($instance->location)):
				$slug = isset($instance->slug) ? $instance->slug : basename(untrailingslashit($instance->location));
				$plugin = $this->get_plugin_file($slug);
				if (is_plugin_active($plugin = $this->get_plugin_file($slug))){
					$result = deactivate_plugins($plugin);
					if ($this->handle_result($result,'Plugin Deactivated','Problem Deactivating Plugin')){
						$call_again = 'confirm';
						$this->confirm = 'Delete Plugin Files?';
						$this->confirm_action = $subaction;
					}
					else{
						$success = false;
					}
				}
				elseif(isset($confirmed) and $confirmed === 'true'){
					$result = delete_plugins(array($plugin));
					if ($this->handle_result($result,'Plugin Files Deleted','Problem Deleting Plugin Files')){
						$call_again = true;
					}
					else{
						$success = false;
					}
				}
				else{
					$success = true;
				}
				break;
			}

			$this->message = ob_get_clean();

			$key = $module;
			$module = $this->registered_modules[$module];

			ob_start();
			include('views/toolbox-grid-cell.php');
			$markup = ob_get_clean();


			$return = array(
				'success' => $success,
				'call_again' => $call_again, 	// The next step
				'markup' => $markup
			);
		}
		else{
			$return = array(
				'success' => false,
				'message' => 'Requested module does not exist'
			);
		}

		echo json_encode($return);
		die();
	}

	function get_plugin_file($slug){
		// A little hacky, but we're going to figure out if the provided plugin
		// is active based on just the slug (rather than the slug/filename.php)
		$plugins = get_plugins();
		foreach (array_keys((array)$plugins) as $plugin){
			if (strpos($plugin,$slug.'/') === 0){
				return $plugin;
			}
		}
		return $slug;
	}

	function handle_result($result,$success_string=null,$failure_string=null){
		if ( is_wp_error( $result ) ) {
			$errors = $result->get_error_messages();
			if (isset($failure_string)){
				echo "$failure_string";
			}
			switch ( count( $errors ) ) :
			case 0 :
				echo '';
				break;
			case 1 :
				echo "<p>{$errors[0]}</p>";
				break;
			default :
				echo "<ul>\n\t\t<li>" . join( "</li>\n\t\t<li>", $errors ) . "</li>\n\t</ul>";
				break;
			endswitch;
			return false;
		}
		elseif (!isset($result) or $result){ // null $result means success for the functions in question
			if (isset($success_string)){
				echo "$success_string";
			}
			return true;
		}
		else{
			if (isset($failure_string)){
				echo "$failure_string";
			}
			return false;
		}
	}

	function settings_nonce($module){
		wp_nonce_field("{$module}_settings","{$module}_nonce");
	}

	function get_settings($module,$setting=null){
		$settings = get_option("{$module}_settings",array());
		if (isset($setting)){
			return $settings[$setting];
		}
		return $settings;
	}

	function maybe_save_settings($module){
		if (isset($_POST) and isset($_POST[$module]) and isset($_POST["{$module}_nonce"]) and wp_verify_nonce($_POST["{$module}_nonce"], "{$module}_settings")){
			tbk_toolbox::save_settings($module);
			wp_redirect($_SERVER['REQUEST_URI']); // redirect so hitting refresh doesn't cause it to post again, and so the settings don't get updated multiple times (just in case)
			exit;
		}
	}

	function save_settings($module,$settings=null){
		if (!isset($settings)){
			$settings = stripslashes_deep($_POST[$module]);
		}
		if (isset($_POST['_delete_file'])){
			foreach ((array)$_POST['_delete_file'] as $key => $value){
				$field = self::desanitize_field_name($key);
				if (isset($field['index'])){
					// It's part of a hasMany
					if (isset($settings[$field['key']][$field['index']][$field['subkey']])){
						wp_delete_attachment($settings[$field['key']][$field['index']][$field['subkey']]);
						unset($settings[$field['key']][$field['index']][$field['subkey']]);
					}
				}
				else{
					// it's just a plain old regular file field
					if (isset($settings[$field['key']])){
						wp_delete_attachment($settings[$field['key']]);
						unset($settings[$field['key']]);
					}
				}
			}
		}
		update_option("{$module}_settings",$settings);

		// Handle file uploads
		self::handle_file_uploads($module);
	}

	function handle_file_uploads($module){
		if (isset($_FILES[$module])){
			foreach (array_keys($_FILES[$module]['name']) as $key){
				// Files might come in either as an array (i.e. module_name[settings_key][file_key])
				// Or even as an array of arrays (i.e. module_name[settings_key][row_num][file_key])
				// We need to handle either type, so we'll spoof an array to send to tbk_toolbox::handle_file_upload();
				if (is_array($_FILES[$module]['name'][$key])){
					// Part of a hasMany
					foreach(array_keys($_FILES[$module]['name'][$key]) as $index){
						foreach (array_keys($_FILES[$module]['name'][$key][$index]) as $subkey){
							$file_spoof = array();
							foreach (array_keys($_FILES[$module]) as $spoof_key){
								$file_spoof[$spoof_key] = $_FILES[$module][$spoof_key][$key][$index][$subkey];
							}
							$allowed = $_POST['_allowed_for'][self::sanitize_field_name("{$module}[{$key}][{$index}][$subkey]")];
							if ($attach_id = tbk_toolbox::handle_file_upload($file_spoof,$allowed)){
								$settings = get_option("{$module}_settings",array());
								$settings[$key][$index][$subkey] = $attach_id;
								update_option("{$module}_settings",$settings);
							}
						}
					}
				}
				else{
					$file_spoof = array();
					foreach (array_keys($_FILES[$module]) as $spoof_key){
						$file_spoof[$spoof_key] = $_FILES[$module][$spoof_key][$key];
					}
					$allowed = $_POST['_allowed_for'][self::sanitize_field_name("{$module}[{$key}]")];
					if ($attach_id = tbk_toolbox::handle_file_upload($file_spoof,$allowed)){
						$settings = get_option("{$module}_settings",array());
						$settings[$key] = $attach_id;
						update_option("{$module}_settings",$settings);
					}
				}
			}
		}
	}

	function handle_file_upload($file,$allowed){
		if ($file['size'] > 0){
			// Get the type of the uploaded file. This is returned as "type/extension"
			$arr_file_type = wp_check_filetype(basename($file['name']));
			$uploaded_file_type = $arr_file_type['type'];

			// Set an array containing a list of acceptable formats
			$allowed_file_types = explode(',',$allowed); //stripslashes($_POST['_allowed_for_'.$key]));

			// If the uploaded file is the right format
			if(in_array($uploaded_file_type, $allowed_file_types)) {

				// Options array for the wp_handle_upload function. 'test_upload' => false
				$upload_overrides = array( 'test_form' => false );

				// Handle the upload using WP's wp_handle_upload function. Takes the posted file and an options array
				require_once(ABSPATH.'wp-admin/includes/file.php');
				$uploaded_file = wp_handle_upload($file, $upload_overrides);

				// If the wp_handle_upload call returned a local path for the image
				if(isset($uploaded_file['file'])) {

					// The wp_insert_attachment function needs the literal system path, which was passed back from wp_handle_upload
					$file_name_and_location = $uploaded_file['file'];

					// Set up options array to add this file as an attachment
					$attachment = array(
						'post_mime_type' => $uploaded_file_type,
						'post_title' => $file['name'],
						'post_content' => '',
						'post_status' => 'inherit'
					);

					// Run the wp_insert_attachment function. This adds the file to the media library and generates the thumbnails.
					$attach_id = wp_insert_attachment( $attachment, $file_name_and_location );
					require_once(ABSPATH . "wp-admin" . '/includes/image.php');
					$attach_data = wp_generate_attachment_metadata( $attach_id, $file_name_and_location );
					wp_update_attachment_metadata($attach_id,  $attach_data);

					return $attach_id;
				}
			}
		}
		return false;
	}

	/**
	 * This function allows a quick and easy way to add a settings form
	 * to any toolbox module.
	 *
	 * To use it, you'll need to pass in the name of the module and an array of
	 * settings variables.  Basically, it's an array where the key is the individual
	 * setting key and the value is a definition of what type of setting you want.
	 *
	 * The value can be either a string ('text' or 'textarea' to quickly add that type
	 * of field) or an array.  If it's an array, it can have the following key/value pairs
	 *
	 * 	type 		=> The type of field.  Currently supported are text, textarea, select, radio, checkbox & file
	 *  label 		=> The field label.  If not supplied, we'll use a sanitized version of the setting key
	 * 	options 	=> For select, radio, and checkbox, this must be supplied and must be an array of values.
	 *			   	   The value for each option is its key.  You can use an associative or non-associative array
	 * 	description => An optional description that will be included on the form
	 * 	allowed 	=> For file types, an array of allowed mime types.  defaults to array('image/jpg','image/jpeg','image/gif','image/png')
	 * 	hasMany 	=> If this is specified, then it allows you to create a repeatable set of fields.  This should be
	 *				   set to an array of fields defined as you would define them regularly.  All of the same options apply.
	 *				   You will be able to add as many entries as you want, delete and reorder them.
	 *
	 * TO GRAB A GIVEN SETTING IN YOUR CODE, SIMPLY CALL tbk_toolbox::get_settings($module,$setting_key);
	 * For non-repeated settings, this will give you back the value of the setting (for 'file' types it's the attachment id)
	 * For repeated settings, it will give you back an array of all current settings.
	 *
	 * Here is an example options array:
	 *
	 * 	$options = array(
	 * 		'some_text' => 'text',
	 * 		'lotsa_text' => 'textarea',
	 * 		'radio_choice' => array(
	 * 			'type' => 'radio',
	 *			'label' => 'Choose One',
	 * 			'options' => array(
	 * 				'apples',
	 * 				'oranges',
	 * 				'bananas',
	 * 			)
	 * 		),
	 * 		'checkbox' => array(
	 * 			'type' => 'checkbox',
	 * 			'options' => array(
	 * 				'Figs',
	 * 				'Pears',
	 * 				'Peaches',
	 * 			),
	 * 			'description' => 'Claram anteposuerit litterarum formas humanitatis per: seacula quarta decima et quinta decima eodem modo. Parum typi qui: nunc nobis videntur parum clari?'
	 * 		),
	 * 		'file' => array(
	 * 			'type' => 'file',
	 * 		),
	 * 		'another' => array(
	 * 			'type' => 'file',
	 * 		),
	 * 		'group' => array(
	 * 			'hasMany' => array(
	 * 				'text_field' => 'text',
	 * 				'something' => 'text',
	 * 				'coolio' => array(
	 * 					'type' => 'radio',
	 * 					'options' => array(
	 * 						'apples',
	 * 						'oranges',
	 * 						'bananas',
	 * 					)
	 * 				),
	 * 				'group_file' => array(
	 * 					'type' => 'file',
	 * 				),
	 * 				'whoa' => array(
	 * 					'type' => 'checkbox',
	 * 					'options' => array(
	 * 						'Figs',
	 * 						'Pears',
	 * 						'Peaches',
	 * 					),
	 * 					'description' => 'Claram anteposuerit litterarum formas humanitatis per: seacula quarta decima et quinta decima eodem modo. Parum typi qui: nunc nobis videntur parum clari?'
	 * 				),
	 * 			)
	 * 		),
	 * 	);
	 */
	function options_form($module,$options=array()){

		// A little massaging
		$enctype = false;
		foreach ($options as $key => $option){
			if ($option['type'] == 'file'){
				$enctype = "multipart/form-data";
			}
			$options[$key] = self::sanitize_option($option,$key);
		}
		?>
		<form class="tbk_toolbox-settings-form" action="" method="post" <?php if ($enctype){ echo "enctype=\"$enctype\""; } ?>>
			<?php tbk_toolbox::settings_nonce($module); ?>
			<?php
				foreach ($options as $key => $option){
					$current = self::get_settings($module,$key);
					if (!isset($option['hasMany'])){
						$name = "{$module}[{$key}]".($option['type'] == 'checkbox' ? '[]' : '');
						echo self::settings_field($name,$current,$option);
					}
					else{
						echo '<label>' . $option['label'] . ': </label>';
						echo '<div class="hasmany-wrapper">'."\n";
						$looper = (array)$current;
						$looper['_template'] = array();
						foreach ($looper as $index => $subcurrent){
							echo '<fieldset class="fieldset'.$index.'">'."\n";
							$first = true;
							foreach ($option['hasMany'] as $subkey => $suboption){
								$suboption = self::sanitize_option($suboption,$subkey);
								if ($index === '_template'){
									$name = "_templates[{$module}][{$key}][__row__][{$subkey}]".($suboption['type'] == 'checkbox' ? '[]' : '');
								}
								else{
									$name = "{$module}[{$key}][{$index}][{$subkey}]".($suboption['type'] == 'checkbox' ? '[]' : '');
								}
								echo self::settings_field($name,$current[$index][$subkey],$suboption,($first ? 'first' : 'not-first'));
								$first = false;
							}
							echo '<input type="button" class="button delete-row" value="Delete This '.esc_attr($option['label']).'" />';
							echo '</fieldset>'."\n";
						}
						echo '<input type="button" class="button add-row" id="add-new-'.$key.'" value="Add New '.esc_attr($option['label']).'" />';
						echo '</div>'."\n";
					}
				}
			?>
			<br/>
			<input name ="<?php echo $module . '_update' ?>" type ="submit" class ="button-primary" value ="Update Settings"/>
		</form>
		<div class="clear"></div>


		<?php
	}

	function sanitize_option($option,$key){
		if (!is_array($option)){ // Allow option to simply be 'text' or 'textarea'
			$option = array(
				'type' => $option,
			);
		}
		if (!isset($option['type']) and !isset($option['hasMany'])){
			$option['type'] = 'text';
		}
		if (!isset($option['label'])){
			$option['label'] = self::labelize($key);
		}
		if ($option['type'] == 'file'){
			$enctype = 'multipart/form-data';
			if (!isset($option['allowed'])){
				$image_types = array('image/jpg','image/jpeg','image/gif','image/png');
				$option['allowed'] = $image_types;
			}
		}
		return $option;
	}

	function labelize($string){
		return ucwords(preg_replace('/[^a-zA-Z0-9 ]/',' ',$string));
	}

	function sanitize_field_name($name){
		return preg_replace('/[^a-zA-Z0-9_]/','-',$name);
	}

	function desanitize_field_name($name){
		preg_match('/^([^\-]+)-(.*)-$/',$name,$matches);
		if (preg_match('/^(.*)--([0-9]+)--(.*)$/',$matches[2],$submatches)){
			// It's a repeated setting
			return array(
				'module' => $matches[1],
				'key' => $submatches[1],
				'index' => $submatches[2],
				'subkey' => $submatches[3]
			);
		}
		else{
			return array(
				'module' => $matches[1],
				'key' => $matches[2]
			);
		}
	}

	function settings_field($name,$current,$option,$wrapper_class=''){
		?>
		<div class="field-wrapper <?php echo $wrapper_class; ?>">
		<label><?php echo $option['label']; ?>: </label>
		<?php switch($option['type']) :
			case 'text': ?>
				<input type='text' name="<?php echo $name; ?>" value="<?php echo esc_attr($current); ?>"/>
			<?php break;
			case 'textarea': ?>
				<textarea name="<?php echo $name; ?>"><?php echo $current; ?></textarea>
			<?php break;
			case 'select': ?>
				<select name="<?php echo $name; ?>">
				<?php foreach ((array)$option['options'] as $value => $text) : $key = (is_int($value) and (empty($option['key']) or $option['key'] != 'index')) ? $text : $value; ?>
					<option value="<?php echo esc_attr($key); ?>" <?php selected($current,$key); ?>><?php echo $text; ?></option>
				<?php endforeach; ?>
				</select>
			<?php break;
			case 'radio':
			case 'checkbox': ?>
			<?php $first = true;
				foreach ((array)$option['options'] as $value => $text) :
					$key = (is_int($value) and (empty($option['key']) or $option['key'] != 'index')) ? $text : $value;
					$id = self::sanitize_field_name($name).sanitize_title($value);
					if ($option['type'] == 'radio'){
						$checked = (isset($current) and $current == $key);
					}
					else{
						$checked = in_array($key,(array)$current);
					}
				?>
				<input class="<?php echo $option['type'] . ($first ? ' first' : ''); ?>" value="<?php echo esc_attr($key); ?>" name="<?php echo $name; ?>" type="<?php echo $option['type']; ?>" <?php checked(true,$checked); ?> id="<?php echo $id; ?>"/> <label class="checkbox-label" for="<?php echo $id; ?>"><?php echo $text; ?></label>
			<?php $first = false; endforeach; ?>
			<?php break;
			case 'file':
				$id = self::sanitize_field_name($name);
				if ($current) : ?>
				<a href="<?php echo get_edit_post_link( $current, true ); ?>" title="Edit Attachment">
					<?php echo wp_get_attachment_image($current,array(75,75),false,array('style' => 'float:left;margin-top:5px')); ?>
				</a>
				<input type="checkbox" value="1" name="_delete_file[<?php echo $id; ?>]" class="deleter" id="deleter_<?php echo $id; ?>"/> <label class="checkbox-label" for="deleter_<?php echo $id; ?>">Delete</label>
				<input type="hidden" name="<?php echo $name; ?>" value="<?php echo $current; ?>" />
				<?php else : ?>
				<input type="file" name="<?php echo $name; ?>" />
				<input type="hidden" name="_allowed_for[<?php echo $id; ?>]" value="<?php echo esc_attr(implode(',',$option['allowed'])); ?>"/>
				<?php endif; ?>
			<?php break;
			endswitch; ?>
			<?php if (isset($option['description'])) : ?>
				<p class="description"><?php echo $option['description']; ?></p>
			<?php endif; ?>
		</div> <!-- field-wrapper -->
		<?php
	}

	function add_redirect($pattern,$callback){
		add_filter('option_rewrite_rules',create_function('$rules',"\$rules['$pattern'] = 'index.php?_tbk_toolbox_callback=".count(self::$redirects)."&_tbk_toolbox_match=\$matches[1]'; return \$rules;"));
		self::$redirects[] = $callback; // the index corresponds to the count($this->redirect) included above
	}

	function query_vars($query_vars){
		$query_vars[] = '_tbk_toolbox_callback';
		$query_vars[] = '_tbk_toolbox_match';
		return $query_vars;
	}

	function do_redirect(){
		$callback = get_query_var('_tbk_toolbox_callback');
		if ($callback !== '' and array_key_exists($callback,self::$redirects)){
			$callback = self::$redirects[$callback];
			call_user_func_array( $callback, array(get_query_var('_tbk_toolbox_match')) );
		}
	}

}

//add_action('parse_request','conf_app_parse_request'); // uncomment to check what was matched
function conf_app_parse_request($wp_rewrite){
	print_r($wp_rewrite);
	die();
}


add_action('init','tbk_toolbox_instantiate',1);
function tbk_toolbox_instantiate(){
	global $tbkcp_plugin;
	global $tbk_toolbox;
	$tbkcp_plugin = $tbk_toolbox = new tbk_toolbox();
}

// The version of class.PluginUpdateChecker.php that gets included
// by any plugin hosted by Self Hosted Plugins does not contain a hook
// that we need for the Toolbox.  To get around this, we're going to
// make sure that the toolbox is loaded first.  This little snippet
// found at http://wordpress.org/support/topic/how-to-change-plugins-load-order
// makes that happen.
function this_plugin_first() {
	// ensure path to this file is via main wp plugin path
	$wp_path_to_this_file = preg_replace('/(.*)plugins\/(.*)$/', WP_PLUGIN_DIR."/$2", __FILE__);
	$this_plugin = plugin_basename(trim($wp_path_to_this_file));
	$active_plugins = get_option('active_plugins');
	$this_plugin_key = array_search($this_plugin, $active_plugins);
	if ($this_plugin_key) { // if it's 0 it's the first plugin already, no need to continue
		array_splice($active_plugins, $this_plugin_key, 1);
		array_unshift($active_plugins, $this_plugin);
		update_option('active_plugins', $active_plugins);
	}
}
add_action("activated_plugin", "this_plugin_first");
?><?php //BEGIN::SELF_HOSTED_PLUGIN_MOD
					
	/**********************************************
	* The following was added by Self Hosted Plugin
	* to enable automatic updates
	* See http://wordpress.org/extend/plugins/self-hosted-plugins
	**********************************************/
	require "__plugin-updates/plugin-update-checker.class.php";
	$__UpdateChecker = new PluginUpdateChecker('http://plugins.tbkcreative.com/extend/plugins/tbk_toolbox/update', __FILE__,'tbk_toolbox');			
	
//END::SELF_HOSTED_PLUGIN_MOD ?>