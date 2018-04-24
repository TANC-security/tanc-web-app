<?php

class Main_Main {


	public function mainAction($request, $response, $kernel) {
		/*
		$kernel->iCanHandle('process', 'kp/main.php::mainAction');
		$kernel->iCanHandle('output', 'kp/main.php');
		$request->appName = 'kp';
		 */
//		_set('template.main.file', 'kp/views/main_main.tpl');

		$response->addTo('extraJs','scripts/vue-main.js');
	}
}
