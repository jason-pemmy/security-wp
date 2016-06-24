<?php

//$credit_line = new Tbk_Credit_Line();

class Tbk_Credit_Line {

	private $source = 'http://tbkcreative.com/credit.php';

	function __construct() {
		wp_schedule_event( time(), 'daily', 'populate_options_record' );
		add_action( 'populate_options_record', array( &$this, 'save_to_options_table' ) );

		add_action( 'add_credit_line_to_footer', array( &$this, 'add_credit_line' ) );
	}

	function save_to_options_table() {
		$credit_line_data = file_get_contents( $this->source );
		update_option( 'credit_line', $credit_line_data );
	}

	public function add_credit_line() {
		$credit_line = get_option( 'credit_line' );

		return apply_filters( 'filter_credit_line', $credit_line );
	}
}