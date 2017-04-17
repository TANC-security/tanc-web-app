<?php

use Beanstalk\Client;

class Main_Health {


	public function mainAction($response) {
		$ready = FALSE;
		try { 
			$ready = $this->peekBeanstalk();
		} catch (\Exception $e) {
			$ready = FALSE;
			$response->addInto('user-message', ['msg'=>'Cannot communicate with message queue.', 'type'=>'error']);
			return;
		}
		if (!$ready) {
			$response->addInto('user-message', ['msg'=>'Trouble with message queue.', 'type'=>'error']);
		}
	}


	public function peekBeanstalk() {
		$beanstalk = \_make('beanstalkclient');
		$beanstalk->connect();
		$beanstalk->useTube('display');
		return $beanstalk->peekReady();
	}
}
