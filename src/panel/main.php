<?php

class Panel_Main {

	public $logService;

	public function eventAction($request, $response) {
		echo "event action\n";
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
		$client = \_make('mqttclient');
		$client->connect();

		$topic = _get('topic-prefix', 'security/').'event-frontend';
		$promise = $client->publish(
		    json_encode($payload), $topic, 0
		);
		\Amp\Promise\wait($promise);
	}
}
