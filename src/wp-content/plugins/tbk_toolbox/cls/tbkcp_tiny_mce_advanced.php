<?php
/*
Description: Includes the tiny_mce_advnaced plugin and form options for setting some editor classes
Author: Paul MacLean
Usage: 
1. Click the *settings* link after enabling to see the form options
2. Also see the tinymce advanced settings to place in the classes dropdown (drag and drop it in)
*/

define('TBKCP_TINYMCE_PRE', 'tbkcp_tinymce_pre_');

class tbkcp_tiny_mce_advanced {
    /* Function: __construct
       Includes the plugins setup file and adds the fitler for the classes dropdown
     */
    function __construct() {
		$this->location = 'http://wordpress.org/extend/plugins/tinymce-advanced/';

	if (get_option(TBKCP_TINYMCE_PRE . "classes")) {
	    add_filter('tiny_mce_before_init', array(&$this, 'add_default_classes'));
	}
    }
     /* Function: add_default_classes
        Adds in the admin set classes into the editor dropdown
     */
    function add_default_classes($init_array) {
	$init_array['theme_advanced_styles'] = get_option(TBKCP_TINYMCE_PRE . "classes"); // filter styles
	return $init_array;
    }
     /* Function: form_options
        Takes a comma seperated list of classes and adds them tp the wp options
     */
    function form_options() {
	if (isset($_POST[TBKCP_TINYMCE_PRE . "update"])) {

	    //Update the dimensions
	    if (isset($_POST[TBKCP_TINYMCE_PRE . "classes"])) {
		update_option(TBKCP_TINYMCE_PRE . 'classes', $_POST[TBKCP_TINYMCE_PRE . 'classes']);
	    }
	}
	?>

	<a href ="http://wordpress.org/extend/plugins/tinymce-advanced/faq/" target ="_blank">Faq's</a><br/>
	<a href ="<?php bloginfo('url') ?>/wp-admin/options-general.php?page=tinymce-advanced">Plugin Settings</a>
	<div id ="orbit_slider_options" style ="width:600px;position:relative;">
	    <h3>TINYMCE Advanced custom classes</h3>
	    <div style ="">Classes: Enter in a comma separated list (ex: 'h1=h1,.header_title=.header_title') <input style ="width:400px;margin-bottom: 5px;" type ="text" name ="<?php echo TBKCP_TINYMCE_PRE . 'classes' ?>" value ="<?php echo get_option(TBKCP_TINYMCE_PRE . 'classes') ?>" /></div>

	    <em>If using a custom classes please drag and drop the styles box in the  <a href ="<?php bloginfo('url') ?>/wp-admin/options-general.php?page=tinymce-advanced">Plugin Settings</a></em><br/>
	    <input name ="<?php echo TBKCP_TINYMCE_PRE . 'update' ?>" type ="submit" class ="button-primary" value ="Update Custom Classes"/>
	</div>
	<?php
    }

}
?>
