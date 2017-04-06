<?php

include_once ('src/Beanstalk/Client.php');

use Beanstalk\Client;

class Kp_Main {

	public function output($response) {
		_set('page_title', 'Keypad');
	}

	public function fooAction($request, $response) {
		$k = $request->cleanString('msg');
		$response->addTo('key', trim($k));

		$beanstalk = new Client(['host'=>'192.168.1.79']);
		$beanstalk->connect();
		$beanstalk->useTube('hangouts');

		$x = $beanstalk->put(
		    23, // Give the job a priority of 23.
		    0,  // Do not wait to put job into the ready queue.
		    10, // Give the job 10 sec to run.
		    json_encode(['text'=>$k])
		);
		var_dump($x);
		exit();
	}


	public function mainAction($response) {
		$response->addTo('main', ['foo', 'bar']);
		$response->addTo('extraJs', "
		<script type=\"text/javascript\">
$(document).ready(function() {

	var burl = $('body').data('base-url');
	var kpbuf = '';
	var timeoutRef;
	var to = 600;
	var doneTyping = function() {
		
		if (!timeoutRef) return;
		if (kpbuf == '') return;
		timoutRef = null;
		var bufcopy = kpbuf;
		kpbuf = '';
		$.ajax(burl+'kp/main/send/?k='+bufcopy);
	}
	
	$('.kp-container > button').on('click',function(e) {
		kpbuf += e.target.value;
	
		if (timeoutRef)  clearTimeout(timeoutRef);
		timeoutRef = setTimeout(function() {
			doneTyping();
		}, to);
//		$.ajax(burl+'kp/main/send/?k='+e.target.value);
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
		}).fail(function(xhr, type, status) {
			showDisplayError();
			setTimeout(pollDisplay,10000);
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
	function showDisplayError() {
		if ($('.alert').length){
			return;
		}
		$('#content-main').prepend('<div class=\"alert alert-danger\">Communication with the security panel has been interrupted.</div>');
	}
});
		</script>
");
	}


	/**
	 * Read current state from tmpfs
	 */
	public function displayAction($response) {
		$state = @file_get_contents('/dev/shm/display.json');
		$object = json_decode($state);
		if (!is_object($object) || ! isset($object->msg)) {
			$response->statusCode = 500;
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
