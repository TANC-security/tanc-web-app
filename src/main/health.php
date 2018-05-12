<?php

use Beanstalk\Client;

class Main_Health {


	public function mainAction($response) {
		$ready = FALSE;
		try { 
			ob_start();
			$ready = $this->peekMqtt();
			ob_end_clean();
		} catch (\Exception $e) {
			$ready = FALSE;
			$response->addInto('user-message', ['msg'=>'Cannot communicate with message queue.', 'type'=>'error']);
			return;
		}
	}

	public function peekMqtt() {
		$beanstalk = \_make('mqttclient');
		$conn = false;
		$p = $beanstalk->connect(function($err, $res) use (&$conn) { 
			var_dump($err);
			var_dump($res);
			if (!$err) {
				$conn = true;
			}
		});

		Amp\Promise\wait($p);
		return $conn;
	}
}
