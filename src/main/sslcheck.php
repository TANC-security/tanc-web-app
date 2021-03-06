<?php

class Main_Sslcheck {

	public $rootCertName = 'root_certificate.crt';
	public $privKeyName  = 'root_key.rsa';
	public $sslCertService;

	/**
	 * Output some static JS for JSONP request.
	 * This function is only used to check if a valid
	 * cert is installed already via JS
	 */
	public function pingAction($request, $response) {
		header('Content-type: application/json');
		echo "sslcheck({'sslcheck':'good'});";
		exit();
	}

	public function gencertAction($request, $response) {

		$privkey = $this->sslCertService->getOrGenerateKey($this->privKeyName);
		if ($privkey !== FALSE) {
			$response->addTo('items', 'success');
		} else {
			$response->addTo('items', 'failure');
			return;
		}

		$subject = '';
		$listDn = array();
		$listDn['O']  = 'Self Signed';
		$listDn['CN'] = 'Tanc Home Security';

		foreach ($listDn as $_k => $_v) {
			$subject .= '/'.$_k.'='. $_v;
		}

		$rootcert = $this->sslCertService->getOrGenerateRoot($this->rootCertName, $subject, $privkey);
		if ($rootcert !== FALSE) {
			$response->addTo('items', 'success');
		}

		list($hostname) = explode('/', $request->baseUri);
		$subject = '';
		$listDn = array();
		$listDn['O']  = 'Self Signed';
		$listDn['CN'] = $hostname;

		foreach ($listDn as $_k => $_v) {
			$subject .= '/'.$_k.'='. $_v;
		}

		$csr        = $this->makeDeviceCsr($subject, $hostname, $privkey);
		$devicecert = $this->signDeviceCert($csr, $privkey, $rootcert);
		$response->addTo('certs', $devicecert);
		if ($devicecert !== FALSE) {
			$f = fopen('etc/ssl/tanc.vhost.crt', 'w');
			fputs($f, $devicecert);
			fclose($f);
		}

		//$this->enableSslHost($response);
		return;
	}

	/**
	 * Copy etc/ssl/tanc.vhost.ssl.conf.tpl  to etc/nginx/tanc.vhost.ssl.conf
	 * restart nginx
	 */
	public function enableSslHost($response) {
		$source = 'etc/ssl/tanc.vhost.ssl.conf.tpl';
		$target = 'etc/nginx/tanc.vhost.ssl.conf';

		$retvar = 0;
		$output = [];
		exec('cp '.$source.' '.$target);
		exec('sed -ie \'s/\$ROOT/\/app\/tanc-webapp\//\' '.$target);
		exec('sed -ie \'s/\$HOST_NAME/'. str_replace('.', '\.', $_SERVER['SERVER_NAME']).'/\' '.$target);
		$response->addTo('template-copied', $retvar);
		if ($retvar) {
			throw new \Exception("Cannot copy nginx ssl vhost template");
		}

		$retvar = 0;
		$output = [];
		exec('nginx -t', $output, $retvar);
		$response->addTo('nginx-restart', $retvar);
		if ($retvar) {
			throw new \Exception("Cannot verify nginx config syntax.");
		}

	}

	public function dlrootAction($reqeuest, $response, $kernel) {
		$kernel->clearHandlers('output');
		_connect('output', function() {
			header('Content-type: text/plain');
			header('Content-disposition: attachment; filename=TANC-root-certificate.crt');
			echo file_get_contents('etc/ssl/root_certificate.crt');
		});
	}

