<?php
/*
Description: Will load an href into a jquery dialog for all elements given the tbkcp_main_popup class
Author: Paul MacLean
Usage: 
//Requires jquery-ui (loaded by tbk_client_plugin by default)
<a href "your-href" class ="tbkcp_main_popup">Open popup<a/>
*/
class tbkcp_popup_dialog {
    /*  Function: __construct
         adds the enqueue actions
   */
    function __construct(){
      	  add_action('wp_enqueue_scripts', array(&$this, 'load_popup_assets'));
    }
    /*  Function: load_popup_assets
        loads the popup_dialog.js script
    */
    function load_popup_assets(){
	wp_enqueue_script(
		'tbkcp_popup_script',
		TBKCP_PLUGIN_URL.'assets/js/popup_dialog.js',
		array('jquery')
	);
    }
}

?>
