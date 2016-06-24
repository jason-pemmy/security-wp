<?php
class Toolbox_Installer_Skin extends Plugin_Installer_Skin{
	function feedback($string){
		if ( isset($this) and isset( $this->upgrader->strings[$string] ) )
			$string = $this->upgrader->strings[$string];

		if ( strpos($string, '%') !== false ) {
			$args = func_get_args();
			$args = array_splice($args, 1);
			if ( !empty($args) )
				$string = vsprintf($string, $args);
		}
		if ( empty($string) )
			return;
			
		$message = $string;
		if ( is_wp_error($message) ){
			if ( $message->get_error_data() )
				$message = $message->get_error_message() . ': ' . $message->get_error_data();
			else
				$message = $message->get_error_message();
		}
		echo "<p>$message</p>\n";
	}
}

?>