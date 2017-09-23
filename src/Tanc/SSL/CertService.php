<?php
namespace Tanc\SSL;

class CertService {
	public $keyBits = 2048;
	public $keyType = 'rsa';
	public $baseDir = './etc/ssl/';

	/**
	 * Get or create a new key and store it in $fname
	 *
	 * @return mixed String contents of file or FALSE if an error encountered making key
	 */
	public function getOrGenerateKey($fname) {
		$file = $this->baseDir.$fname;
		$key = $this->getFile($file);
		if (!$key) {
			$key = $this->generateKey();
			if ($key) {
				file_put_contents($file, $key);
			}
		}
		return $key;
	}

	public function getOrGenerateRoot($fname, $subject, $privkey) {
		$file = $this->baseDir.$fname;
		$key = $this->getFile($file);
		if (!$key) {
			$key = $this->generateRoot($subject, $privkey);

			if ($key) {
				file_put_contents($file, $key);
			}
		}
		return $key;
	}

	/**
	 * @return mixed String contents of file or FALSE if file does not exist
	 */
	public function getFile($fname) {
		$file = $fname;
		if (file_exists($file)) {
			return file_get_contents($file);
		}
		return FALSE;

	}

	/**
	 * @return mixed String contents key or false if shell command failed
	 */
	public function generateKey() {
		//generate new certificate
		$keygen  = 'openssl genpkey -algorithm '.strtoupper($this->keyType).' -pkeyopt rsa_keygen_bits:'.$this->keyBits;
		$output = array();
		$retvar = 0;
		exec( escapeshellcmd($keygen), $output, $retvar);
		if ($retvar !== 0) {
			return FALSE;
		}
		return implode("\n", $output);
	}

	public function generateRoot($subject, $signkey) {
		//openssl req -x509 -new -nodes -key selfsignwithus_root_private-4.key -days 365 -subj /C=US/O=unit -out /dev/stdout
        $keygen  = 'openssl req -x509 -new -nodes -key /dev/stdin -days 365 -subj '.escapeshellarg($subject); //.' -out /dev/stdout';
		$output = array();
		$retvar = 0;
		$keygen = 'echo -n '.escapeshellarg($signkey).' | '.$keygen;
		exec($keygen, $output, $retvar);
		if ($retvar !== 0 ) {
			return FALSE;
		}
		return implode("\n", $output);
	}
}
