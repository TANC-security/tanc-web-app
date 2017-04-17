<?php

class Status_Main {

	public $logService;

	public function sendAction($request, $response) {
		$status = $request->vars['status'];
		echo "send action\n";
		echo $status['armed']."\n";
		echo $status['faulted']."\n";
		if ($status['armed'] == 'yes' && $status['faulted'] == 'no') {

			$message = $this->_getMessageArmed();
		}
		if ($status['armed'] == 'no' && $status['faulted'] == 'no') {

			$message = $this->_getMessageDisarmed();
		}

//			echo $message."\n";
			echo "sending email to ...\n";
			echo _get('mail.to.default')."\n";

			$x = $this->sendTransactionEmail(
				_get('mail.to.default'),
				$message,
				_get('smtp.username'),
				NULL,
				$message
			);
			echo $x."\n";
			return;
	}

	public function _getMessageDisarmed() {
		return 'System Disarmed';
	}

	public function _getMessageArmed() {
		return 'System Armed';
	}

	public function sendTransactionEmail($to, $subject, $from, $htmlBody=NULL, $textBody=NULL) {
		if (!is_array($to)) {
			$to = array($to);
		}

		if (!include_once 'local/swiftmailer/swiftmailer/lib/swift_required.php' ) {
			echo "no swiftmailer\n";
			return;
		}

		$settings = \_makeNew('settings');
		$settings->set('key', 'smtp');
		$settings->loadExisting();
		$smtpVals = json_decode($settings->get('value'), TRUE);
		$from     = $smtpVals['from'];

		$transport = Swift_SmtpTransport::newInstance($smtpVals['host'], $smtpVals['port'], "ssl")
//		  ->setAuthMode('XOAUTH2')
		  ->setUsername($smtpVals['smtp_username'])
		  ->setPassword($smtpVals['smtp_password']);


		$mailer = Swift_Mailer::newInstance($transport);

		$message = Swift_Message::newInstance()
		  ->setSubject($subject)
		  ->setFrom($from)
		  ->setTo($to)
		  ->addPart($textBody, 'text/plain');

		$result = $mailer->send($message);
		return $result;
	}
}
