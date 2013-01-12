<?php

/**
 * RAZOR EXCEPTION HANDLER
 * Global singleton that catches and logs all uncaught exceptions
 *
 * @version 3.2
 * @since 3.2
 * @package Razor
 * @subpackage Exception
 * @license GPL v2.0
 * @link https://code.google.com/p/wp-razor/
 *
 * ========================================================================================================
 */

class RAZ_exceptionHandler {


	var $buffer;			    // Error data


	// ============================================================================================================ //


	public function  __construct() {

		$this->buffer = array();
	}


	public function add($data){

		$this->buffer[] = $data;
	}


} // End of class RAZ_exceptionHandler

?>