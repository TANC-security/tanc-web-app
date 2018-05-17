<?php

class Status_Main {

	public $logService;

	public function sendAction($request, $response) {
		echo "send action\n";
		$status = $request->vars['status'];
		echo $status['armed']."\n";
		echo $status['faulted']."\n";
		if ($status['armed'] == 'yes' && $status['faulted'] == 'no') {

			$message = $this->_getMessageArmed();
		}
		if ($status['armed'] == 'no' && $status['faulted'] == 'no') {

			$message = $this->_getMessageDisarmed();
		}


		$notifyList = \_makeNew('settings');
		$notifyList->set('key', 'notifylist1');
		$notifyList->loadExisting();
		$notifyListVals = json_decode($notifyList->get('value'), TRUE);
		$to = [];
		foreach($notifyListVals as $_key => $_val) {
			if ($_val)
			$to[] = $_val;
		}


//			echo $message."\n";
			echo "sending email to ...\n";
			echo json_encode($to)."\n";

			$x = $this->sendTransactionEmail(
				$to,
				$message,
				_get('smtp.username', NULL),
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

		$settings = \_makeNew('plugin');
		$settings->set('plug_name', 'smtp1');
		$settings->set('plug_type', 'smtp');
		$x = $settings->loadExisting();
		$smtpVals = json_decode($settings->get('data'), TRUE);
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
