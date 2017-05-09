<?php

class Main_Sslcheck {

	public $rootCertName = 'root_certificate.crt';
	public $privKeyName  = 'root_key.rsa';

	public function gencertAction($request, $response) {

		$privkey = $this->generateKey($this->privKeyName);
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

		$fname = 'etc/'.$this->rootCertName;
		if (file_exists($fname)) {
			$rootcert   = file_get_contents($fname);
		} else {
			$rootcert   = $this->generateRootCert($subject, $privkey);
			$response->addTo('certs', $rootcert);
			if ($rootcert !== FALSE) {
				$f = fopen($fname, 'w');
				fputs($f, $rootcert);
				fclose($f);
			}
		}


		list($hostname) = explode('/', $request->baseUri);
		$subject = '';
		$listDn = array();
		$listDn['O']  = 'Self Signed';
		$listDn['CN'] = $hostname;

		foreach ($listDn as $_k => $_v) {
			$subject .= '/'.$_k.'='. $_v;
		}

		$csr        = $this->makeDeviceCsr($subject, $privkey);
		$devicecert = $this->signDeviceCert($csr, $privkey, $rootcert);
		$response->addTo('certs', $devicecert);
		if ($devicecert !== FALSE) {
			$f = fopen('etc/'.$hostname.'.crt', 'w');
			fputs($f, $devicecert);
			fclose($f);
		}

		return;
	}

	public function generateRootCert($subject, $signkey) {
		//openssl req -x509 -new -nodes -key selfsignwithus_root_private-4.key -days 365 -subj /C=US/O=unit -out /dev/stdout
        $keygen  = 'openssl req -x509 -new -nodes -key /dev/stdin -days 365 -subj '.escapeshellarg($subject); //.' -out /dev/stdout';
		$output = array();
		$retvar = 0;
		$keygen = 'echo -n '.escapeshellarg($signkey).' | '.$keygen;
		exec($keygen, $output, $retvar);
		return implode("\n", $output);
	}


	public function dlrootAction($reqeuest, $response, $kernel) {
		$kernel->clearHandlers('output');
		$kernel->connect('output', function() {
			header('Content-type: text/plain');
			header('Content-disposition: attachment; filename=TANC-root-certificate.crt');
			echo file_get_contents('etc/root_certificate.crt');
		});
	}

	public function generateKey($fname) {
		$file = './etc/'.$fname;
		if (file_exists($file)) {
			return file_get_contents($file);
		}

		//generate new certificate
		$type = 'rsa';
		$bits = '2048';

		$keygen  = 'openssl genpkey -algorithm '.strtoupper($type).' -pkeyopt rsa_keygen_bits:'.$bits;
		$output = array();
		$retvar = 0;
		exec( escapeshellcmd($keygen), $output, $retvar);
		if ($retvar !== 0) {
			return FALSE;
		}
		$f = fopen($file, 'w');
		if (!$f) {
			return FALSE;
		}
		fputs($f, implode("\n", $output));
		fclose($f);
		return implode("\n", $output);
	}

	public function makeDeviceCsr($subject, $rootkey) {
		//openssl req -new -key selfsignwithus_root_private-4.key -nodes -subj '/CN=www.example.com' -out selfsignwithus_root_device_csr.pem
		$command = 'openssl req -new -key /dev/stdin -nodes -subj '.escapeshellarg($subject).' -out /dev/stdout';
		$output = array();
		exec('echo '.escapeshellarg($rootkey).' | '.$command, $output);
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
		//echo( 'echo '.escapeshellarg($csr."\n".$rootkey."\n".$rootcert).' | '.$command);
		$this->releaseLock();
//		echo 'echo '.escapeshellarg($csr."\n".$rootkey."\n".$rootcert."\n").' | '.$command;
//		exit();
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
