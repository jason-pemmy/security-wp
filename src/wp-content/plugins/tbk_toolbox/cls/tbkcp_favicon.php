<?php
/*
Description: This loads in the favicon from user supplied info
Author: Paul MacLean
*/
define('TBKCP_FAVICON_PRE', 'tbkcp_favicon_');

class tbkcp_favicon {

    function __construct() {
	add_action('wp_head', array(&$this, 'blog_favicon'));

    }

    function get_description(){
	return 'Loads a favicon from an admin uploaded image';
    }

    function blog_favicon() {
	echo '<link rel="Shortcut Icon" type="image/x-icon" href="'.get_option(TBKCP_FAVICON_PRE.'url').'"/>';
    }

    function form_options() {
	// Form Handling
	if (isset($_POST[TBKCP_FAVICON_PRE . "update"])) {

	    //Upload the image the wordpress way
	    if (isset($_FILES[TBKCP_FAVICON_PRE . "file"])) {
		$uploaded_logo_file = wp_handle_upload($_FILES[TBKCP_FAVICON_PRE . "file"], array('test_form' => false));

		if (!$uploaded_logo_file['error']) {
		    update_option(TBKCP_FAVICON_PRE . 'url', $uploaded_logo_file['url']);
		}
	    }

	}

	// Form Markup
	?>
	<div id ="admin_logo_options_wrap" style ="width:400px;position:relative;">
	    <h3>Favicon Image</h3>
	<?php echo get_option(TBKCP_FAVICON_PRE . 'url') != false ? '<img style ="width:100px;height:110px;float:left" src ="' . get_option(TBKCP_FAVICON_PRE . 'url') . '">' : ''; ?>
		<input style ="margin:7px 0px 5px 0px" type="file" name= "<?php echo TBKCP_FAVICON_PRE . 'file' ?>"/><br/>
		<input name ="<?php echo  TBKCP_FAVICON_PRE . 'update' ?>" type ="submit" class ="button-primary" value ="Update Favicon"/>
	</div>
	<?php
    }

    //Checks to see if all essential elements have been set
    function get_configuration_errors(){
	$errors =array();
	if(!get_option(TBKCP_FAVICON_PRE . 'url')){
	    array_push($errors, 'Image Not Set');
	}

	if($errors){
	    return str_replace('_', ' ', TBKCP_FAVICON_PRE) . ': ' . implode(',', $errors);
	}
	return false;
    }

}

?>
