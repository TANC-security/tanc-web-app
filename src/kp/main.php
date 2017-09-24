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

		$beanstalk = \_make('beanstalkclient');
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
	var to = 800;
	var doneTyping = function() {
		
		if (!timeoutRef) return;
		if (kpbuf == '') return;
		timoutRef = null;
		var bufcopy = kpbuf;
		kpbuf = '';
		$.ajax(burl+'kp/main/send/?k='+encodeURIComponent(bufcopy));
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
		$.get(burl+'kp/main/displayBeanstalk', function(data) {
			if (!data.items) { showDisplayError(); setTimeout(pollDisplay, 1000); return;}
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
			setTimeout(pollDisplay,1000);
			removeDisplayError();
		}).fail(function(xhr, type, status) {
			showDisplayError();
			setTimeout(pollDisplay,10000);
		});
	} catch (e) {
			setTimeout(pollDisplay,3000);
	}
	}

	function onBadWs() {
		showDisplayError();
		wsDisplay();
	}

	function wsDisplay() {
		// Then some JavaScript in the browser:
		var burlParts = burl.split('://');
		var scheme = 'ws://';
		if (burlParts[0] == 'https') {
			scheme = 'wss://';
		}
		var conn = new WebSocket(scheme+burlParts[1]+'display/');

		conn.onmessage = function(e) {
			//console.log(e.data);
			var displayMsg   = e.data || '';
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
			removeDisplayError();
		};
		conn.onopen = function(e) {
		};

		conn.onclose = function(e) {
			setTimeout(onBadWs,1000);
		};
	}
	try {
//		pollDisplay();
		wsDisplay();
	} catch (e) {
		setTimeout(onBadWs,3000);
	}

	function showDisplayError() {
		if ($('.alert').length){
			return;
		}
		$('#content-main').prepend('<div class=\"alert alert-danger\">Communication with the security panel has been interrupted.</div>');
	}
	function removeDisplayError() {
		if ($('.alert').length){
$('.alert').remove();
		}
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
		$beanstalk = \_make('beanstalkclient');
		$x = $beanstalk->connect();
		$beanstalk->watch('default');
		$beanstalk->watch('display');
		$beanstalk->useTube('display');

		$currentjob = $beanstalk->reserve(2);
		$lastjob = FALSE;
		while ($job = $beanstalk->peekReady()) {
			if ($lastjob) {
				$beanstalk->delete($lastjob['id']);
			}
			$lastjob = $job;
		}
		$beanstalk->release($currentjob['id'], 1024, 0);
		$beanstalk->disconnect();
		$object = json_decode($currentjob['body']);
		if (!is_object($object) || ! isset($object->msg)) {
//			$response->addTo('items', "Display         Error");
//			$response->addTo('usermsg', print_r($currentjob, 1));
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
		$beanstalk = \_make('beanstalkclient');
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
