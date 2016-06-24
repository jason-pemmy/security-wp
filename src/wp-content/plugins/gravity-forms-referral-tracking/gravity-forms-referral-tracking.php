<?php

/*
Plugin Name: Gravity Forms Referral Tracking Plugin
Description: This plugin will populate hidden fields in Gravity Forms with referral and search terms (if available)
Version: 1.0
Plugin URI: http://tbkcreative.com
Author URI: http://tbkcreative.com
Author: Andre LeFort, tbk Creative
*/

/* Consider this a bootstrap for the plugin, nothing is defined in this, everything resides in OO classes found within the plugin */

global 	$gravity_forms_referral,
		$tbk_plugin_utils;

/*
 * One might consider the following 3 defines more appropriate in the actual plugin, but since everything
 * is pathed from this root directory, we define the constants here and pass the values into the plugin class
 * which implements the TBK_IPlugin interface
*/

define( 'GF_PLUGIN_PATH', plugin_dir_path(__FILE__) );
define( 'GF_PLUGIN_URL', plugin_dir_url(__FILE__) );
define( 'GF_PLUGIN_SLUG', basename( __FILE__, '.php') );

/* The following are helpful constants for debugging and testing */
define( 'GF_IN_DEBUG_MODE', FALSE);

/********************************************************/
/* instantiate the plugin, these calls must remain here */
/********************************************************/
add_action( 'tbk_base_plugin_loaded', 'init_gf_referral_plugin', 10);

function init_gf_referral_plugin(){
	try {
		$std_autoloader = Zend\Loader\AutoloaderFactory::getRegisteredAutoloader( 'Zend\Loader\StandardAutoloader' );
		$std_autoloader->registerPrefix( 'TBKGF', GF_PLUGIN_PATH . 'classes/TBKGF' );
	} catch (Exception $e) {
		/* weird, die */
		echo $e->getMessage();
		die();
	}

	/* let's instantiate an instance of the message hub, it will be used throughout the plugin to contain messages */
	$message_hub = TBK_MessageCollectionHub::get_instance();
	$message_hub->setup_logger( GF_PLUGIN_PATH . 'logs/messages.log' );

	$tbk_git_deploy_plugin = new TBKGF_ReferralPlugin( GF_PLUGIN_PATH, GF_PLUGIN_URL, GF_PLUGIN_SLUG );
	$tbk_git_deploy_plugin->get_utils()->set_is_in_debug_mode( GF_IN_DEBUG_MODE );
}

?>
<?php //BEGIN::SELF_HOSTED_PLUGIN_MOD
					
	/**********************************************
	* The following was added by Self Hosted Plugin
	* to enable automatic updates
	* See http://wordpress.org/extend/plugins/self-hosted-plugins
	**********************************************/
	require "__plugin-updates/plugin-update-checker.class.php";
	$__UpdateChecker = new PluginUpdateChecker('http://plugins.tbkcreative.com/extend/plugins/gravity-forms-referral-tracking/update', __FILE__,'gravity-forms-referral-tracking');			
	
//END::SELF_HOSTED_PLUGIN_MOD ?>