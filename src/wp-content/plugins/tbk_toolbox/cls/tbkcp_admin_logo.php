<?php
/*
Description: Puts an image in replace of the default wordpress admin login logo. Has admin form settings for uploading and setting dimensions
Author: Paul MacLean
Usage: Click the settings link after enabling to upload an admin logo
*/

define('TBKCP_ADMIN_LOGO_PRE', 'tbkcp_admin_logo_');

class tbkcp_admin_logo {
    /*
      Function: __construct
      Adds the filters to replace the default wp login logo
     */

    function __construct() {
	add_action('login_head', array(&$this, 'custom_login_logo'));
	add_filter('login_headerurl', array(&$this, 'custom_login_url'));
	add_filter('login_headertitle', array(&$this, 'login_header_title'));
    }

    /*
      Function: custom_login_logo
      Sets the logo image and new dimensions
     */

    function custom_login_logo() {
	//Check for custom dimensions
	$admin_logo_width = get_option(TBKCP_ADMIN_LOGO_PRE . 'width');
	$admin_logo_height = get_option(TBKCP_ADMIN_LOGO_PRE . 'height');
	if (!$admin_logo_width) {
	    $admin_logo_width = '274';
	}
	if (!$admin_logo_height) {
	    $admin_logo_height = '63';
	}
	?>
	<style type="text/css">
	    h1 a {
		background-image: url( <?php echo get_option(TBKCP_ADMIN_LOGO_PRE . 'url') ?> )  !important;
		background-size:   <?php echo $admin_logo_width . 'px ' . $admin_logo_height . 'px' ?> !important;

	    }
	</style>
	<?php
    }

    /*
      Function: custom_login_url
      Sets the logo url to the front end link
     */

    function custom_login_url() {
	return site_url();
    }

    /*
      Function: login_header_title
      Sets the title of the login to the blog name
     */

    function login_header_title() {
	return get_bloginfo('name');
    }

    /*
      Function: form_options
      The form handler and markup for the admin settings (file upload and dimensions)
     */

    function form_options() {
	// Form Handling
	if (isset($_POST[TBKCP_ADMIN_LOGO_PRE . "update"])) {

	    //Upload the image the wordpress way
	    if (isset($_FILES[TBKCP_ADMIN_LOGO_PRE . "file"])) {
		$uploaded_logo_file = wp_handle_upload($_FILES[TBKCP_ADMIN_LOGO_PRE . "file"], array('test_form' => false));

		if (!$uploaded_logo_file['error']) {
		    update_option(TBKCP_ADMIN_LOGO_PRE . 'url', $uploaded_logo_file['url']);
		}
	    }

	    //Update the dimensions
	    if (isset($_POST[TBKCP_ADMIN_LOGO_PRE . "width"])) {
		update_option(TBKCP_ADMIN_LOGO_PRE . 'width', $_POST[TBKCP_ADMIN_LOGO_PRE . 'width']);
	    }
	    if (isset($_POST[TBKCP_ADMIN_LOGO_PRE . "height"])) {
		update_option(TBKCP_ADMIN_LOGO_PRE . 'height', $_POST[TBKCP_ADMIN_LOGO_PRE . 'height']);
	    }
	}
	// Form Options Markup
	?>
	<div id ="admin_logo_options_wrap" style ="width:400px;position:relative;">
	    <h3>Admin Logo Settings</h3>
	    <?php echo get_option(TBKCP_ADMIN_LOGO_PRE . 'url') != false ? '<img style ="width:100px;height:110px;float:left" src ="' . get_option(TBKCP_ADMIN_LOGO_PRE . 'url') . '">' : ''; ?>
	    <input style ="margin:7px 0px 5px 0px" type="file" name= "<?php echo TBKCP_ADMIN_LOGO_PRE . 'file' ?>"/><br/>
	    Width: <input style ="width:50px;margin-bottom: 5px;" type ="text" name ="<?php echo TBKCP_ADMIN_LOGO_PRE . 'width' ?>" value ="<?php echo get_option(TBKCP_ADMIN_LOGO_PRE . 'width') == false ? "274" : get_option(TBKCP_ADMIN_LOGO_PRE . 'width'); ?>" />px
	    <div style ="margin-right:55px;float:right">Height: <input style ="width:50px" type ="text" name ="<?php echo TBKCP_ADMIN_LOGO_PRE . 'height' ?>" value ="<?php echo get_option(TBKCP_ADMIN_LOGO_PRE . 'height') == false ? "63" : get_option(TBKCP_ADMIN_LOGO_PRE . 'height'); ?>" />px </div><br/>
	    <em>Dimensions Initially set to wordpress defaults</em>
	    <input name ="<?php echo TBKCP_ADMIN_LOGO_PRE . 'update' ?>" type ="submit" class ="button-primary" value ="Update Admin Logo"/>
	</div>
	<?php
    }

}
?>