<?php
include ('local/autoload.php');

class Tanc_Tests_SSLService extends PHPUnit_Framework_TestCase { 

	public function setUp() {
		$this->service = new Tanc\SSL\CertService();
	}

	/**
	 */
	public function test_service() {
		$this->assertTrue( is_object($this->service) );
	}

	public function test_make_new_rsa_key() {
		$key = $this->service->generateKey();
		$this->assertTrue(strlen($key) > 0);
	}

	public function test_make_new_root_cert() {
		$key = $this->service->generateKey();
		$cert = $this->service->generateRoot('/O=Org/CN=common name', $key);
		$this->assertTrue(strlen($cert) > 0);
	}
}