	public function makeDeviceCsr($subject, $hostname, $rootkey) {
		$sanList = array();
		//get existing subjectAltName
		if (file_exists('./etc/ssl/openssl.cnf.1')) {
			$x = explode("\n", trim(file_get_contents('./etc/ssl/openssl.cnf.1')));
			$san = array_pop($x);
			$x = explode('=', $san);
			$sanList = explode(',', $x[1]);
		}
		//is IP?
		if (intval($hostname)) {
			$recentSan = 'IP:'.$hostname;
		} else {
			$recentSan = 'DNS:'.$hostname;
		}

		if (!in_array($recentSan, $sanList)) {
			$sanList[] = $recentSan;
		}
		$config = file_get_contents('./etc/ssl/openssl.cnf');
		$config .= "\nreq_extensions = v3_req\n[v3_req]\nsubjectAltName = @SAN\n[SAN]\nsubjectAltName=".implode(',', $sanList);
		file_put_contents('./etc/ssl/openssl.cnf.1', $config);
		//openssl req -new -key selfsignwithus_root_private-4.key -nodes -subj '/CN=www.example.com' -out selfsignwithus_root_device_csr.pem
		//$command = 'openssl req -new -key /dev/stdin -nodes -subj '.escapeshellarg($subject).' -config <(cat /etc/ssl/openssl.cnf <(printf "req_extensions = v3_req\n[SAN]\nsubjectAltName=DNS:192.168.1.98")) -reqexts SAN -extensions SAN';
		$command = 'openssl req -new -key /dev/stdin -nodes -subj '.escapeshellarg($subject).' -config ./etc/ssl/openssl.cnf.1 -reqexts SAN -extensions SAN';
//echo('echo '.escapeshellarg($rootkey).' | '.$command);
//exit();
		$output = array();
		$retvar = 0;
		exec('echo '.escapeshellarg($rootkey).' | '.$command, $output, $retvar);
		if ($retvar != 0) {
			throw new Exception ('Unable to generate CSR');
		}
		return implode("\n", $output);
	}


	/**
	 * @throws Exception cannot fork bg tasks
	 */
	public function signDeviceCert($csr, $rootkey, $rootcert) {
		if (!strlen($rootkey)) {
			return -1;
		}
		if (!strlen($rootcert)) {
			return -2;
			return '';
		}
		if (!strlen($csr)) {
			die('no csr');
			return '';
		}

		$this->createFifos();
		$this->blockForLock();

		$output = array();
		$command  = 'openssl x509 -req -CA var/openssl.cert -CAkey var/openssl.key -CAcreateserial -days 730 ';

/*
		echo('echo '.escapeshellarg($rootcert) .' > var/openssl.cert 2>&1 &');
//		echo('echo '.escapeshellarg($rootcert) .' > var/openssl.cert 2>&1 &');
		exec('echo '.escapeshellarg($rootcert) .' > var/openssl.cert 2>&1 &');
//		echo('echo '.escapeshellarg($rootkey) .' > var/openssl.key 2>&1 &');
		exec('echo '.escapeshellarg($rootkey) .' > var/openssl.key 2>&1 &');

//		echo( 'echo '.escapeshellarg($csr).' | '.$command);
		exec( 'echo '.escapeshellarg($csr).' | '.$command, $output);
*/

		$command = './bin/sign_device_cert.php';
		$retval  = 0;
		exec( 'echo '.escapeshellarg($csr."\n".$rootkey."\n".$rootcert."\n").' | '.$command, $output, $retval);
		$this->releaseLock();
		if ($retval !== 0) {
//			echo 'echo '.escapeshellarg($csr."\n".$rootkey."\n".$rootcert."\n").' | '.$command;
//			exit();
			throw new Exception ('Signing device cert failed');
		}
		return implode("\n", $output);
	}

	public function createFifos() {
		$x = 0;
		if (!file_exists('./var/openssl.cert')) {
			exec('mkfifo ./var/openssl.cert', $output, $x);
		}
		if (!file_exists('./var/openssl.key')) {
			exec('mkfifo ./var/openssl.key', $output, $x);
		}
		if ($x !== 0) {
			echo "no fifos"; exit();
		}
	}

	/**
	 * Acquire a semaphore lock
	 * @throws Exception
	 */
	public function blockForLock() {
		$this->ftok = ftok(__FILE__, 'O');
		$this->sem = sem_get($this->ftok, 1);
		if (!sem_acquire($this->sem)) {
			throw new Exception ('Unable to acquire lock');
		}
	}


	public function releaseLock() {
		if ($this->sem !== NULL)  {
			sem_release($this->sem);
		}
	}
}
