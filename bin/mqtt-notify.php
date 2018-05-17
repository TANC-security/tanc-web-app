<?php

set_time_limit(0);
//@ob_end_flush();
ob_implicit_flush(true);

include_once(dirname(__DIR__).'/local/autoload.php');



$mqttAddress = getenv('MQTT_ADDRESS');
if ($mqttAddress == '') {
	$mqttAddress = '127.0.0.1:1883';
}
$httpAddress = getenv('HTTP_ADDRESS');
if ($httpAddress == '') {
	$httpAddress = '127.0.0.1';
}
$topicPrefix = getenv('TOPIC_PREFIX');
if ($topicPrefix == '') {
	$topicPrefix = 'security/';
}

Metrodb_Connector::setDsn('default', 'sqlite3://root:mysql@127.0.0.1:3306/var/db/tanc.db');
$topic = $topicPrefix.'event-frontend';

use Amp\Loop;
Loop::run(function () use ($mqttAddress, $httpAddress, $topicPrefix) {

	$client = new MarkKimsal\Mqtt\Client('tcp://'.$mqttAddress.'/?topics=security/status,security/event,security/alarm');
	$p = $client->connect();
	$p->onResolve(function() { echo "*** connect resolved ***\n"; });

	$client->on('message', function($packet) use ($client, $topicPrefix) {

		echo "D/Notify: got message on topic: ".$packet->getTopic()."\n".$packet->getMessage()."\n";

		try {
			$payload = json_decode($packet->getMessage(), TRUE);
			$topic = $packet->getTopic();
			
			if ($topic == 'security/status') {
				$h = new ActionPayloadHandler();
				$h->handle_status_payload($payload);
			}
			if ($topic == 'security/event') {
				$h = new EventPayloadHandler();
				$h->handle_event_payload($payload, $client, $topicPrefix);
			}

		} catch (\Error $t) {
			var_dump($t);
		} catch (\Exception $t) {
			var_dump($t);
		}

	});
});


class ActionPayloadHandler {

	public function handle_status_payload($payload) {
		echo "send action\n";
		echo $payload['armed']."\n";
		echo $payload['faulted']."\n";
		$message = '';
		if ($payload['armed'] == 'yes' && $payload['faulted'] == 'no') {

			$message = $this->_getMessageArmed();
		}
		if ($payload['armed'] == 'no' && $payload['faulted'] == 'no') {

			$message = $this->_getMessageDisarmed();
		}

		$notifyList = new Metrodb_Dataitem('settings');
		$notifyList->set('key', 'notifylist1');
		$notifyList->loadExisting();
		$notifyListVals = json_decode($notifyList->get('value'), TRUE);
		$to = [];
		foreach($notifyListVals as $_key => $_val) {
			if ($_val)
			$to[] = $_val;
		}

		echo $message."\n";
		echo "sending email to ...\n";
		echo json_encode($to)."\n";

		$x = $this->sendTransactionEmail(
			$to,
			$message,
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

	public function sendTransactionEmail($to, $subject, $htmlBody=NULL, $textBody=NULL) {
		if (!is_array($to)) {
			$to = array($to);
		}

		if (!include_once 'local/swiftmailer/swiftmailer/lib/swift_required.php' ) {
			echo "no swiftmailer\n";
			return;
		}

//		$settings = \_makeNew('plugin');

		$settings = new Metrodb_Dataitem('plugin');
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

class EventPayloadHandler {

	public function handle_event_payload($payload, $client, $tp) {

		//save all events in the event log
		$dataitem = new Metrodb_Dataitem('event');
		$dataitem->set('code',      $payload['code']);
		$dataitem->set('qualifier', $payload['qualifier']);
		$dataitem->set('uz',        $payload['user_zone']);
		$dataitem->save();
	}
}
