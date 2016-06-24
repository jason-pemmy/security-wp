<?php 
/*
Description: Extends the built-in avatar functions of WordPress. It adds a “Custom Avatar URL” field to the user’s Profile page
Author: Paul MacLean
Usage: 
<?php get_the_author_meta('custom_avatar'); ?>
*/

define('TBKCP_CUSTOM_AVA_PRE', 'tbkcp_custom_avatars_');

class tbkcp_custom_avatars {

    function __construct() {
	add_action('show_user_profile', array(&$this, 'custom_avatar_field'));
	add_action('edit_user_profile', array(&$this, 'custom_avatar_field'));

	add_action('personal_options_update', array(&$this,'save_custom_avatar_field'));
	add_action('edit_user_profile_update', array(&$this,'save_custom_avatar_field'));

	add_filter('get_avatar', array(&$this,'gravatar_filter'),10,5);
    }

    function save_custom_avatar_field( $user_id ) {
	if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }
		update_usermeta( $user_id, 'custom_avatar', $_POST['custom_avatar'] );
    }

    function custom_avatar_field($user) {
	?>
	<h3>Custom Avatar</h3>

	<table class ="form-table">
	    <tr>
		<th><label for="custom_avatar">Custom Avatar URL:</label></th>
		<td>
		    <input type="text" name="custom_avatar" id="be_custom_avatar" value="<?php echo esc_attr(get_the_author_meta('custom_avatar', $user->ID)); ?>" /><br />
		    <span>Type in the URL of the image you'd like to use as your avatar. This will override your default Gravatar, or show up if you don't have a Gravatar. <br /><strong>Image should be 70x70 pixels.</strong></span>
		</td>
	    </tr>
	</table>
	<?php
    }

    function gravatar_filter($avatar, $id_or_email, $size, $default, $alt) {
	$custom_avatar = get_the_author_meta('custom_avatar',$id_or_email);

	if ($custom_avatar)
		$return = '<img src="'.$custom_avatar.'" width="'.$size.'" height="'.$size.'" alt="'.$alt.'" />';
	elseif ($avatar)
		$return = $avatar;
	else
		$return = '<img src="'.$default.'" width="'.$size.'" height="'.$size.'" alt="'.$alt.'" />';

	return $return;
}

}