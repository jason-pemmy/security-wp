<?php
/*
Description: Updates the blog urls for migration (forked from velvet blues urls)
Author: Paul MacLean
Usage: 
1.Go to settings -> update urls
*/

class tbkcp_update_urls {
    function __construct() {
	require_once(TBKCP_PLUGIN_DIR . 'external_plugins/velvet-blues-update-urls/velvet-blues-update-urls.php');
    }
}

?>
