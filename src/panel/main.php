<?php

class Panel_Main {

	public $logService;

	public function eventAction($request, $response) {
		$payload = $request->vars['payload'];
		echo "event action\n";

		$dataitem = new Metrodb_Dataitem('event');
		$dataitem->set('code',      $payload['code']);
		$dataitem->set('qualifier', $payload['qualifier']);
		$dataitem->save();
		$payload = $this->addDescription($payload);
		$x = $this->sendEvent($payload);

		$response->addTo('items', print_r($payload,1));
	}

	/**
	 * Lookup the code for an event description
	 */
	public function addDescription($payload) {
		switch ($payload['code']) {
			case '0132':
			case '132':
			case  132:
				$payload['description'] = ucfirst($payload['qualifier']) . ' Zone '.$payload['user_zone'].' ALARM Away';
				break;
			case '0134':
			case '134':
			case  134:
				$payload['description'] = ucfirst($payload['qualifier']) . ' Zone '.$payload['user_zone'].' ALARM Stay';
				break;
			case '408':
				$payload['description'] = ucfirst($payload['qualifier']) . ' Quick Arm Stay';
				break;
			case '302':
				$payload['description'] = ucfirst($payload['qualifier']) . ' Trouble: Low Battery';
				break;

			case '441':
				if ($payload['qualifier'] == 'new') {
					$payload['description'] = 'User '.$payload['user_zone']. ' Armed stay';
				} else {
					$payload['description'] = 'User '.$payload['user_zone']. ' Disarmed stay';
				}
				break;

			case '401':
				if ($payload['qualifier'] == 'new') {
					$payload['description'] = 'User '.$payload['user_zone']. ' Armed away';
				} else {
					$payload['description'] = 'User '.$payload['user_zone']. ' Disarmed away';
				}
				break;
		}

		return $payload;
	}

	/**
	 * Reinject payload into beanstalk to use it
	 * as a messge queue
	 */
	public function sendEvent($payload) {
		$beanstalk = \_make('beanstalkclient');
		$beanstalk->connect();
		$beanstalk->useTube('event-frontend');

		return $beanstalk->put(
		    23, // Give the job a priority of 23.
		    0,  // Do not wait to put job into the ready queue.
		    0,  // Give the job 0 sec to run.
		    json_encode($payload)
		);
		

		/*
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
		 */
	}
}
