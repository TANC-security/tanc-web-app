<?php

include_once ('src/Beanstalk/Client.php');

use Beanstalk\Client;

class Kp_Main {

	public function output($response) {
		_set('page_title', 'Keypad');
	}

	public function mainAction($response) {
		$response->addTo('main', ['foo', 'bar']);
		$response->addTo('extraJs', "
		<script>
$(document).ready(function() {

	var burl = $('body').data('base-url');
	$('.kp-container > button').on('click',function(e) {
		$.ajax(burl+'kp/main/send/?k='+e.target.value);
	});
	function pollDisplay() {
	try {
		$.get(burl+'kp/main/display', function(data) {
			if (!data.items) { setTimeout(pollDisplay, 2000); return;}
			var displayMsg   = data.items[0] || '';
			var line1 = line2 = '';
			for (i=0; i < 16; i++) {
				line1 += displayMsg.charAt(i);
			}
			for (i=16; i < 32; i++) {
				line2 += displayMsg.charAt(i);
			}
			line1 = line1.replace(' ', '&nbsp;');
			line2 = line2.replace(' ', '&nbsp;');

			$('.kp-view').html(line1+'<br/>'+line2);
			setTimeout(pollDisplay,2000);
		});
	} catch (e) {
			setTimeout(pollDisplay,3000);
	}
	}
	try {
	pollDisplay();
	} catch (e) {
			setTimeout(pollDisplay,3000);
	}
});
		</script>
");
	}


	/**
	 * Read current state from tmpfs
	 */
	public function displayAction($response) {
		$state = file_get_contents('/dev/shm/display.json');
		$object = json_decode($state);
		if (!is_object($object) || ! isset($object->msg)) {
			//$response->addTo('items', "Display         Error");
			//$response->addTo('usermsg', print_r($lastjob, 1));
			//$response->addTo('usermsg', print_r($job, 1));
		} else {
			$response->addTo('items', print_r($object->msg, 1));
		}

	}

	public function displayBeanstalkAction($response) {
		$beanstalk = new Client(['host'=>'127.0.0.1']);
		$beanstalk->connect();
		$beanstalk->watch('display');

		$job = $beanstalk->reserve(2);
		$lastjob = $job;
		while ($job) {
			$lastjob = $job;
			$beanstalk->delete($job['id']);
			$job = $beanstalk->reserve(0);
		}
		$object = json_decode($lastjob['body']);
		if (!is_object($object) || ! isset($object->msg)) {
			//$response->addTo('items', "Display         Error");
			//$response->addTo('usermsg', print_r($lastjob, 1));
			//$response->addTo('usermsg', print_r($job, 1));
		} else {
			$response->addTo('items', print_r($object->msg, 1));
		}

	}

	public function sendAction($request, $response) {
		$k = $request->cleanString('k');
		$response->addTo('key', trim($k));
		if (strlen(trim($k))) {
			$x = $this->sendKey($k);
			$response->addTo('items', $x);
		}
	}

	public function sendKey($key) {
		$beanstalk = new Client(['host'=>'127.0.0.1']);
		$beanstalk->connect();
		$beanstalk->useTube('input');

		return $beanstalk->put(
		    23, // Give the job a priority of 23.
		    0,  // Do not wait to put job into the ready queue.
		    10, // Give the job 10 sec to run.
		    $key
		);
	}
}