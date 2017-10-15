<?php

Set_time_limit(0);
//@ob_end_flush();
ob_implicit_flush(true);

include_once(dirname(__DIR__).'/local/autoload.php');

#require_once(dirname(__DIR__).'/local/amphp/aerys/lib/internal/functions.php');

$beanstalkAddress = getenv('BEANSTALK_ADDRESS');
if ($beanstalkAddress == '') {
	$beanstalkAddress = '127.0.0.1:11300';
}

$cnt=0;

use Amp\Loop;

class MyAwesomeWebsocket implements Aerys\Websocket {
    private $endpoint;
	public  $clients;
	public  $lastMsg;

	public function onStart(Aerys\Websocket\Endpoint $endpoint) {
		$this->endpoint = $endpoint;
	}

	public function blastAndSave($payload) {
		$this->endpoint->broadcast(
			json_encode(
				$payload
			)
		);
	}

	public function sendDisplayMessage($message, $clientId=NULL) {
		if ($clientId == NULL) {
			$this->endpoint->broadcast(
				json_encode(['type'=>'display', 'message'=>$message])
			);
		} else {
			$this->endpoint->send(
				json_encode(['type'=>'display', 'message'=>$message])
				, $clientId
			);
		}
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

		$this->sendDisplayMessage($msg);
	}

	public function validateSession($sessid) {
		$sfile = 'var/sess/sess_'.$sessid;
		if (!file_exists($sfile)) {
			var_dump($sfile);
			return FALSE;
		}
		//session_start is too messed up to use in multi connection environment
		$s    = file_get_contents($sfile);
		$sarr = explode(';', $s);
		foreach ($sarr as $_var) {
			if (!strpos($_var, '|')) continue;
			list($k, $v) = explode('|', $_var);
			if ($k == '_touch') {
				return (bool) intval($v) <= time() - 7200;
			}
		}

		return TRUE;
	}

	/**
	 * return session ID if session is valid,
	 * false otherwise
	 */
	public function onHandshake(Aerys\Request $request, Aerys\Response $response) {
		// Do eventual session verification and manipulate Response if needed to abort
		$x = $request->getCookie('s');
		if (!$this->validateSession($x) ) {
			$response->setStatus(401);
			return FALSE;
		}
		return $x;
	}

	public function onOpen(int $clientId, $handshakeData) {
		if ($handshakeData == FALSE) {
			return;
		}
		$this->clients[] = $clientId;
		if ($this->lastMsg != '') {
			$this->sendDisplayMessage($this->lastMsg, $clientId);
//			$this->endpoint->send($this->lastMsg, $clientId);
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


/*
class MySessionHandler implements SessionHandlerInterface
{
    private $savePath;

    public function open($savePath, $sessionName)
    {
        $this->savePath = $savePath;
        if (!is_dir($this->savePath)) {
            mkdir($this->savePath, 0777);
        }

        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($id)
    {
        return (string)@file_get_contents("$this->savePath/sess_$id");
    }

    public function write($id, $data)
    {
        return file_put_contents("$this->savePath/sess_$id", $data) === false ? false : true;
    }

    public function destroy($id)
    {
        $file = "$this->savePath/sess_$id";
        if (file_exists($file)) {
            unlink($file);
        }

        return true;
    }

    public function gc($maxlifetime)
    {
        foreach (glob("$this->savePath/sess_*") as $file) {
            if (filemtime($file) + $maxlifetime < time() && file_exists($file)) {
                unlink($file);
            }
        }

        return true;
    }
}
 */


/*
$handler = new MySessionHandler();
ini_set('session.use_cookies', '0');
ini_set("session.use_cookies", 0);
ini_set("session.use_only_cookies", 0);
ini_set("session.use_trans_sid", 1);
ini_set("session.cache_limiter", "");
session_set_save_handler($handler, true);
 */

use Psr\Log\NullLogger;

$myWs = new \MyAwesomeWebsocket();
$websocket = \Aerys\websocket($myWs);
$host = (new \Aerys\Host)->use($websocket);
$host->expose('0.0.0.0', 8088);
$server = \Aerys\initServer(new NullLogger, [$host], []);
//yield $server->start();
$server->start();



//$beanstalkAddress = 'tcp://'.$beanstalkAddress.'?tube=display';
Loop::run(function () use ($beanstalkAddress, $myWs) {
	$client = new Amp\Beanstalk\BeanstalkClient('tcp://'.$beanstalkAddress.'?tube=display');
	$client->watch('display');

	$clientEvent = new Amp\Beanstalk\BeanstalkClient('tcp://'.$beanstalkAddress.'?tube=event-frontend');
	$clientEvent->watch('event-frontend');

	echo "D/Queue: watching tube display ...\n";
	Loop::repeat($msInterval=50,
		function() use ($client, $clientEvent, $myWs){

		try {
			$promise      = $client->reserve(0);
			$promiseEvent = $clientEvent->reserve(0);
		} catch (Exception $e) {
			if ($e instanceOf Amp\Beanstalk\DeadlineSoonException) {
				//var_dump($e->getJob());
			}
		}
		$promise->onResolve( function($error, $result) use ($client, $myWs) {

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
					echo "D/WS: blast msg: ".$status['msg']." .\n";
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

				$k->onResolve( function($err, $res) use ($client, $id) {
					echo "I/Job: DELETED JOB: " . $id."\n";
				});
				/*
				 */
			} catch (Exception $e) {
				var_dump($e->getMessage());
			}
		});

		$promiseEvent->onResolve( function($error, $result) use ($clientEvent, $myWs) {

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

				$payload = json_decode($result[1], TRUE);
				echo "D/E: got job\n";
				print_r($payload);
		
				try {
					$myWs->blastAndSave($payload);
				} catch (\Error $t) {
					var_dump($t);
				} catch (\Exception $t) {
					var_dump($t);
				}

				//do work here
//				var_dump($result);
				$k  = $clientEvent->delete($id);
//				$k  = $client->release($id);
				//echo "I/Job: DELETING JOB: " . $id."\n";

				$k->onResolve( function($err, $res) use ($clientEvent, $id) {
					echo "I/Job: DELETED JOB: " . $id."\n";
				});
				/*
				 */
			} catch (Exception $e) {
				var_dump($e->getMessage());
			}
		});
	});
});


