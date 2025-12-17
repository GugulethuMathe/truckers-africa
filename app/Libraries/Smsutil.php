<?php

namespace App\Libraries;

class Smsutil
{
	// Your PHP installation needs cUrl support, which not all PHP installations
	// include by default.
	// To run under docker:
	// docker run -v $PWD:/code php:7.3.2-alpine php /code/code_sample.php
	public function sendSMS($cellNumber, $message)
	{
		$username = 'kyasms';
		$password = 'kya@Notification1';
		$queryCell = $this->cleanCell($cellNumber);
		$messages = array(
			array('to' => $queryCell, 'body' => $message)
		);

		$result = $this->send_message(json_encode($messages), 'https://api.bulksms.com/v1/messages?auto-unicode=true&longMessageMaxParts=30', $username, $password);

		if ($result['http_status'] != 201) {
			log_message('error', "SMS Error: " . ($result['error'] ? $result['error'] : "HTTP status " . $result['http_status'] . "; Response was " . $result['server_response']));
			return false;
		}
		
		return true;
	}

	public function send_message($post_body, $url, $username, $password)
	{
		$ch = curl_init();
		$headers = array(
			'Content-Type:application/json',
			'Authorization:Basic ' . base64_encode("$username:$password")
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_body);
		// Allow cUrl functions 20 seconds to execute
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		// Wait 10 seconds while trying to connect
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		$output = array();
		$output['server_response'] = curl_exec($ch);
		$curl_info = curl_getinfo($ch);
		$output['http_status'] = $curl_info['http_code'];
		$output['error'] = curl_error($ch);
		curl_close($ch);
		return $output;
	}

	public function cleanCell($raw)
	{
		//Remove any spaces
		$raw = str_replace(" ", "", $raw);
		//Remove + sign
		$raw = str_replace("+", "", $raw);
		//Remove leading zero, if any 0823776272
		if ($raw[0] == "0" and strlen($raw) == 10) {
			$raw = substr_replace($raw, "27", 0, 1);
		}

		//Check if cell number contains 27 as first digit, add 27 if not available

		if (!($raw[0] == "2" and $raw[1] == "7")) {
			$raw = "27$raw";
		}

		return $raw;
	}
}
