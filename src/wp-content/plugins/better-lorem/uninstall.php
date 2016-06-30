<?php
/**
 * Delete the better-related option
 *
 * @package better-related
 * @subpackage uninstall
 * @since 0.9
 */

// If uninstall/delete not called from WordPress then exit
if( ! defined ( 'ABSPATH' ) && ! defined ( 'WP_UNINSTALL_PLUGIN' ) )
	exit ();

// Delete shadowbox option from options table
delete_option ( 'better-lorem' );
