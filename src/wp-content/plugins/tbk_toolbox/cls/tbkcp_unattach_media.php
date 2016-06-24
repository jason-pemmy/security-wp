<?php
/*
Description: Allows admins to unnattach media
Author: Paul MacLean
Usage: 
1. A new unnattach link will be present in the media section

*/

class tbkcp_unattach_media {
    function __construct() {
	add_action( 'admin_menu', array( &$this,'unattach_init' ));
    }

    function unattach_init() {
	if ( current_user_can( 'upload_files' ) ) {
		add_filter('media_row_actions', array(&$this,'unattach_media_row_action'), 10, 2);
		//this is hacky but couldn't find the right hook
		add_submenu_page('tools.php', 'Unattach Media', 'Unattach', 'upload_files', 'unattach', array(&$this,'unattach_do_it'));
		remove_submenu_page('tools.php', 'unattach');
	}
    }
    function unattach_media_row_action( $actions, $post ) {
	if ($post->post_parent) {
		$url = admin_url('tools.php?page=unattach&noheader=true&&id=' . $post->ID);
		$actions['unattach'] = '<a href="' . esc_url( $url ) . '" title="' . __( "Unattach this media item.") . '">' . __( 'Unattach') . '</a>';
	}

	return $actions;
}
function unattach_do_it() {
	global $wpdb;

	if (!empty($_REQUEST['id'])) {
		$wpdb->update($wpdb->posts, array('post_parent'=>0), array('id'=>$_REQUEST['id'], 'post_type'=>'attachment'));
	}

	wp_redirect(admin_url('upload.php'));
	exit;
}
}




//action to set post_parent to 0 on attachment


//set it up


?>
