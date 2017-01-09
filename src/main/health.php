<?php

include_once ('src/Beanstalk/Client.php');

use Beanstalk\Client;


class Main_Health {


	public function mainAction($response) {
		$ready = FALSE;
		try { 
			$ready = $this->peekBeanstalk();
		} catch (\Exception $e) {
			$ready = FALSE;
		}
		if (!$ready) {
			$response->addInto('user-message', ['msg'=>'cannot communicate with message queue', 'type'=>'error']);
		}
	}

	public function peekBeanstalk() {
		$beanstalk = new Client(['host'=>'127.0.0.1']);
		$beanstalk->connect();
		$beanstalk->watch('display');
		return $beanstalk->peakReady();
	}
}
