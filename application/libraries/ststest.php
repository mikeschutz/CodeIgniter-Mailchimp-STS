<?php if (!defined('BASEPATH')) exit('No direct script access allowed.');

/*
 * Sample usage / testing of Mailchimp_STS API calls in CodeIgniter 
 * Some tests are commented out by defalt. Enter appropriate settings and uncomment as desired.
 */

class STStest extends CI_Controller {

	function index()
	{
	
		echo "<pre>"; // Not bothering with HTML or views for these tests...
		
		$this->load->library('mailchimp_sts');

		echo "*** Testing Email Verification Methods ***\n\n";

		echo "DeleteVerifiedEmail test #1:\n";
		// $result = $this->mailchimp_sts->deleteVerifiedEmailAddress("EMAIL.TO@DELETE");
		var_dump($result); echo "\n\n";

		echo "ListVerifiedEmailAddresses test #1:\n";
		$result = $this->mailchimp_sts->listVerifiedEmailAddresses();
		var_dump($result); echo "\n\n";

		echo "VerifyEmailAddress test #1:\n";
		// $result = $this->mailchimp_sts->verifyEmailAddress("EMAIL.TO@ADD");
		var_dump($result); echo "\n\n";

		echo "*** Testing MC Stats Methods ***\n\n";
		
		echo "GetBounces test #1:\n";
		$result = $this->mailchimp_sts->getBounces("2011-12-01 00:00:00");
		var_dump($result); echo "\n\n";

		echo "GetBounces test #2:\n";
		$result = $this->mailchimp_sts->getBounces();
		var_dump($result); echo "\n\n";
		
		echo "GetSendStats test:\n";
		$result = $this->mailchimp_sts->getSendStats();
		var_dump($result); echo "\n\n";
		
		echo "GetTags test:\n";
		$result = $this->mailchimp_sts->getTags();
		var_dump($result); echo "\n\n";
		
		echo "GetUrlStats test:\n";
		$result = $this->mailchimp_sts->getUrlStats();
		var_dump($result); echo "\n\n";
		
		echo "GetUrls test:\n";
		$result = $this->mailchimp_sts->getUrls();
		var_dump($result); echo "\n\n";
		
		echo "*** Testing Sending Methods ***\n\n";

		echo "sendEmail test:\n";
		$message = array(
			'html' => '<html><body><h2>Test Message 2</h2><p>This is a test email message</p></body></html>',
			'subject' => 'Test email through STS/SES',
			'to_email' => 'EMAIL@TOSEND.TO',
			'to_name' => 'RECIPIENT NAME',
			'from_email' => 'VERIFIED@EMAIL.ADDRESS', // omit to use config default
			'from_name' => 'Your Name', // omit to use config default
		);
		// $result = $this->mailchimp_sts->sendEmail($message);
		if ($result !== false)
		{
			echo "Message sent with ID: " . $result . "\n\n";
		} else {
			echo "Failed to send email: (".$this->mailchimp_sts->errorCode.") " . $this->mailchimp_sts->errorMessage . "\n\n";
		}

		echo "*** Testing Stats Methods ***\n\n";

		echo "getSendQuota test:\n";
		$stats = $this->mailchimp_sts->getSendQuota();
		var_dump($stats); echo "\n\n";

		echo "getSendStatistics test:\n";
		$stats = $this->mailchimp_sts->getSendStatistics();
		var_dump($stats); echo "\n\n";


	}

}

/* End of file ststest.php */
/* Location: ./application/controllers/ststest.php */