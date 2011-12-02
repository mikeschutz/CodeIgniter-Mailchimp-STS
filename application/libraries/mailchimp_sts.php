<?php

/* CodeIgniter library for MailChimp STS */

class Mailchimp_STS {
	var $version = "1.0";
	var $errorMessage;
	var $errorCode;
	
	/**
	 * Cache the information on the API location on the server
	 */
	var $apiUrl;
	
	/**
	 * Default to a 300 second timeout on server calls
	 */
	var $timeout = 300; 
	
	/**
	 * Default to a 8K chunk size
	 */
	var $chunkSize = 8192;
	
	/**
	 * Cache the user api_key so we only have to log in once per client instantiation
	 */
	var $api_key;

	/**
	 * Cache the user api_key so we only have to log in once per client instantiation
	 */
	var $secure = false;
	
	/**
	 * Connect to the MailChimp API for a given list.
	 * 
	 * @param string $apikey Your MailChimp apikey
	 * @param string $secure Whether or not this should use a secure connection
	 */
	function Mailchimp_STS($apikey, $secure=false)
	{
		// Get CI Instance
		$this->CI = &get_instance();
		
		$this->CI->load->config('mailchimp_sts', TRUE);
		
		$this->secure = $this->CI->config->item('secure', 'mailchimp_sts');
		$this->apiUrl = parse_url("http://sts.mailchimp.com/" . $this->version . "/");
		$this->api_key = $this->CI->config->item('apikey', 'mailchimp_sts');
	}

	function setTimeout($seconds)
	{
		if (is_int($seconds)){
			$this->timeout = $seconds;
			return true;
		}
	}

	function getTimeout()
	{
		return $this->timeout;
	}

	function useSecure($val)
	{
		if ($val===true)
		{
			$this->secure = true;
		}
		else 
		{
			$this->secure = false;
		}
	}

	/* API call code starts here: */

	/**
	 * Deletes a verified email address. This action takes immediate effect, so use it with care. 
	 * For details view http://apidocs.mailchimp.com/sts/1.0/deleteverifiedemailaddress.func.php
	 *
	 * @section Email Verification
	 *
	 * @param string email the verified email address to delete
	 * @return array request ID (?)
	 */
	function deleteVerifiedEmailAddress($email) {
		return $this->callServer("DeleteVerifiedEmailAddress", array('email' => $email));
	}

	/**
	 * Returns a list containing all of the email addresses that have been verified. 
	 * For details view http://apidocs.mailchimp.com/sts/1.0/listverifiedemailaddresses.func.php
	 *
	 * @section Email Verification
	 *
	 * @return array email_addresses the list of verified email addresses
	 */
	function listVerifiedEmailAddresses() {
		return $this->callServer("ListVerifiedEmailAddresses");
	}

	/**
	 * Verifies an email address. This action causes a confirmation email message to be sent to the specified address. 
	 * For details view http://apidocs.mailchimp.com/sts/1.0/verifyemailaddress.func.php
	 *
	 * @section Email Verification
	 *
	 * @param string email the email address to verify
	 * @return array request ID (?)
	 */
	function verifyEmailAddress($email) {
		return $this->callServer("VerifyEmailAddress", array('email' => $email));
	}

	/**
	 * Returns all full bounce messages from the past 48 hours..
	 * For details view http://apidocs.mailchimp.com/sts/1.0/getbounces.func.php
	 *
	 * @section MC Stats
	 *
	 * @param string since optional time to limit results. Format Y-m-d H:i:s
	 * @return array the bounce messages including datetime and message
	 */
	function getBounces($since = NULL) {
		$params = array();
		if ($since !== NULL) $params['since'] = $since;
		return $this->callServer("GetBounces", $params);
	}

	/**
	 * Returns all stats in one hour intervals. Note that "_all" is special tag which aggregates all other tags
	 * For details view http://apidocs.mailchimp.com/sts/1.0/getsendstats.func.php
	 *
	 * @section MC Stats
	 *
	 * @param string tag_id optional tag to filter results.
	 * @param string since optional filter data returned to date/hour in the format "YYYY-MM-DD HH"
	 * @return array the bounce messages including datetime and message
	 */
	function getSendStats($tag_id = NULL, $since = NULL) {
		$params = array();
		if ($tag_id !== NULL) $params['tag_id'] = $tag_id;
		if ($since !== NULL) $params['since'] = $since;
		return $this->callServer("GetSendStats", $params);
	}

	/**
	 * Returns all tags defined for your account along with all-time, non-unique aggregate stats for each. 
	 * For details view http://apidocs.mailchimp.com/sts/1.0/gettags.func.php
	 *
	 * @section MC Stats
	 *
	 * @return array tag data
	 */
	function getTags() {
		return $this->callServer("GetTags");
	}

	/**
	 * Returns all URL stats in one hour intervals. 
	 * For details view http://apidocs.mailchimp.com/sts/1.0/geturlstats.func.php
	 *
	 * @section MC Stats
	 *
	 * @param string url_id optional filter by a single URL.
	 * @param string since optional filter data returned to date/hour in the format "YYYY-MM-DD HH"
	 * @return array tag data
	 */
	function getUrlStats($url_id = NULL, $since = NULL) {
		$params = array();
		if ($url_id !== NULL) $params['url_id'] = $tag_id;
		if ($since !== NULL) $params['since'] = $since;
		return $this->callServer("GetUrlStats");
	}

