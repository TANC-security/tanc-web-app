<?php

use Beanstalk\Client;

class Main_Health {


	public function mainAction($response) {
		$ready = FALSE;
		try { 
			//$ready = $this->peekBeanstalk();
			ob_start();
			$ready = $this->peekMqtt();
			ob_end_clean();
		} catch (\Exception $e) {
			$ready = FALSE;
			$response->addInto('user-message', ['msg'=>'Cannot communicate with message queue.', 'type'=>'error']);
			return;
		}
		//new web socket server routinely drains display queue
		//so we cannot determine if there's trouble when the display queue
		//is empty.
		//TODO: we could maybe look at how many workers are watching
		//the default queue
		//or we could check the systemd service serial-comm
		/*
		if (!$ready) {
			$response->addInto('user-message', ['msg'=>'Trouble with message queue.', 'type'=>'error']);
		}
		 */
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

	public function peekBeanstalk() {
		$beanstalk = \_make('beanstalkclient');
		$beanstalk->connect();
		$beanstalk->useTube('display');
		return $beanstalk->peekReady();
	}
}
