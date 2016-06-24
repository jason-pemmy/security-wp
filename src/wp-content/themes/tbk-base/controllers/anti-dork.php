<?php
/*
 * Dorking:
 * Google Dorking is a term that refers to the practice of applying advanced search techniques and specialized search
 * engine parameters to discover confidential information from companies and individuals that wouldn't typically
 * show up during a normal web search.
 *
 * http://www.webopedia.com/TERM/G/google-dorking.html
 */
Anti_Dork::instantiate();
class Anti_Dork extends Base_Factory {

	function __construct() {
		//remove unnecessary header information
		add_action('init', array( &$this, 'remove_header_info' ));

		//remove wp version meta tag and from rss feed
		add_filter('the_generator', array( &$this, '__return_false' ));

		//remove wp version param from any enqueued scripts
		add_filter( 'script_loader_src', array( &$this, 'at_remove_wp_ver_css_js' ), 9999 );

		/*Disable ping back scanner and complete xmlrpc class. */
		add_filter( 'wp_xmlrpc_server_class', '__return_false' );
		add_filter( 'xmlrpc_enabled', '__return_false');

		//Remove error mesage in login
		add_filter('login_errors', create_function('$a', 'return "Invalid Input";'));

		//remove xpingback header
		add_filter('wp_headers', array( &$this, 'remove_x_pingback' ));
	}

	function remove_header_info() {
		remove_action('wp_head', 'feed_links_extra', 3);
		remove_action('wp_head', 'rsd_link');
		remove_action('wp_head', 'wlwmanifest_link');
		remove_action('wp_head', 'wp_generator');
		remove_action('wp_head', 'start_post_rel_link');
		remove_action('wp_head', 'index_rel_link');
		remove_action('wp_head', 'parent_post_rel_link', 10, 0);
		remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10,0); // for WordPress >= 3.0
	}

	function at_remove_wp_ver_css_js( $src ) {
		if (strpos($src, 'ver=')) {
			$src = remove_query_arg('ver', $src);
		}
		return $src;
	}

	function remove_x_pingback($headers) {
		unset($headers['X-Pingback']);
		return $headers;
	}

}