	/**
	 * Returns all tracked urls defined for your account along with all-time, non-unique aggregate stats for each 
	 * For details view http://apidocs.mailchimp.com/sts/1.0/geturls.func.php
	 *
	 * @section MC Stats
	 *
	 * @return array url data
	 */
	function getUrls() {
		return $this->callServer("GetUrls");
	}

	
	/**
	 * Composes an email message based on input data, and then immediately queues the message for sending.
	 * For details view http://apidocs.mailchimp.com/sts/1.0/sendemail.func.php
	 *
	 * @section Sending
	 *
	 * @param array $message message data
	 * @param bool $track_opens whether or not to turn on MailChimp-specific opens tracking
	 * @param bool $track_clicks whether or not to turn on MailChimp-specific click tracking
	 * @param array $tags an array of strings to tag the message with
	 * @return mixed message_id on success, false on failure
	 */
	function sendEmail($message, $track_opens = TRUE, $track_clicks = TRUE, $tags = array()) {
		// Add defaults if not set:
		foreach (array('from_email', 'from_name') as $key)
			if (!isset($message[$key])) $message[$key] = $this->CI->config->item($key, 'mailchimp_sts');
		
		// Prepare message:
		$params = array();
		$params['message'] = $message;
		
		$params['track_opens'] = $track_opens;
		$params['track_clicks'] = $track_clicks;
		$params['tags'] = $tags;
		
		// Send away:
		$result = $this->callServer("sendEmail", $params);

		if ($result['status'] == "sent") return $result['message_id'];
		else
		{
			$this->errorMessage = $result['msg'];
			$this->errorCode = $result['aws_code'];
			return false;
		}
	}

	/**
	 * Returns the user's current activity limits.
	 * For details view http://apidocs.mailchimp.com/sts/1.0/getsendquota.func.php
	 *
	 * @section Stats
	 *
	 * @return array the users quota information
	 */
	function getSendQuota() {
		return $this->callServer("GetSendQuota");
	}

	/**
	 * Returns the user's sending statistics. The result is the last two weeks of sending activity. 
	 * For details view http://apidocs.mailchimp.com/sts/1.0/getsendstatistics.func.php
	 *
	 * @section Stats
	 *
	 * @return array user's sending statistics
	 */
	function getSendStatistics() {
		return $this->callServer("GetSendStatistics");
	}



	/**
	 * Actually connect to the server and call the requested methods, parsing the result
	 * You should never have to call this function manually
	 */
	function callServer($method, $params = NULL)
	{
		$dc = "us1";
		if (strstr($this->api_key,"-"))
		{
			list($key, $dc) = explode("-",$this->api_key,2);
			if (!$dc) $dc = "us1";
		}
		$host = $dc.".".$this->apiUrl["host"];
		$params["apikey"] = $this->api_key;

		$this->errorMessage = "";
		$this->errorCode = "";

		$sep_changed = false;
		//sigh, apparently some distribs change this to &amp; by default
		if (ini_get("arg_separator.output")!="&")
		{
			$sep_changed = true;
			$orig_sep = ini_get("arg_separator.output");
			ini_set("arg_separator.output", "&");
		}
		$post_vars = http_build_query($params);
		if ($sep_changed)
		{
			ini_set("arg_separator.output", $orig_sep);
		}

		$payload = "POST " . $this->apiUrl["path"] . $method . ".php HTTP/1.0\r\n";
		$payload .= "Host: " . $host . "\r\n";
		$payload .= "User-Agent: CI_MC_STS/" . $this->version ."\r\n";
		$payload .= "Content-type: application/x-www-form-urlencoded\r\n";
		$payload .= "Content-length: " . strlen($post_vars) . "\r\n";
		$payload .= "Connection: close \r\n\r\n";
		$payload .= $post_vars;
		
		ob_start();
		if ($this->secure)
		{
			$sock = fsockopen("ssl://".$host, 443, $errno, $errstr, 30);
		}
		else 
		{
			$sock = fsockopen($host, 80, $errno, $errstr, 30);
		}
		if(!$sock)
		{
			$this->errorMessage = "Could not connect (ERR $errno: $errstr)";
			$this->errorCode = "-99";
			ob_end_clean();
			return false;
		}
		
		$response = "";
		fwrite($sock, $payload);
		stream_set_timeout($sock, $this->timeout);
		$info = stream_get_meta_data($sock);
		while ((!feof($sock)) && (!$info["timed_out"]))
		{
			$response .= fread($sock, $this->chunkSize);
			$info = stream_get_meta_data($sock);
		}
		fclose($sock);
		ob_end_clean();

		if ($info["timed_out"])
		{
			$this->errorMessage = "Could not read response (timed out)";
			$this->errorCode = -98;
			return false;
		}

		list($headers, $response) = explode("\r\n\r\n", $response, 2);
		$headers = explode("\r\n", $headers);
		$errored = false;

		foreach($headers as $h)
		{
			if (substr($h,0,26)==="X-MailChimp-API-Error-Code"){
				$errored = true;
				$error_code = trim(substr($h,27));
				break;
			}
		}
		
		if(ini_get("magic_quotes_runtime")) $response = stripslashes($response);
		
		$serial = unserialize($response);
		if($response && $serial === false)
		{
			$response = array("error" => "Bad Response.  Got This: " . $response, "code" => "-99");
		}
		else
		{
			$response = $serial;
		}
		if($errored && is_array($response) && isset($response["error"]))
		{
			$this->errorMessage = $response["error"];
			$this->errorCode = $response["code"];
			return false;
		}
		elseif($errored)
		{
			$this->errorMessage = "No error message was found";
			$this->errorCode = $error_code;
			return false;
		}
		
		return $response;
	}

}

/* End mailchimp_sts.php */
/* Location: ./application/libraries/mailchimp_sts.php */