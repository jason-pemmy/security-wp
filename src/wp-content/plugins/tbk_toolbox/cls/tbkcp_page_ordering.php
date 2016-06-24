<?php
/*
Description: Gives drag and drop page ordering capability in the wordpress admin. Started as a fork from the simple page ordering plugin
Author: Paul MacLean
Usage: 
1. Enable and drag and drop pages to reorder (posts currently not supported)
2. Custom posts need to have page attributes and heirarchical supports
*/
class tbkcp_page_ordering {
    function __construct() {
	require_once (TBKCP_PLUGIN_DIR . '/external_plugins/tbk-page-ordering/simple-page-ordering.php');
    }
}
?>