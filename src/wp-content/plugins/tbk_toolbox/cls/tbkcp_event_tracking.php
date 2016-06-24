<?php

class tbkcp_event_tracking {

    function __construct() {
	include_once(TBKCP_PLUGIN_DIR.'external_plugins/tbk_event_tracking/tbk_event_tracking.php');
    }

    function get_description() {
	return 'Easily adds Google analytic tracking events';
    }

}