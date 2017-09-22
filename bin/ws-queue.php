<?php

Set_time_limit(0);
//@ob_end_flush();
ob_implicit_flush(true);

include_once(dirname(__DIR__).'/local/autoload.php');

require_once(dirname(__DIR__).'/local/amphp/aerys/lib/internal/functions.php');

$beanstalkAddress = getenv('BEANSTALK_ADDRESS');
if ($beanstalkAddress == '') {
	$beanstalkAddress = '127.0.0.1:11300';
}

$cnt=0;


class MyAwesomeWebsocket implements Aerys\Websocket {
    private $endpoint;
	public $clients;
	public $lastMsg;

	public function onStart(Aerys\Websocket\Endpoint $endpoint) {
		$this->endpoint = $endpoint;
	}

	public function blast($msg) {
		if ($this->lastMsg == $msg) {
			return;
			//nothing new
		}
		$this->lastMsg = $msg;

		/*
		if (!count($this->clients)) {
			return;
		}
		 */

		$this->endpoint->broadcast($msg);
		/*
		//TODO: use broadcast()
		foreach ($this->clients as $_clientId) {
			$this->endpoint->send($msg, $_clientId);
		}
		 */
	}

	public function onHandshake(Aerys\Request $request, Aerys\Response $response) {
		// Do eventual session verification and manipulate Response if needed to abort
	}

	public function onOpen(int $clientId, $handshakeData) {
		$this->clients[] = $clientId;
		if ($this->lastMsg != '') {
			$this->endpoint->send($this->lastMsg, $clientId);
		}
	}

	public function onData(int $clientId, Aerys\Websocket\Message $msg) {
		// send back what we get in
		$msg = yield $msg; // Do not forget to yield here to get a string
		yield $this->endpoint->send($msg, $clientId);
	}

	public function onClose(int $clientId, int $code, string $reason) {
		//TODO: remove from clients[]
		// client disconnected, we may not send anything to him anymore
	}

	public function onStop() {
//		$this->endpoint->broadcast("Byebye!");
	}
}


use Psr\Log\NullLogger;

Amp\run(function () use ($beanstalkAddress) {
	$client = new Amp\Beanstalk\BeanstalkClient($beanstalkAddress);
	$client->watch('display');

	$myWs = new \MyAwesomeWebsocket();
	$websocket = \Aerys\websocket($myWs);
	$host = (new \Aerys\Host)->use($websocket);
	$host->expose('0.0.0.0', 8088);
	$server = \Aerys\initServer(new NullLogger, [$host], []);
	yield $server->start();


	echo "D/Queue: watching tube display ...\n";
	Amp\repeat(function() use ($client, $myWs){

		try {
			$promise = $client->reserve(0);
		} catch (Exception $e) {
			if ($e instanceOf Amp\Beanstalk\DeadlineSoonException) {
				//var_dump($e->getJob());
			}
		}
		$promise->when( function($error, $result, $cbData) use ($client, $myWs) {

			if ($error instanceOf Amp\Beanstalk\TimedOutException) {
				return;
			}

			if ($error instanceOf Amp\Beanstalk\DeadlineSoonException) {
				var_dump( get_class($error) );
				return;
			}

			if (!$result) {
				echo "D/Job: no result\n";
				return;
			}

			if ($result) {
				echo "I/Job: RESERVED JOB: ".$result[0]."\n";
			}

			try {
				$id = $result[0];

				$status = json_decode($result[1], TRUE);
		
				try {
					$myWs->blast(print_r($status['msg'], 1));
					echo "D/Job: nothing to do .\n";
				} catch (\Error $t) {
					var_dump($t);
				} catch (\Exception $t) {
					var_dump($t);
				}

				//do work here
//				var_dump($result);
				$k  = $client->delete($id);
//				$k  = $client->release($id);
				//echo "I/Job: DELETING JOB: " . $id."\n";

				$k->when( function($err, $res) use ($client, $id) {
					echo "I/Job: DELETED JOB: " . $id."\n";
				});
				/*
				 */
			} catch (Exception $e) {
				var_dump($e->getMessage());
			}
		});
	}, $msInterval=50);
});


