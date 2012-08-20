<?php
/**
 * RAZOR UNIT TEST MOCK MAILER
 * A mock function that overrides the standard PHP mailer class, preventing it from sending e-mails
 * during testing, and capturing the sent "e-mails" for analysis.
 *
 * @version 3.1
 * @since 0.1
 * @author original version from http://svn.automattic.com/wordpress-tests/
 * @package Razor
 * @subpackage Core
 * @license GPL v2.0
 * @link http://code.google.com/p/wp-razor/
 *
 * ========================================================================================================
 */


class MockPHPMailer extends PHPMailer {

	var $mock_sent = array();

	// Override the Send function so it doesn't actually send anything
	function Send() {

		$header = "";
		$body = "";
		$result = true;

		if((count($this->to) + count($this->cc) + count($this->bcc)) < 1)
		{
		    $this->SetError($this->Lang("provide_address"));
		    return false;
		}

		// Set whether the message is multipart/alternative
		if(!empty($this->AltBody)){
		    $this->ContentType = "multipart/alternative";
		}

		$this->error_count = 0; // Reset errors
		$this->SetMessageType();
		$header .= $this->CreateHeader();
		$body = $this->CreateBody();

		if($body == ""){
		    return false;
		}

		$this->mock_sent[] = array(
		    'to' => $this->to,
		    'cc' => $this->cc,
		    'bcc' => $this->bcc,
		    'header' => $header,
		    'body' => $body,
		);

		return $result;
	}

}

?>