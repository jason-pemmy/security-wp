<?php
if ( ! function_exists( 'log_message' ) ) {
	function log_message( $message ) {
		if ( true === WP_DEBUG_LOG ) {
			if ( is_array( $message ) || is_object( $message ) ) {
				error_log( print_r( $message, true ) );
			} else {
				error_log( $message );
			}
		}
	}

	if ( WP_DEBUG_LOG && true !== WP_DEBUG ) {
		ini_set( 'log_errors', 0 );
		ini_set( 'error_log', WP_CONTENT_DIR . '/debug.log' );
	}
}

if ( ! function_exists( 'show_error' ) ) {
	function show_error( $message, $status_code = null, $heading = 'An Error Was Encountered' ) {
		if ( ! empty( $heading ) ) {
			$message = '<h2>' . $heading . '</h2>' . $message;
		}
		wp_die( $message );
	}
}