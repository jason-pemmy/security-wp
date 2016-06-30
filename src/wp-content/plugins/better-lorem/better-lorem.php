<?php
/*
    Copyright 2010 Nicolas Kuttler (email : wp@nicolaskuttler.de )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA

Plugin Name: Better Lorem Ipsum Generator
Author: Nicolas Kuttler
Author URI: http://www.nkuttler.de/
Plugin URI: http://www.nkuttler.de/wordpress/better-lorem-posts/
Description: Better lorem ipsum generator that does taxonomies and post types
Version: 0.9.3.7
Text Domain: better-lorem
*/

/**
 * @package better-lorem
 * @subpackage pluginwrapper
 * @since 0.9
 */
if ( !class_exists( 'BetterLorem' ) ) {

	class BetterLorem {

		/**
		 * Array containing the options
		 *
		 * @since unknown
		 * @var array
		 */
		private $options;

		/**
		 * Path to the plugin
		 *
		 * @since 0.9
		 * @var string
		 */
		public $plugin_dir;

		/**
		 * Path to the plugin file
		 *
		 * @since 0.9
		 * @var string
		 */
		public $plugin_file;

		/**
		 * A LorelIpsum object
		 *
		 * @since 0.9
		 * @var string
		 */
		public $Lorem;

		/**
		 * Constructor, set up the variables
		 *
		 * @since 0.9
		 */
		public function __construct() {
			$this->options = get_option( 'better-lorem' );
			// Full path to main file
			$this->plugin_file= __FILE__;
			$this->plugin_dir = dirname( $this->plugin_file );
		}

		/**
		 * Return a specific option value
		 *
		 * @param string $option name of option to return
		 * @return mixed
		 * @since 0.9
		 */
		public function get_option( $option ) {
			if ( isset ( $this->options[$option] ) )
				return $this->options[$option];
			else
				return false;
		}

		/**
		 * Deactivate this plugin and die with an error message
		 *
		 * @param string error
		 * @since 0.9
		 * @return none
		 */
		public function deactivate_and_die( $error = false ) {
			load_plugin_textdomain(
				'better-lorem',
				false,
				basename( $this->plugin_dir ) . '/translations'
			);
			$message = sprintf( __( "Better Lorem has been automatically deactivated because of the following error: <strong>%s</strong>." ), $error );
			if ( !function_exists( 'deactivate_plugins' ) )
				include ( ABSPATH . 'wp-admin/includes/plugin.php' );
			deactivate_plugins( __FILE__ );
			wp_die( $message );
		}

		/**
		 * Log helper, exposes the log to the web by default
		 *
		 * @since 0.9
		 */
		public function log( $msg, $level = 0 ) {
			$log = $this->plugin_dir . '/log.txt';
			$msg = date( 'H:i:s' ) . ": $msg";
			$msg .= "\n";
			$fh = fopen( $log, 'a' ) or trigger_error( "can't open logfile $log" );
			if ( !$this->get_option( 'log' ) )
				return;
			if ( $this->get_option( 'loglevel' ) != $level )
				return;
			fwrite( $fh, $msg );
			fclose( $fh );
		}

	}

	/**
	 * Instantiate the appropriate classes
	 */
	$missing = 'Core plugin files are missing, please reinstall the plugin';
	if ( is_admin() ) {
		if ( @include( 'inc/admin.php' ) )
			$BetterLoremAdmin = new BetterLoremAdmin;
		else
			BetterLorem::deactivate_and_die( $missing );
		if ( @include( 'inc/LoremIpsum.class.php' ) )
			$BetterLoremAdmin->Lorem = new LoremIpsumGenerator;
		else
			BetterLorem::deactivate_and_die( $missing );
	}

}
