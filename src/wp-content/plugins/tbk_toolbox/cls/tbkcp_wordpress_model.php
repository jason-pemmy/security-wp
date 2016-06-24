<?php
/*
Description: Enables and includes the WordPressModel class.  
Author: Trevor Mills
Usage: 
$Model = new WordPressModel();
$Model->bind('post_type','my_custom_post_type');
$posts = $Model->getAll();
*/

class tbkcp_wordpress_model {
    function __construct() {
		include_once(TBKCP_PLUGIN_DIR.'assets/lib/model/WordPressModel.php');
    }
}
?>