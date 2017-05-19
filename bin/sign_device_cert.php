#!/usr/bin/php
<?php

$f = fopen('php://stdin', 'r');

$csr = '';
$key = '';
$crt = '';
$buf = '';
while ($line = fgets($f, 4096)) {
	$buf .= $line;
	if (strpos($line, 'END CERTIFICATE REQUEST') != 0) {
		$csr = $buf;
		$buf = '';
		continue;
	}
	if (strpos($line, 'END CERTIFICATE') != 0) {
		$crt = $buf;
		$buf = '';
		continue;
	}

	if (strpos($line, 'END PRIVATE KEY') != 0) {
		$key = $buf;
		$buf = '';
		continue;
	}
}
$buf = '';

if ($csr == '') {
	echo 'missing csr';
	die(1);
}
if ($key == '') {
	echo 'missing key';
	die(1);
}
if ($crt == '') {
	echo 'missing crt';
	die(1);
}



		$pid = pcntl_fork();
		if ($pid) {
		} else if ($pid == -1) {
			echo 'failed to fork';
			die(1);
		} else {
			//exec('echo '.escapeshellarg($crt) .' > var/openssl.cert 2>&1 &');
			exec('echo '.escapeshellarg($crt) .' > var/openssl.cert &');
		usleep(2000);
			exit();
		}

		$pid2 = pcntl_fork();
		if ($pid2) {
		} else if ($pid2 == -1) {
			echo 'failed to fork';
			die(1);
		} else {
			//exec('echo '.escapeshellarg($key) .' > var/openssl.key 2>&1 &');
			exec('echo '.escapeshellarg($key) .' > var/openssl.key &');
		usleep(2000);
			exit();
		}
		usleep(4000);

		$output = array();
		$retval = 0;
#		$command  = 'openssl x509 -req -CA var/openssl.cert -CAkey var/openssl.key -CAcreateserial -days 3650';//  -out /dev/stdout';
		$command  = 'openssl ca   -config ./etc/ssl/openssl.cnf.1 -batch -in /dev/stdin -cert var/openssl.cert -keyfile var/openssl.key -days 3650 -out /dev/stdout';
		//echo( 'echo '.escapeshellarg(trim($csr)).' | '.$command);
		//echo( 'echo '.escapeshellarg($csr."\n").' | '.$command."\n");
		$x = exec( 'echo '.escapeshellarg($csr."\n").' | '.$command, $output, $retval);
		if ($retval != 0 ) {
			echo implode("", $output);
			echo "execution failed with status ".$retval." \n";
			exit();
		}
		echo implode("\n", $output);
#		exec ('rm var/openssl.srl');

		$status = 0;
		pcntl_waitpid($pid, $status);
		pcntl_waitpid($pid2, $status);
