<?php

set_time_limit(0);
//@ob_end_flush();
ob_implicit_flush(true);

include_once(dirname(__DIR__).'/local/autoload.php');


$metrofw = new Metrofw_Beanstalk();
$metrofw->bootstrap('',  FALSE);

$beanstalkAddress = getenv('BEANSTALK_ADDRESS');
if ($beanstalkAddress == '') {
	$beanstalkAddress = '127.0.0.1:11300';
}

$cnt=0;

use Amp\Loop;
Loop::run(function () use ($metrofw, $beanstalkAddress) {
	$clientStatus = new Amp\Beanstalk\BeanstalkClient('tcp://'.$beanstalkAddress);
	$clientStatus->watch('status');
	echo "D/Queue: watching tube status ...\n";

	$clientEvent = new Amp\Beanstalk\BeanstalkClient('tcp://'.$beanstalkAddress);
	$clientEvent->watch('event');
	echo "D/Queue: watching tube event ...\n";

	//Amp\repeat(function() use ($clientStatus, $metrofw){
	Loop::repeat(50,
	function() use ($clientStatus, $clientEvent, $metrofw) {

		try {
			$promiseStatus = $clientStatus->reserve(0);
			$promiseEvent  = $clientEvent->reserve(0);
		} catch (Exception $e) {
			if ($e instanceOf Amp\Beanstalk\DeadlineSoonException) {
				//var_dump($e->getJob());
			}
		}
		$promiseStatus->onResolve( function($error, $result, $cbData) use ($clientStatus, $metrofw) {

			if ($error instanceOf Amp\Beanstalk\TimedOutException) {
				return;
			}

			if ($error instanceOf Amp\Beanstalk\DeadlineSoonException) {
				var_dump( get_class($error) );
				return;
			}

			if (!$result) {
				var_Dump($error);
				echo "D/Job: no result\n";
				return;
			}

			if ($result) {
				echo "I/Job: RESERVED STATUS JOB: ".$result[0]."\n";
			}

			try {
				$id = $result[0];

				$status = json_decode($result[1], TRUE);
				
				$request = new Metrofw_Request();
				$request->vars['status'] = $status;
				$request->appName = 'status';
				$request->modName = 'main';
				$request->actName = 'send';
				$request->requestedUrl = '/status/main/send';

				_didef('request',   $request);
				$response = _makeNew('response');

				try {
//					echo "I/Job: onRequest: ".print_r($request,1)."\n";
					$metrofw->onRequest($request, $response);
				} catch (\Error $t) {
					var_dump($t);
				} catch (\Exception $t) {
					var_dump($t);
				}
				var_dump($response);

				//do work here
//				var_dump($result);
				$k  = $clientStatus->delete($id);
				//echo "I/Job: DELETING JOB: " . $id."\n";

				$k->onResolve( function($err, $res) use ($clientStatus, $id) {
					echo "I/Job: DELETED JOB: " . $id."\n";
				});
			} catch (Exception $e) {
				var_dump($e->getMessage());
			}
		});

		$promiseEvent->onResolve( function($error, $result, $cbData) use ($clientEvent, $metrofw) {

			if ($error instanceOf Amp\Beanstalk\TimedOutException) {
				return;
			}

			if ($error instanceOf Amp\Beanstalk\DeadlineSoonException) {
				var_dump( get_class($error) );
				return;
			}

			if (!$result) {
				var_Dump($error);
				echo "D/Job: no result\n";
				return;
			}

			if ($result) {
				echo "I/Job: RESERVED EVENT JOB: ".$result[0]."\n";
			}

			try {
				$id = $result[0];

				$payload = json_decode($result[1], TRUE);
		
				$request = new Metrofw_Request();
				$request->vars['payload'] = $payload;
				$request->appName = 'panel';
				$request->modName = 'main';
				$request->actName = 'event';
				$request->requestedUrl = '/panel/main/event';

				_didef('request',   $request);
				$response = _makeNew('response');

				try {
					//echo "I/Job: onRequest: ".print_r($request,1)."\n";
					$metrofw->onRequest($request, $response);
				} catch (\Error $t) {
					var_dump($t);
				} catch (\Exception $t) {
					var_dump($t);
				}

				$clientEvent->delete($id)->onResolve(
					function($err, $res) use ($id) {
						echo "I/Job: DELETED JOB: " . $id."\n";
				});

			} catch (Exception $e) {
				var_dump($e->getMessage());
			}
		});
	});
});



class Metrofw_Beanstalk
{
	public $kernel;
	public $container;

	/**
	 * Bootstrap an application implementing the HttpKernelInterface.
	 *
	 * @param string $appBootstrap The name of the class used to bootstrap the application
	 * @param string|null $appBootstrap The environment your application will use to bootstrap (if any)
	 * @param boolean $debug If debug is enabled
	 * @see http://stackphp.com
	 */
	public function bootstrap($appenv, $debug) {
		if (!include_once ('local/metrophp/metrodi/container.php')) {
			header('HTTP/1.1 500 Internal Server Error');
			echo 'System startup failure.  Incomplete dependencies.';
			exit();
		}
		if (!include_once ('local/metrofw/kernel.php')) {
			header('HTTP/1.1 500 Internal Server Error');
			echo 'System startup failure.  Incomplete dependencies.';
			exit();
		}


		$this->container = Metrodi_Container::getContainer();
		$this->kernel    = new Metrofw_Kernel($this->container);
		_didef('kernel',    $this->kernel);
		_didef('container', $this->container);

		if(!include('etc/bootstrap.php')) {
			$this->container = NULL;
			$this->kernel    = NULL;
			return;
		}
	}

	/**
	 * Returns the repository which is used as root for the static file serving.
	 *
	 * @return string
	 */
	public function getStaticDirectory() {
		$templateName  = _get('template_name', 'webapp01');
		_set('template_name', $templateName);
		$this->baseDir = _get('template_basedir', 'local/templates/');

		return $this->baseDir . $templateName;
	}

	/**
	 * Handle a request using a HttpKernelInterface implementing application.
	 *
	 * @param  $request
	 * @param  $response
	 */
	public function onRequest($request, $response) {
		Metrodi_Container::$container = NULL;
		$this->container = Metrodi_Container::getContainer();
		$this->container->didef('request', $request);
		$this->kernel->serviceList = array();
		$this->kernel->cycleStack  = array();
		$this->kernel->container   = $this->container;
		_didef('kernel',    $this->kernel);
		_didef('container', $this->container);

		if(!include('etc/bootstrap.php')) {
			echo "no bootstrap\n";
			$this->container = NULL;
			return;
		}
		_didef('request',   $request);
		_clearHandlers('analyze');
		_connect('analyze',   'metrofw/router.php::autoRoute', 3);

		try {
			$this->kernel->_runLifecycle('analyze');
			$this->kernel->_runLifecycle('resources');
//			$this->kernel->_runLifecycle('authenticate');
//			$this->kernel->_runLifecycle('authorize');
			$this->kernel->_runLifecycle('process');
//			$this->kernel->_runLifecycle('output');
			$x = ob_get_contents();
			ob_end_clean();
			echo $x;
//			$this->kernel->_runLifecycle('hangup');
		} catch (Exception $e) {
//			ob_end_clean();
		}

		/*
		$rsp = _make('response');
		$response->writeHead($rsp->get('statusCode'), ['Content-type'=>'text/html']);
		$response->write($x);
		$response->end();
		 */

		$this->container = NULL;

		_didef('container', $this->container);
	}
}
