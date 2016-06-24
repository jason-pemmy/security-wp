<?php
/*
Description: Displays a twitter feed via a shortcode
Author: Paul MacLean
Usage: 
1. Shotcode ex: [twitter-feed username="3doordigital"]
2. See http://3doordigital.com/wordpress/plugins/wp-twitter-feed/ for complete list of options
*/

class tbkcp_twitter_feed {
    function __construct() {
	require_once(TBKCP_PLUGIN_DIR . 'external_plugins/wp-twitter-feed/wp-twitter-feed.php');
    }
}

?>
